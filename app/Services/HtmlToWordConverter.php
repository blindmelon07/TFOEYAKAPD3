<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Converts the club forms editor's HTML into WordprocessingML block elements
 * (paragraphs and tables) for a .docx document.
 *
 * Supported: paragraphs with alignment, bold, italic, underline, line
 * breaks, bullet and numbered lists (nested), and tables with header rows
 * and merged cells. Plain text without tags is treated as one paragraph per
 * line, which is how forms were stored before rich text existed.
 */
class HtmlToWordConverter
{
    private const string WORD_NAMESPACE = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    private const string XML_NAMESPACE = 'http://www.w3.org/XML/1998/namespace';

    /**
     * Text width of the letterhead page (8.5 in minus 0.83 in margins), in twips.
     */
    private const int TEXT_WIDTH = 9858;

    private const int LIST_INDENT = 720;

    private const int LIST_HANGING = 360;

    /**
     * @var array<string, string>
     */
    private const array ALIGNMENTS = ['left' => 'left', 'center' => 'center', 'right' => 'right', 'justify' => 'both'];

    private DOMDocument $document;

    /**
     * Build the body blocks for the given Word document.
     *
     * @return list<DOMElement>
     */
    public function convert(DOMDocument $document, string $content): array
    {
        $this->document = $document;

        if (trim($content) === '') {
            return [];
        }

        if (! str_contains($content, '<')) {
            return $this->plainTextBlocks($content);
        }

        $blocks = [];
        $this->appendBlocks(RichTextSanitizer::parse($content), $blocks);

        while ($blocks !== [] && $this->isEmptyParagraph($blocks[array_key_last($blocks)])) {
            array_pop($blocks);
        }

        return $blocks;
    }

    /**
     * An empty paragraph, used to leave a line between the date and the body.
     */
    public function spacer(DOMDocument $document): DOMElement
    {
        $this->document = $document;

        return $this->paragraph([], null);
    }

    /**
     * @return list<DOMElement>
     */
    private function plainTextBlocks(string $content): array
    {
        $lines = preg_split('/\R/', $content) ?: [];

        while ($lines !== [] && trim((string) end($lines)) === '') {
            array_pop($lines);
        }

        return array_map(
            fn (string $line): DOMElement => $this->paragraph($line === '' ? [] : [$this->textRun($line, [])], 'both'),
            $lines,
        );
    }

    /**
     * @param  list<DOMElement>  $blocks
     */
    private function appendBlocks(DOMNode $parent, array &$blocks, int $listLevel = 0): void
    {
        $looseInline = [];

        foreach ($parent->childNodes as $child) {
            if ($child instanceof DOMText) {
                if (trim($child->textContent) !== '') {
                    $looseInline = [...$looseInline, ...$this->runs($child, [])];
                }

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, ['strong', 'b', 'em', 'i', 'u', 'br'], true)) {
                $looseInline = [...$looseInline, ...$this->runs($child, [])];

                continue;
            }

            $this->flushInline($looseInline, $blocks);

            match ($tag) {
                'p' => $blocks[] = $this->paragraph($this->runs($child, []), $this->alignmentOf($child)),
                'ul', 'ol' => $this->appendList($child, $blocks, $listLevel),
                'table' => $blocks[] = $this->table($child),
                default => $this->appendBlocks($child, $blocks, $listLevel),
            };
        }

