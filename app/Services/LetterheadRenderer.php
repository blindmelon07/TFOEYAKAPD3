<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;
use RuntimeException;
use ZipArchive;

/**
 * Builds a .docx from a letterhead template by filling its placeholders and
 * adding body paragraphs, leaving the template's banner, fonts, page size and
 * margins untouched.
 *
 * Placeholders such as "[title]" or "{date}" are matched across a paragraph's
 * text runs, so they still work when Word splits them into several runs. A
 * paragraph containing only "{body}" is replaced by the body; otherwise the
 * body is inserted right after the "{date}" (or "[title]") paragraph.
 */
class LetterheadRenderer
{
    private const string WORD_NAMESPACE = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    private const string XML_NAMESPACE = 'http://www.w3.org/XML/1998/namespace';

    private const string BODY_PLACEHOLDER = '{body}';

    /**
     * Render a filled-in copy of the template and return its temporary path.
     *
     * @param  array<string, string>  $placeholders  Placeholder text mapped to its replacement.
     */
    public function render(string $templatePath, array $placeholders, string $body): string
    {
        $outputPath = $this->temporaryPath();

        if (! copy($templatePath, $outputPath)) {
            throw new RuntimeException("Could not copy the letterhead template [{$templatePath}].");
        }

        $zip = new ZipArchive;

        if ($zip->open($outputPath) !== true) {
            throw new RuntimeException("The letterhead template [{$templatePath}] is not a valid .docx file.");
        }

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $partName = (string) $zip->getNameIndex($index);

            if (! preg_match('#^word/(document|header\d*|footer\d*)\.xml$#', $partName)) {
                continue;
            }

            $document = new DOMDocument;
            $document->loadXML((string) $zip->getFromName($partName));

            if ($partName === 'word/document.xml') {
                $this->insertBody($document, $placeholders, $body);
            }

            $this->fillPlaceholders($document, $placeholders);

            $zip->addFromString($partName, (string) $document->saveXML());
        }

        $zip->close();

