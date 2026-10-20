<x-company-stage :company="$company" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms">
    <x-slot:welcome>
        <section class="stage-welcome">
            <span class="stage-eyebrow">{{ $company->name }} / {{ $stageCopy['assistant'] }}</span>
            <h1>{{ $stageCopy['welcome'] }}<br><em>{{ $stageCopy['counter'] }}.</em></h1>
            <p>{{ $stageCopy['intro'] }}</p>
            <div class="welcome-actions">
                <a class="stage-action" data-stage-exit data-tour-line="{{ $narration['tour_products'] }}" href="{{ route('company.products', ['companySlug' => $company->slug, 'lang' => $locale]) }}">{{ $labels['chip_products'] }} <span aria-hidden="true">→</span></a>
                <a class="welcome-secondary" data-stage-exit data-tour-line="{{ $narration['tour_consultation'] }}" href="{{ route('company.consultation', ['companySlug' => $company->slug, 'lang' => $locale]) }}">{{ $labels['chip_consultation'] }}</a>
            </div>
            <div class="home-product-strip">
                @foreach ($products->take(3) as $product)
                    <a data-stage-exit href="{{ route('company.product', ['companySlug' => $company->slug, 'itemSlug' => $product->slug, 'lang' => $locale]) }}">
                        <span>0{{ $loop->iteration }}</span><strong>{{ $product->translatedName($locale) }}</strong><span aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </div>
            <div class="welcome-footnote"><span>SOFTWARE / WORKSPACES / GROWTH</span><span>01 — {{ $company->city }}</span></div>
        </section>
    </x-slot:welcome>
</x-company-stage>
