<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Reduces HTML from the club forms editor to the formatting it actually
 * offers: paragraphs, bold/italic/underline, lists, alignment and tables.
 *
 * Anything else is unwrapped (its text kept) or, for scripts and similar,
 * dropped with its content, and only harmless attributes survive. This keeps
 * stored content safe to show in the browser even if someone posts HTML
 * without going through the editor.
 */
class RichTextSanitizer
{
    /**
     * @var list<string>
     */
    private const array ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'colgroup', 'col',
    ];

    /**
     * @var list<string>
     */
    private const array DROPPED_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'template', 'svg', 'math', 'noscript', 'head', 'title', 'textarea', 'select',
    ];

    /**
     * Sanitize editor HTML. Plain text (no tags) is returned unchanged.
     */
    public function sanitize(string $html): string
    {
        if (trim($html) === '' || ! str_contains($html, '<')) {
            return $html;
        }

        $source = self::parse($html);
        $output = new DOMDocument;
        $container = $output->createElement('div');
        $output->appendChild($container);

        $this->copyChildren($source, $container, $output);

        $sanitized = '';

        foreach ($container->childNodes as $child) {
            $sanitized .= $output->saveHTML($child);
        }

        return $sanitized;
    }

    /**
     * Parse an HTML fragment as UTF-8 and return its <body>.
     */
    public static function parse(string $html): DOMElement
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>');
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $document->getElementsByTagName('body')->item(0);

        return $body ?? $document->createElement('body');
    }

    private function copyChildren(DOMNode $from, DOMNode $to, DOMDocument $output): void
    {
        foreach ($from->childNodes as $child) {
            if ($child instanceof DOMText) {
                $to->appendChild($output->createTextNode($child->textContent));

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROPPED_TAGS, true)) {
                continue;
            }

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                $this->copyChildren($child, $to, $output);

                continue;
            }

            $element = $output->createElement($tag);
            $this->copyAllowedAttributes($child, $element);
            $to->appendChild($element);
            $this->copyChildren($child, $element, $output);
        }
    }

    private function copyAllowedAttributes(DOMElement $from, DOMElement $to): void
    {
        if ($to->tagName === 'p' && preg_match('/text-align:\s*(left|center|right|justify)/i', $from->getAttribute('style'), $match)) {
            $to->setAttribute('style', 'text-align: '.strtolower($match[1]));
        }

        if (in_array($to->tagName, ['td', 'th'], true)) {
            foreach (['colspan', 'rowspan'] as $attribute) {
                $value = $from->getAttribute($attribute);

                if (ctype_digit($value) && (int) $value > 1 && (int) $value <= 50) {
                    $to->setAttribute($attribute, $value);
                }
            }
        }
    }
}
