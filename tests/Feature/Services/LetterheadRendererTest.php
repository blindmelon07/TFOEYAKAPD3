<?php

use App\Services\LetterheadRenderer;

/**
 * Read the main document XML of a generated .docx.
 */
function documentXml(string $path): string
{
    $zip = new ZipArchive;
    $zip->open($path);
    $xml = (string) $zip->getFromName('word/document.xml');
    $zip->close();

    return $xml;
}

/**
 * Get every piece of visible text in a .docx, in order.
 *
 * @return list<string>
 */
function documentTexts(string $path): array
{
    preg_match_all('#<w:t(?:\s[^>]*)?>([^<]*)</w:t>#', documentXml($path), $matches);

    return array_values(array_filter(
        array_map(fn (string $text): string => html_entity_decode($text, ENT_QUOTES | ENT_XML1), $matches[1]),
        fn (string $text): bool => trim($text) !== '',
    ));
}

/**
 * Build a minimal .docx whose body is the given paragraph XML.
 */
function minimalDocx(string $paragraphsXml): string
{
    $path = tempnam(sys_get_temp_dir(), 'docx');
    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>');
    $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$paragraphsXml.'<w:sectPr/></w:body></w:document>');
    $zip->close();

    return $path;
}

beforeEach(function () {
    $this->renderer = new LetterheadRenderer;
    $this->generated = [];
});

afterEach(function () {
    foreach ($this->generated as $path) {
        @unlink($path);
    }
});

it('fills the district letterhead and keeps its banner image', function () {
    $path = $this->generated[] = $this->renderer->render(
        resource_path('templates/letterhead.docx'),
        ['[title]' => 'MEMORANDUM', '{date}' => 'October 5, 2026'],
        "To all members:\n\nPlease attend the general assembly.",
    );

    $xml = documentXml($path);

    expect(documentTexts($path))->toBe([
        'MEMORANDUM',
        '           October 5, 2026',
        'To all members:',
        'Please attend the general assembly.',
    ])
        ->and($xml)->not->toContain('[title]')
        ->and($xml)->not->toContain('{date}')
        ->and($xml)->toContain('r:embed="rId8"');

    $zip = new ZipArchive;
    $zip->open($path);
    expect($zip->locateName('word/media/image1.png'))->not->toBeFalse();
    $zip->close();
});

it('escapes special characters in the filled text', function () {
    $path = $this->generated[] = $this->renderer->render(
        resource_path('templates/letterhead.docx'),
        ['[title]' => 'Q&A <Session>', '{date}' => 'today'],
        'Bring "snacks" & drinks',
    );

    expect(documentXml($path))->toContain('Q&amp;A &lt;Session&gt;')
        ->and(documentTexts($path))->toContain('Bring "snacks" & drinks');
});

it('fills a placeholder that Word split across several runs', function () {
    $template = minimalDocx('<w:p><w:r><w:t>[ti</w:t></w:r><w:r><w:t>tle]</w:t></w:r></w:p>');
    $path = $this->generated[] = $this->renderer->render($template, ['[title]' => 'NOTICE'], '');
    unlink($template);

    expect(documentTexts($path))->toBe(['NOTICE']);
});

it('puts the body where a {body} paragraph is', function () {
    $template = minimalDocx(
        '<w:p><w:r><w:t>[title]</w:t></w:r></w:p>'
        .'<w:p><w:r><w:t>{body}</w:t></w:r></w:p>'
        .'<w:p><w:r><w:t>Signed by the Secretary</w:t></w:r></w:p>',
    );
    $path = $this->generated[] = $this->renderer->render($template, ['[title]' => 'NOTICE'], "Line one\nLine two");
    unlink($template);

    expect(documentTexts($path))->toBe(['NOTICE', 'Line one', 'Line two', 'Signed by the Secretary']);
});

it('recognises valid and invalid templates', function () {
    $text = tempnam(sys_get_temp_dir(), 'txt');
    file_put_contents($text, 'not a word document');

    expect(LetterheadRenderer::isValidTemplate(resource_path('templates/letterhead.docx')))->toBeTrue()
        ->and(LetterheadRenderer::isValidTemplate($text))->toBeFalse();

    unlink($text);
});

