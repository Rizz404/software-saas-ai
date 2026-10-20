<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software SaaS AI</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="stage-page antialiased" style="--solution_consultant-image: url('{{ $avatarImage }}'); --stage-focus: {{ $backdrop['focus'] }}; --stage-focus-mobile: {{ $backdrop['focusMobile'] }}">
    <main class="stage" data-stage data-scene="opening">
        <div class="stage-loader" data-stage-loader role="status"><span class="company-monogram" aria-hidden="true">S</span><p>{{ $opening['loading'] }}</p></div>
        <img src="{{ $backdrop['image'] }}" alt="" class="stage-image" fetchpriority="high">
        <div class="stage-shade"></div>
        <img src="{{ $backdrop['character'] }}" alt="" class="stage-character" data-anchor="right">

        <header class="stage-header">
            <span class="flex min-w-0 items-center gap-3">
                <span class="company-monogram" aria-hidden="true">S</span>
                <span class="block truncate font-semibold tracking-tight">Software SaaS AI</span>
            </span>
            <div class="stage-tools">
                <x-sound-toggle :on="$opening['sound_on']" :off="$opening['sound_off']" />
                <nav aria-label="Language" class="stage-lang">
                    @foreach ($supportedLocales as $code)
                        <a href="?lang={{ $code }}" lang="{{ $code }}" aria-label="{{ ['id' => 'Bahasa Indonesia', 'en' => 'English', 'ja' => '日本語'][$code] }}" @if($code === $locale) aria-current="true" @endif>{{ $code }}</a>
                    @endforeach
                </nav>
            </div>
        </header>

        <section class="opening-panel" aria-labelledby="opening-title">
            <p class="stage-eyebrow">AI SAAS CONSULTANT</p>
            <h1 id="opening-title">{{ $opening['welcome'] }}<br><em>Software SaaS AI</em></h1>
            <p class="opening-tagline">{{ $opening['tagline'] }}</p>

            @if ($companies->count() === 1)
                @php
                    $companySlug = $companies->first()->slug;
                    $openingLinks = [
                        'products' => route('company.products', ['companySlug' => $companySlug, 'lang' => $locale]),
                        'consultants' => route('company.consultants', ['companySlug' => $companySlug, 'lang' => $locale]),
                        'consultation' => route('company.consultation', ['companySlug' => $companySlug, 'lang' => $locale]),
                        'staff' => route('company.staff', ['companySlug' => $companySlug, 'lang' => $locale]),
                    ];
                @endphp
                <ul class="opening-links">
                    @foreach ($openingLinks as $key => $href)
                        <li><a data-stage-exit href="{{ $href }}"><span>{{ $opening[$key] }}</span><span aria-hidden="true">›</span></a></li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="opening-enter">
            @forelse ($companies as $company)
                <a href="{{ route('company.show', ['companySlug' => $company->slug, 'lang' => $locale]) }}" class="opening-button" data-stage-exit>
                    {{ str_replace(':name', $company->name, $opening['enter']) }} <span aria-hidden="true">→</span>
                </a>
            @empty
                <p class="opening-empty">{{ $opening['empty'] }}</p>
            @endforelse
        </div>
    </main>
</body>
</html>