        $this->flushInline($looseInline, $blocks);
    }

    /**
     * Wrap text that sat directly between blocks into its own paragraph.
     *
     * @param  list<DOMElement>  $looseInline
     * @param  list<DOMElement>  $blocks
     */
    private function flushInline(array &$looseInline, array &$blocks): void
    {
        if ($looseInline !== []) {
            $blocks[] = $this->paragraph($looseInline, null);
            $looseInline = [];
        }
    }

    /**
     * Lists become indented paragraphs with a bullet or number and a hanging
     * indent, which Word shows exactly like a list without needing the
     * template to define list styles.
     *
     * @param  list<DOMElement>  $blocks
     */
    private function appendList(DOMElement $list, array &$blocks, int $level): void
    {
        $isNumbered = strtolower($list->tagName) === 'ol';
        $number = 0;

        foreach ($list->childNodes as $item) {
            if (! $item instanceof DOMElement || strtolower($item->tagName) !== 'li') {
                continue;
            }

            $number++;
            $marker = $isNumbered ? "{$number}." : ($level % 2 === 0 ? '•' : '◦');
            $isFirstParagraph = true;

            foreach ($item->childNodes as $part) {
                if ($part instanceof DOMElement && in_array(strtolower($part->tagName), ['ul', 'ol'], true)) {
                    $this->appendList($part, $blocks, $level + 1);

                    continue;
                }

                $runs = $this->runs($part, []);

                if ($runs === [] && ! $isFirstParagraph) {
                    continue;
                }

                $prefix = $isFirstParagraph
                    ? [$this->textRun($marker, []), $this->tabRun()]
                    : [];

                $blocks[] = $this->paragraph(
                    [...$prefix, ...$runs],
                    $part instanceof DOMElement ? $this->alignmentOf($part) : null,
                    self::LIST_INDENT * ($level + 1),
                    $isFirstParagraph ? self::LIST_HANGING : 0,
                    after: 60,
                );

                $isFirstParagraph = false;
            }
        }
    }

    /**
     * Convert inline content into runs, carrying bold/italic/underline down.
     *
     * @param  array{bold?: bool, italic?: bool, underline?: bool}  $format
     * @return list<DOMElement>
     */
    private function runs(DOMNode $node, array $format): array
    {
        if ($node instanceof DOMText) {
            $text = preg_replace('/\s+/u', ' ', $node->textContent) ?? '';

            return $text === '' ? [] : [$this->textRun($text, $format)];
        }

        if (! $node instanceof DOMElement) {
            return [];
        }

        $tag = strtolower($node->tagName);

        if ($tag === 'br') {
            return [$this->breakRun()];
        }

        $format = match ($tag) {
            'strong', 'b' => [...$format, 'bold' => true],
            'em', 'i' => [...$format, 'italic' => true],
            'u' => [...$format, 'underline' => true],
            default => $format,
        };

        $runs = [];

        foreach ($node->childNodes as $child) {
            $runs = [...$runs, ...$this->runs($child, $format)];
        }

        return $runs;
    }

    private function table(DOMElement $table): DOMElement
    {
        $rows = [];

        foreach ($table->getElementsByTagName('tr') as $row) {
            $rows[] = $row;
        }

        $columnCount = max(1, $this->columnCount($rows[0] ?? null));
        $columnWidth = intdiv(self::TEXT_WIDTH, $columnCount);

        $wordTable = $this->element('w:tbl');

        $properties = $this->append($wordTable, 'w:tblPr');
        $this->append($properties, 'w:tblW', ['w:w' => (string) ($columnWidth * $columnCount), 'w:type' => 'dxa']);

        $borders = $this->append($properties, 'w:tblBorders');

        foreach (['top', 'left', 'bottom', 'right', 'insideH', 'insideV'] as $side) {
            $this->append($borders, "w:{$side}", ['w:val' => 'single', 'w:sz' => '4', 'w:space' => '0', 'w:color' => '000000']);
        }

        $this->append($properties, 'w:tblLayout', ['w:type' => 'fixed']);

        $margins = $this->append($properties, 'w:tblCellMar');
        $this->append($margins, 'w:left', ['w:w' => '100', 'w:type' => 'dxa']);
        $this->append($margins, 'w:right', ['w:w' => '100', 'w:type' => 'dxa']);

        $grid = $this->append($wordTable, 'w:tblGrid');

        for ($column = 0; $column < $columnCount; $column++) {
            $this->append($grid, 'w:gridCol', ['w:w' => (string) $columnWidth]);
        }

        /** @var array<int, array{remaining: int, span: int}> $mergedFromAbove */
        $mergedFromAbove = [];

        foreach ($rows as $row) {
            $wordRow = $this->append($wordTable, 'w:tr');
            $cells = $this->cellsOf($row);

            if ($cells !== [] && array_all($cells, fn (DOMElement $cell): bool => strtolower($cell->tagName) === 'th')) {
                $this->append($this->append($wordRow, 'w:trPr'), 'w:tblHeader');
            }

            $column = 0;
            $cellIndex = 0;

            while ($cellIndex < count($cells) || $this->hasMergeAtOrAfter($mergedFromAbove, $column)) {
                if (isset($mergedFromAbove[$column])) {
                    $span = $mergedFromAbove[$column]['span'];
                    $wordRow->appendChild($this->continuationCell($span * $columnWidth, $span));

                    if (--$mergedFromAbove[$column]['remaining'] === 0) {
                        unset($mergedFromAbove[$column]);
                    }

                    $column += $span;

                    continue;
                }

                if ($cellIndex >= count($cells)) {
                    $column++;

                    continue;
                }

                $cell = $cells[$cellIndex++];
                $span = max(1, (int) $cell->getAttribute('colspan'));
                $rowSpan = max(1, (int) $cell->getAttribute('rowspan'));

                $wordRow->appendChild($this->cell($cell, $span * $columnWidth, $span, $rowSpan > 1));

                if ($rowSpan > 1) {
                    $mergedFromAbove[$column] = ['remaining' => $rowSpan - 1, 'span' => $span];
                }

                $column += $span;
            }
        }

        return $wordTable;
    }

    private function cell(DOMElement $cell, int $width, int $span, bool $startsVerticalMerge): DOMElement
    {
        $isHeader = strtolower($cell->tagName) === 'th';
        $wordCell = $this->element('w:tc');

        $properties = $this->append($wordCell, 'w:tcPr');
        $this->append($properties, 'w:tcW', ['w:w' => (string) $width, 'w:type' => 'dxa']);

        if ($span > 1) {
            $this->append($properties, 'w:gridSpan', ['w:val' => (string) $span]);
        }

        if ($startsVerticalMerge) {
            $this->append($properties, 'w:vMerge', ['w:val' => 'restart']);
        }

        if ($isHeader) {
            $this->append($properties, 'w:shd', ['w:val' => 'clear', 'w:color' => 'auto', 'w:fill' => 'EBEEF3']);
        }

        $format = $isHeader ? ['bold' => true] : [];
        $paragraphs = 0;

        foreach ($cell->childNodes as $part) {
            $runs = $this->runs($part, $format);

            if ($part instanceof DOMText && $runs === []) {
                continue;
            }

            $wordCell->appendChild($this->paragraph(
                $runs,
                $part instanceof DOMElement ? $this->alignmentOf($part) : null,
                after: 0,
            ));
            $paragraphs++;
        }

        if ($paragraphs === 0) {
            $wordCell->appendChild($this->paragraph([], null, after: 0));
        }

        return $wordCell;
    }

    /**
     * The placeholder cell Word needs where a cell above spans down into this row.
     */
    private function continuationCell(int $width, int $span): DOMElement
    {
        $wordCell = $this->element('w:tc');
        $properties = $this->append($wordCell, 'w:tcPr');
        $this->append($properties, 'w:tcW', ['w:w' => (string) $width, 'w:type' => 'dxa']);

        if ($span > 1) {
            $this->append($properties, 'w:gridSpan', ['w:val' => (string) $span]);
        }

        $this->append($properties, 'w:vMerge');
        $wordCell->appendChild($this->paragraph([], null, after: 0));

        return $wordCell;
    }

    /**
     * @return list<DOMElement>
     */
    private function cellsOf(DOMElement $row): array
    {
        $cells = [];

        foreach ($row->childNodes as $child) {
            if ($child instanceof DOMElement && in_array(strtolower($child->tagName), ['td', 'th'], true)) {
                $cells[] = $child;
            }
        }

        return $cells;
    }

    private function columnCount(?DOMElement $firstRow): int
    {
        if ($firstRow === null) {
            return 1;
        }

        return array_sum(array_map(
            fn (DOMElement $cell): int => max(1, (int) $cell->getAttribute('colspan')),
            $this->cellsOf($firstRow),
        ));
    }

    /**
     * @param  array<int, array{remaining: int, span: int}>  $mergedFromAbove
     */
    private function hasMergeAtOrAfter(array $mergedFromAbove, int $column): bool
    {
        return array_any(array_keys($mergedFromAbove), fn (int $mergedColumn): bool => $mergedColumn >= $column);
    }

    private function alignmentOf(DOMElement $element): ?string
    {
        if (preg_match('/text-align:\s*(left|center|right|justify)/i', $element->getAttribute('style'), $match)) {
            return self::ALIGNMENTS[strtolower($match[1])];
        }

        return null;
    }

    /**
     * @param  list<DOMElement>  $runs
     */
    private function paragraph(array $runs, ?string $alignment, int $indentLeft = 0, int $hanging = 0, int $after = 120): DOMElement
    {
        $paragraph = $this->element('w:p');
        $properties = $this->append($paragraph, 'w:pPr');
        $this->append($properties, 'w:spacing', ['w:after' => (string) $after, 'w:line' => '276', 'w:lineRule' => 'auto']);

        if ($indentLeft > 0 || $hanging > 0) {
            $this->append($properties, 'w:ind', ['w:left' => (string) $indentLeft, 'w:hanging' => (string) $hanging]);
        }

        if ($alignment !== null) {
            $this->append($properties, 'w:jc', ['w:val' => $alignment]);
        }

        foreach ($runs as $run) {
            $paragraph->appendChild($run);
        }

        return $paragraph;
    }

    /**
     * @param  array{bold?: bool, italic?: bool, underline?: bool}  $format
     */
    private function textRun(string $text, array $format): DOMElement
    {
        $run = $this->runWithProperties($format);
        $textElement = $this->append($run, 'w:t');
        $textElement->appendChild(new DOMText($text));
        $textElement->setAttributeNS(self::XML_NAMESPACE, 'xml:space', 'preserve');

        return $run;
    }

    private function tabRun(): DOMElement
    {
        $run = $this->runWithProperties([]);
        $this->append($run, 'w:tab');

        return $run;
    }

    private function breakRun(): DOMElement
    {
        $run = $this->runWithProperties([]);
        $this->append($run, 'w:br');

        return $run;
    }

    /**
     * @param  array{bold?: bool, italic?: bool, underline?: bool}  $format
     */
    private function runWithProperties(array $format): DOMElement
    {
        $run = $this->element('w:r');
        $properties = $this->append($run, 'w:rPr');
        $this->append($properties, 'w:rFonts', ['w:ascii' => 'Arial', 'w:hAnsi' => 'Arial', 'w:cs' => 'Arial']);

        if ($format['bold'] ?? false) {
            $this->append($properties, 'w:b');
            $this->append($properties, 'w:bCs');
        }

        if ($format['italic'] ?? false) {
            $this->append($properties, 'w:i');
            $this->append($properties, 'w:iCs');
        }

        if ($format['underline'] ?? false) {
            $this->append($properties, 'w:u', ['w:val' => 'single']);
        }

        $this->append($properties, 'w:sz', ['w:val' => '24']);
        $this->append($properties, 'w:szCs', ['w:val' => '24']);

        return $run;
    }

    private function isEmptyParagraph(DOMElement $block): bool
    {
        return $block->localName === 'p' && $block->getElementsByTagNameNS(self::WORD_NAMESPACE, 'r')->length === 0;
    }

    /**
     * @param  array<string, string>  $attributes
     */
    private function append(DOMElement $parent, string $name, array $attributes = []): DOMElement
    {
        $element = $this->element($name, $attributes);
        $parent->appendChild($element);

        return $element;
    }

    /**
     * @param  array<string, string>  $attributes
     */
    private function element(string $name, array $attributes = []): DOMElement
    {
        $element = $this->document->createElementNS(self::WORD_NAMESPACE, $name);

        foreach ($attributes as $attribute => $value) {
            $element->setAttributeNS(self::WORD_NAMESPACE, $attribute, $value);
        }

        return $element;
    }
}
