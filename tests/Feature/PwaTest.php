<?php

/**
 * Read the web app manifest that browsers use to install District III.
 *
 * @return array<string, mixed>
 */
function webAppManifest(): array
{
    return json_decode((string) file_get_contents(public_path('manifest.json')), true, flags: JSON_THROW_ON_ERROR);
}

it('links the manifest and app icons from every page', function (string $routeName) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertSee('<link rel="manifest" href="/manifest.json">', false)
        ->assertSee('<meta name="theme-color" content="#000d29">', false)
        ->assertSee('<link rel="apple-touch-icon" href="/apple-touch-icon.png?v=2">', false)
        ->assertSee('<link rel="icon" href="/favicon.ico?v=2"', false);
})->with(['home', 'login']);

it('describes an installable standalone app that opens the admin panel', function () {
    $manifest = webAppManifest();

    expect($manifest)
        ->name->toBe('Sorsogon Eagles District III')
        ->short_name->toBe('District III')
        ->start_url->toBe('/admin')
        ->display->toBe('standalone');

    $this->get($manifest['start_url'])->assertRedirect(route('login'));
});

it('ships every icon the manifest lists, including maskable ones', function () {
    $icons = collect(webAppManifest()['icons']);

    expect($icons->pluck('sizes')->unique()->values()->all())->toContain('192x192', '512x512')
        ->and($icons->where('purpose', 'maskable'))->not->toBeEmpty();

    $icons->each(function (array $icon): void {
        $path = public_path(ltrim($icon['src'], '/'));
        [$width, $height] = getimagesize($path) ?: [0, 0];

        expect(file_exists($path))->toBeTrue()
            ->and("{$width}x{$height}")->toBe($icon['sizes']);
    });
});

it('ships the service worker and its offline page', function () {
    expect(file_get_contents(public_path('sw.js')))->toContain("const OFFLINE_URL = '/offline.html'")
        ->and(file_exists(public_path('offline.html')))->toBeTrue();
});