describe('rich text', function () {
    /**
     * Render rich text into a minimal template and return the body XML.
     */
    function renderRichText(string $html): string
    {
        $template = minimalDocx('<w:p><w:r><w:t>{body}</w:t></w:r></w:p>');
        $path = (new LetterheadRenderer)->render($template, [], $html);
        $xml = documentXml($path);
        unlink($template);
        unlink($path);

        return $xml;
    }

    it('carries bold, italic and underline into the runs', function () {
        $xml = renderRichText('<p><strong>Bold</strong> <em>Italic</em> <u>Under</u> <strong><em>Both</em></strong></p>');

        expect($xml)
            ->toMatch('#<w:b/>.*?<w:t[^>]*>Bold</w:t>#s')
            ->toMatch('#<w:r><w:rPr><w:rFonts[^>]*/><w:i/><w:iCs/><w:sz[^>]*/><w:szCs[^>]*/></w:rPr><w:t[^>]*>Italic</w:t>#')
            ->toMatch('#<w:u w:val="single"/>.*?<w:t[^>]*>Under</w:t>#s')
            ->toMatch('#<w:b/><w:bCs/><w:i/><w:iCs/>.*?<w:t[^>]*>Both</w:t>#s');
    });

    it('carries paragraph alignment', function (string $alignment, string $wordValue) {
        expect(renderRichText("<p style=\"text-align: {$alignment}\">Text</p>"))
            ->toContain("<w:jc w:val=\"{$wordValue}\"/>");
    })->with([
        'center' => ['center', 'center'],
        'right' => ['right', 'right'],
        'justify' => ['justify', 'both'],
    ]);

    it('turns lists into indented bullet and number paragraphs', function () {
        $xml = renderRichText('<ul><li><p>Apples</p></li><li><p>Pears</p><ul><li><p>Nested</p></li></ul></li></ul><ol><li><p>First</p></li><li><p>Second</p></li></ol>');

        expect($xml)
            ->toMatch('#<w:ind w:left="720" w:hanging="360"/>.*?<w:t[^>]*>•</w:t>.*?<w:tab/>.*?<w:t[^>]*>Apples</w:t>#s')
            ->toMatch('#<w:ind w:left="1440" w:hanging="360"/>.*?<w:t[^>]*>◦</w:t>.*?<w:t[^>]*>Nested</w:t>#s')
            ->toMatch('#<w:t[^>]*>1\.</w:t>.*?<w:t[^>]*>First</w:t>#s')
            ->toMatch('#<w:t[^>]*>2\.</w:t>.*?<w:t[^>]*>Second</w:t>#s');
    });

    it('builds a bordered table with a repeating, shaded header row', function () {
        $xml = renderRichText('<table><tbody><tr><th><p>Name</p></th><th><p>Signature</p></th></tr><tr><td><p>Juan</p></td><td><p></p></td></tr></tbody></table>');

        expect($xml)
            ->toContain('<w:tbl>')
            ->toContain('<w:gridCol w:w="4929"/><w:gridCol w:w="4929"/>')
            ->toContain('<w:insideV w:val="single"')
            ->toContain('<w:tblHeader/>')
            ->toContain('w:fill="EBEEF3"')
            ->toMatch('#<w:b/>.*?<w:t[^>]*>Name</w:t>#s')
            ->toContain('>Juan</w:t>');

        expect(substr_count($xml, '<w:tc>'))->toBe(4);
    });

    it('merges cells across and down', function () {
        $xml = renderRichText('<table><tbody>'
            .'<tr><td colspan="2"><p>Wide</p></td><td rowspan="2"><p>Tall</p></td></tr>'
            .'<tr><td><p>A</p></td><td><p>B</p></td></tr>'
            .'</tbody></table>');

        expect($xml)
            ->toContain('<w:gridSpan w:val="2"/>')
            ->toContain('<w:vMerge w:val="restart"/>')
            ->toContain('<w:vMerge/>')
            ->and(substr_count($xml, '<w:gridCol '))->toBe(3)
            ->and(substr_count($xml, '<w:tc>'))->toBe(5);
    });

    it('keeps line breaks inside a paragraph', function () {
        expect(renderRichText('<p>Line one<br>Line two</p>'))
            ->toMatch('#>Line one</w:t>.*?<w:br/>.*?>Line two</w:t>#s');
    });
});
