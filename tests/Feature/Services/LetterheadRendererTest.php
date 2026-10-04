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
