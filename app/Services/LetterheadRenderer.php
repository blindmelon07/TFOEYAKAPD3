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
 * The body may be rich text (HTML from the forms editor) or plain text; see
 * HtmlToWordConverter for the formatting carried over.
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

    public function __construct(private HtmlToWordConverter $converter = new HtmlToWordConverter) {}

    /**
     * Render a filled-in copy of the template and return its temporary path.
     *
     * @param  array<string, string>  $placeholders  Placeholder text mapped to its replacement.
     */
    public function render(string $templatePath, array $placeholders, string $body): string
    {
        $template = new ZipArchive;

        if ($template->open($templatePath, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException("The letterhead template [{$templatePath}] is not a valid .docx file.");
        }

        // Write a brand-new archive rather than editing a copy of the
        // template: on Windows, a freshly copied file can still be locked
        // (e.g. by antivirus scanning) when the archive is saved.
        $outputPath = $this->temporaryPath();
        $output = new ZipArchive;

        if ($output->open($outputPath, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
            $template->close();

            throw new RuntimeException("Could not create the document [{$outputPath}].");
        }

        for ($index = 0; $index < $template->numFiles; $index++) {
            $partName = (string) $template->getNameIndex($index);
            $contents = (string) $template->getFromIndex($index);

            if (preg_match('#^word/(document|header\d*|footer\d*)\.xml$#', $partName)) {
                $document = new DOMDocument;
                $document->loadXML($contents);

                if ($partName === 'word/document.xml') {
                    $this->insertBody($document, $placeholders, $body);
                }

                $this->fillPlaceholders($document, $placeholders);
                $contents = (string) $document->saveXML();
            }

            $output->addFromString($partName, $contents);
        }

        $template->close();

        if (! $output->close()) {
            throw new RuntimeException("Could not save the document [{$outputPath}].");
        }

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
        $blocks = $this->converter->convert($document, $body);

        foreach ($paragraphs as $paragraph) {
            if (trim($this->textOf($xpath, $paragraph)) === self::BODY_PLACEHOLDER) {
                foreach ($blocks as $block) {
                    $paragraph->parentNode?->insertBefore($block, $paragraph);
                }

                $paragraph->parentNode?->removeChild($paragraph);

                return;
            }
        }

        if ($blocks === []) {
            return;
        }

        $anchor = $this->findParagraphContaining($xpath, $paragraphs, '{date}')
            ?? $this->findParagraphContaining($xpath, $paragraphs, '[title]')
            ?? ($paragraphs === [] ? null : $paragraphs[array_key_last($paragraphs)]);

        if ($anchor === null) {
            return;
        }

        $nextSibling = $anchor->nextSibling;

        foreach ([$this->converter->spacer($document), ...$blocks] as $block) {
            $anchor->parentNode?->insertBefore($block, $nextSibling);
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
