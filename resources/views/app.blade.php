<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="/favicon.ico?v=2" sizes="16x16 32x32 48x48">
        <link rel="icon" href="/icons/favicon-32.png?v=2" type="image/png" sizes="32x32">
        <link rel="icon" href="/icons/favicon-16.png?v=2" type="image/png" sizes="16x16">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=2">

        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#000d29">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="District III">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">

        @fonts

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
