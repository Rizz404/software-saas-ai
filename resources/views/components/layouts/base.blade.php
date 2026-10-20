{{-- The document shell for every non-stage page: one Vite bundle, CSRF meta, skip link. --}}
@props([
    'title' => null,
    'companyName' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — '.($companyName ?? config('app.name')) : ($companyName ?? config('app.name')) }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body {{ $attributes->class('min-h-dvh bg-canvas font-sans text-text antialiased') }}>
    <a href="#main-content" class="skip-link">Skip to content</a>

    {{ $slot }}
</body>
</html>
