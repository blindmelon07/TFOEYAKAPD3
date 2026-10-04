<?php

use App\Services\RichTextSanitizer;

beforeEach(function () {
    $this->sanitizer = new RichTextSanitizer;
});

it('keeps the formatting the editor offers', function () {
    $html = '<p style="text-align: center"><strong>Bold</strong> <em>italic</em> <u>underline</u></p>'
        .'<ul><li><p>One</p></li></ul><ol><li><p>Two</p></li></ol>'
        .'<table><tbody><tr><th colspan="2"><p>Head</p></th></tr><tr><td rowspan="2"><p>Cell</p></td></tr></tbody></table>';

    expect($this->sanitizer->sanitize($html))->toBe($html);
});

it('removes scripts and their contents', function () {
    expect($this->sanitizer->sanitize('<p>Hello</p><script>alert(1)</script><style>p{}</style>'))
        ->toBe('<p>Hello</p>');
});

it('strips event handlers and unsafe attributes', function () {
    expect($this->sanitizer->sanitize('<p onclick="steal()" style="text-align: right; background: url(x)" class="x">Hi</p>'))
        ->toBe('<p style="text-align: right">Hi</p>');
});

it('unwraps unknown tags but keeps their text', function () {
    expect($this->sanitizer->sanitize('<p>Visit <a href="javascript:alert(1)">our <span>site</span></a></p>'))
        ->toBe('<p>Visit our site</p>');
});

it('drops invalid cell spans', function () {
    expect($this->sanitizer->sanitize('<table><tr><td colspan="abc" rowspan="999"><p>x</p></td></tr></table>'))
        ->toBe('<table><tr><td><p>x</p></td></tr></table>');
});

it('keeps text characters escaped', function () {
    expect($this->sanitizer->sanitize('<p>Tom &amp; Jerry &lt;3</p>'))->toBe('<p>Tom &amp; Jerry &lt;3</p>');
});

it('leaves plain text untouched', function () {
    expect($this->sanitizer->sanitize("Line one\nLine two & more"))->toBe("Line one\nLine two & more");
});
