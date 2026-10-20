<x-company-stage :company="$company" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms">
    <section class="stage-content catalog-panel @container">
        <p class="stage-eyebrow">{{ $company->name }}</p>
        <h2>{{ $labels['consultants_heading'] }}</h2>
        @if ($consultantsNarrations['consultants'])
            @include('company.narrator', ['text' => $consultantsNarrations['consultants'], 'key' => 'consultants', 'inline' => true])
        @endif
        <div class="solution-grid">
            @forelse ($consultants as $item)
                <a class="solution-card" data-stage-exit href="{{ route('company.consultant', ['companySlug' => $company->slug, 'areaSlug' => $item->slug, 'lang' => $locale]) }}">
                    @if ($item->images->first()?->image_source)<img src="{{ $item->images->first()?->image_source }}" alt="" loading="lazy">@endif
                    <div class="solution-card-body">
                        <span class="stage-eyebrow">{{ $terms['specialty'][$item->specialty] ?? $item->specialty }}</span>
                        <h3>{{ $item->translatedName($locale) }}</h3>
                        <p>{{ $item->translatedDescription($locale) }}</p>
                        <span class="solution-card-price">60 min · {{ $item->min_participants }}–{{ $item->max_participants }} {{ $stageCopy['participants_unit'] }}</span>
                        <span class="solution-card-link">{{ $labels['view_details'] }} <span aria-hidden="true">↗</span></span>
                    </div>
                </a>
            @empty
                <p>{{ $stageCopy['consultants_empty'] }}</p>
            @endforelse
        </div>
    </section>
</x-company-stage>
