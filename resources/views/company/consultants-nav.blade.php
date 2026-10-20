{{-- On the consultants scenes the side card becomes the area index, so the guest can step between areas without going back. --}}
<aside class="stage-products" aria-label="{{ $labels['consultants_heading'] }}">
    <a href="{{ route('company.show', ['companySlug' => $company->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" class="stage-products-back"><span aria-hidden="true">‹</span> {{ $stageCopy['explore'] }}</a>
    <p class="stage-eyebrow">{{ $labels['consultants_heading'] }}</p>
    <nav class="item-nav">
        @foreach ($consultants as $item)
            <a href="{{ route('company.consultant', ['companySlug' => $company->slug, 'areaSlug' => $item->slug, 'lang' => $locale]) }}"
                data-stage-exit
                @if (($consultant ?? null)?->slug === $item->slug) aria-current="page" @endif>
                @if ($item->images->first())
                    <img class="item-nav-thumb" src="{{ $item->images->first()->image_source }}" alt="" loading="lazy">
                @endif
                <span class="item-nav-text">
                    <span class="item-nav-name">{{ $item->translatedName($locale) }}</span>
                    <span class="item-nav-price">{{ $terms['specialty'][$item->specialty] ?? Str::headline($item->specialty) }} · {{ $item->min_participants > 1 ? $item->min_participants.'–' : '' }}{{ $item->max_participants }} {{ $stageCopy['participants_unit'] }}</span>
                </span>
            </a>
        @endforeach
    </nav>
    <span class="stage-products-footer">{{ $stageCopy['available'] }} · SOFTWARE SAAS AI</span>
</aside>