        return $outputPath;
    }

    /**
     * Determine whether the file is a .docx that can be used as a letterhead.
     */
    public static function isValidTemplate(string $path): bool
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            return false;
        }

        $hasDocument = $zip->locateName('word/document.xml') !== false;
        $zip->close();

        return $hasDocument;
    }

    /**
     * Replace placeholders in every paragraph, merging the paragraph's runs
     * into its first run when a placeholder spans several of them.
     *
     * @param  array<string, string>  $placeholders
     */
    private function fillPlaceholders(DOMDocument $document, array $placeholders): void
    {
        $xpath = $this->xpath($document);

        foreach ($this->elements($xpath, '//w:p') as $paragraph) {
            $textNodes = $this->elements($xpath, './/w:t', $paragraph);
            $text = $this->textOf($xpath, $paragraph);
            $filled = strtr($text, $placeholders);

            if ($filled === $text || $textNodes === []) {
                continue;
            }

            foreach ($textNodes as $position => $textNode) {
                $this->setText($textNode, $position === 0 ? $filled : '');
            }
        }
    }

    /**
     * Put the body where the template asks for it.
     *
     * @param  array<string, string>  $placeholders
     */
    private function insertBody(DOMDocument $document, array $placeholders, string $body): void
    {
        $xpath = $this->xpath($document);
        $paragraphs = $this->elements($xpath, '//w:body/w:p');
        $lines = $this->bodyLines($body);

        foreach ($paragraphs as $paragraph) {
            if (trim($this->textOf($xpath, $paragraph)) === self::BODY_PLACEHOLDER) {
                foreach ($lines as $line) {
                    $paragraph->parentNode?->insertBefore($this->paragraph($document, $line), $paragraph);
                }

                $paragraph->parentNode?->removeChild($paragraph);

                return;
            }
        }

        if ($lines === []) {
            return;
        }

        $anchor = $this->findParagraphContaining($xpath, $paragraphs, '{date}')
            ?? $this->findParagraphContaining($xpath, $paragraphs, '[title]')
            ?? ($paragraphs === [] ? null : $paragraphs[array_key_last($paragraphs)]);

        if ($anchor === null) {
            return;
        }

        $nextSibling = $anchor->nextSibling;

        foreach (['', ...$lines] as $line) {
            $anchor->parentNode?->insertBefore($this->paragraph($document, $line), $nextSibling);
        }
    }

    /**
     * @param  list<DOMElement>  $paragraphs
     */
    private function findParagraphContaining(DOMXPath $xpath, array $paragraphs, string $needle): ?DOMElement
    {
        foreach ($paragraphs as $paragraph) {
            if (str_contains($this->textOf($xpath, $paragraph), $needle)) {
                return $paragraph;
            }
        }

        return null;
    }

    /**
     * Get the elements matching an XPath expression.
     *
     * @return list<DOMElement>
     */
    private function elements(DOMXPath $xpath, string $expression, ?DOMNode $context = null): array
    {
        $elements = [];

        foreach ($xpath->query($expression, $context) ?: [] as $node) {
            if ($node instanceof DOMElement) {
                $elements[] = $node;
            }
        }

        return $elements;
    }

    /**
     * Get a paragraph's visible text, joined across all of its runs.
     */
    private function textOf(DOMXPath $xpath, DOMElement $paragraph): string
    {
        return implode('', array_map(
            fn (DOMElement $textNode): string => $textNode->textContent,
            $this->elements($xpath, './/w:t', $paragraph),
        ));
    }

    /**
     * Split the body into lines, dropping trailing blank lines.
     *
     * @return list<string>
     */
    private function bodyLines(string $body): array
    {
        $lines = preg_split('/\R/', $body) ?: [];

        while ($lines !== [] && trim((string) end($lines)) === '') {
            array_pop($lines);
        }

        return $lines;
    }

    /**
     * Build a justified Arial 12pt paragraph, matching the template's text style.
     */
    private function paragraph(DOMDocument $document, string $text): DOMElement
    {
        $paragraph = $this->element($document, 'w:p');

        $properties = $paragraph->appendChild($this->element($document, 'w:pPr'));
        $properties->appendChild($this->element($document, 'w:spacing', ['w:after' => '120', 'w:line' => '276', 'w:lineRule' => 'auto']));
        $properties->appendChild($this->element($document, 'w:jc', ['w:val' => 'both']));

        if ($text === '') {
            return $paragraph;
        }

        $run = $paragraph->appendChild($this->element($document, 'w:r'));
        $runProperties = $run->appendChild($this->element($document, 'w:rPr'));
        $runProperties->appendChild($this->element($document, 'w:rFonts', ['w:ascii' => 'Arial', 'w:hAnsi' => 'Arial', 'w:cs' => 'Arial']));
        $runProperties->appendChild($this->element($document, 'w:sz', ['w:val' => '24']));
        $runProperties->appendChild($this->element($document, 'w:szCs', ['w:val' => '24']));

        $textNode = $this->element($document, 'w:t');
        $run->appendChild($textNode);
        $this->setText($textNode, $text);

        return $paragraph;
    }

    /**
     * @param  array<string, string>  $attributes
     */
    private function element(DOMDocument $document, string $name, array $attributes = []): DOMElement
    {
        $element = $document->createElementNS(self::WORD_NAMESPACE, $name);

        foreach ($attributes as $attribute => $value) {
            $element->setAttributeNS(self::WORD_NAMESPACE, $attribute, $value);
        }

        return $element;
    }

    private function setText(DOMElement $textNode, string $text): void
    {
        while ($textNode->firstChild) {
            $textNode->removeChild($textNode->firstChild);
        }

        $textNode->appendChild(new DOMText($text));
        $textNode->setAttributeNS(self::XML_NAMESPACE, 'xml:space', 'preserve');
    }

    private function xpath(DOMDocument $document): DOMXPath
    {
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', self::WORD_NAMESPACE);

        return $xpath;
    }

    private function temporaryPath(): string
    {
        $directory = storage_path('app/private/tmp');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $directory.DIRECTORY_SEPARATOR.uniqid('letterhead-', true).'.docx';
    }
}
