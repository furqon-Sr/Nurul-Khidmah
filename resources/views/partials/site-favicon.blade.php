@php
    $faviconPath = isset($settings) ? ($settings->favicon_path ?? null) : null;
    $faviconUrl = $faviconPath ? asset('storage/' . $faviconPath) : null;
    $ext = $faviconPath ? strtolower(pathinfo($faviconPath, PATHINFO_EXTENSION)) : null;
    $faviconMime = match ($ext) {
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        default => null,
    };
@endphp
@if ($faviconUrl && $faviconMime)
    <link rel="icon" href="{{ $faviconUrl }}" type="{{ $faviconMime }}">
    @if ($ext !== 'ico')
        <link rel="alternate icon" href="{{ $faviconUrl }}" sizes="any">
    @endif
    @if (in_array($ext, ['png', 'jpg', 'jpeg'], true))
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif
@else
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
@endif
