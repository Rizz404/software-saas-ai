{{-- On the products scenes the side card becomes the products index, so the guest can step between products without going back. --}}
<aside class="stage-products" aria-label="{{ $labels['products_heading'] }}">
    <a href="{{ route('company.show', ['companySlug' => $company->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" class="stage-products-back"><span aria-hidden="true">‹</span> {{ $stageCopy['explore'] }}</a>
    <p class="stage-eyebrow">{{ $labels['products_heading'] }}</p>
    <nav class="item-nav">
        @foreach ($products->groupBy('category') as $category => $group)
            <p class="item-nav-heading">{{ $terms['category'][$category] ?? Str::headline($category) }}</p>
            @foreach ($group as $item)
                <a href="{{ route('company.product', ['companySlug' => $company->slug, 'itemSlug' => $item->slug, 'lang' => $locale]) }}"
                    data-stage-exit
                    @if (($product ?? null)?->slug === $item->slug) aria-current="page" @endif>
                    @if ($item->image_source)
                        <img class="item-nav-thumb" src="{{ $item->image_source }}" alt="" loading="lazy">
                    @endif
                    <span class="item-nav-text">
                        <span class="item-nav-name">{{ $item->translatedName($locale) }}</span>
                        <span class="item-nav-price">{{ $company->currency }} {{ number_format((float) $item->price, 0, ',', '.') }} {{ $stageCopy['per_month'] }}@if ($item->is_unavailable)<span class="item-nav-badge">{{ $stageCopy['sold_out'] }}</span>@endif</span>
                    </span>
                </a>
            @endforeach
        @endforeach
    </nav>
    <span class="stage-products-footer">{{ $stageCopy['available'] }} · SOFTWARE SAAS AI</span>
</aside>
