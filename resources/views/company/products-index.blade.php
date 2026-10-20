<x-company-stage :company="$company" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms">
    <section class="stage-content catalog-panel @container">
        <p class="stage-eyebrow">{{ $company->name }}</p>
        <h2>{{ $labels['products_heading'] }}</h2>
        @if ($productsNarrations['products'])
            @include('company.narrator', ['text' => $productsNarrations['products'], 'key' => 'products', 'inline' => true])
        @endif
        <p class="detail-note">{{ $stageCopy['price_note'] }}</p>
        <div class="solution-grid">
            @forelse ($products as $item)
                <a class="solution-card" data-stage-exit href="{{ route('company.product', ['companySlug' => $company->slug, 'itemSlug' => $item->slug, 'lang' => $locale]) }}">
                    @if ($item->image_source)<img src="{{ $item->image_source }}" alt="" loading="lazy">@endif
                    <div class="solution-card-body">
                        <span class="stage-eyebrow">{{ $terms['category'][$item->category] ?? $item->category }}</span>
                        <h3>{{ $item->translatedName($locale) }}</h3>
                        <p>{{ $item->translatedDescription($locale) }}</p>
                        <span class="solution-card-price">{{ $labels['from'] }} {{ $company->currency }} {{ number_format((float) $item->price, 0, ',', '.') }} {{ $stageCopy['per_month'] }}</span>
                        <span class="solution-card-link">{{ $labels['view_details'] }} <span aria-hidden="true">↗</span></span>
                    </div>
                </a>
            @empty
                <p>{{ $stageCopy['products_empty'] }}</p>
            @endforelse
        </div>
    </section>
</x-company-stage>
