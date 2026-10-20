@php
    $itemName = $product->translatedName($locale);
    $itemUrl = fn ($item) => route('company.product', ['companySlug' => $company->slug, 'itemSlug' => $item->slug, 'lang' => $locale]);
    $knownTags = collect($product->tags ?? [])->filter(fn ($tag) => isset($terms['tag'][$tag]));
@endphp
<section class="stage-content @container" aria-label="{{ $itemName }}">
    <a href="{{ route('company.products', ['companySlug' => $company->slug, 'lang' => $locale]) }}" data-stage-exit class="panel-close" aria-label="{{ $labels['products_heading'] }}"><span aria-hidden="true">×</span></a>
    <article class="detail-scene">
        <div class="flex flex-wrap items-center justify-between gap-3 pr-12">
            <p class="stage-eyebrow">{{ $stageCopy['item_counter'] }} {{ $itemIndex + 1 }} / {{ $products->count() }}</p>
            @if ($products->count() > 1)
                <nav class="flex gap-2" aria-label="{{ $labels['products_heading'] }}">
                    <a href="{{ $itemUrl($previousItem) }}" data-stage-exit class="detail-scene-step" rel="prev"><span aria-hidden="true">←</span> {{ $stageCopy['prev_item'] }}</a>
                    <a href="{{ $itemUrl($nextItem) }}" data-stage-exit class="detail-scene-step" rel="next">{{ $stageCopy['next_item'] }} <span aria-hidden="true">→</span></a>
                </nav>
            @endif
        </div>

        @include('company.narrator', ['text' => $productsNarrations['item'][$product->slug], 'key' => 'item-'.$product->slug])

        <div class="mt-5 grid gap-7 @2xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
            <div class="detail-photo">
                @if ($product->image_source)
                    <img src="{{ $product->image_source }}" alt="{{ $itemName }}">
                @else
                    <div class="grid h-full place-items-center text-sm text-stone-500">{{ $stageCopy['no_photo'] }}</div>
                @endif
                @if ($product->is_unavailable)
                    <span class="detail-badge">{{ $stageCopy['sold_out'] }}</span>
                @endif
            </div>

            <div class="min-w-0">
                <h2 class="mt-0!">{{ $itemName }}</h2>
                <p class="mt-3 leading-relaxed text-stone-600">{{ $product->translatedDescription($locale) }}</p>

                <p class="detail-price mt-5">{{ $company->currency }} {{ number_format((float) $product->price, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-stone-500">{{ $stageCopy['price_note'] }}</p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button type="button" class="stage-action" data-hero-quick-message="{{ str_replace(':name', $itemName, $stageCopy['ask_item_q']) }}">{{ $stageCopy['ask_item'] }} <span aria-hidden="true">↗</span></button>
                    <a href="{{ route('company.consultation', ['companySlug' => $company->slug, 'lang' => $locale, 'product' => $product->slug]) }}" data-stage-exit data-tour-line="{{ $narration['tour_consultation'] }}" class="detail-scene-step">{{ $stageCopy['reserve_table'] }}</a>
                </div>

                <dl class="detail-scene-facts">
                    <div><dt>{{ $stageCopy['category'] }}</dt><dd>{{ $terms['category'][$product->category] ?? Str::headline($product->category) }}</dd></div>
                    @if ($product->delivery_model)
                        <div><dt>{{ $stageCopy['delivery_model'] }}</dt><dd>{{ Str::ucfirst($terms['delivery_model'][$product->delivery_model] ?? Str::headline($product->delivery_model)) }}</dd></div>
                    @endif
                    @if ($product->trial_days)
                        <div><dt>{{ $stageCopy['trial_days'] }}</dt><dd>{{ $product->trial_days }} {{ $stageCopy['days_unit'] }}</dd></div>
                    @endif
                </dl>

                @if ($knownTags->isNotEmpty())
                    <ul class="mt-5 flex flex-wrap gap-2">
                        @foreach ($knownTags as $tag)
                            <li class="tag-pill {{ in_array($tag, ['signature', 'bestseller'], true) ? 'is-accent' : '' }}">{{ $terms['tag'][$tag] }}</li>
                        @endforeach
                    </ul>
                @endif

                @if (! empty($product->prerequisites))
                    <h3 class="mt-6 text-sm font-semibold">{{ $stageCopy['contains'] }}</h3>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($product->prerequisites as $prerequisite)
                            <li class="tag-pill is-alert">{{ Str::ucfirst($terms['prerequisite'][$prerequisite] ?? $prerequisite) }}</li>
                        @endforeach
                    </ul>
                @endif
                <p class="detail-note">{{ $stageCopy['prerequisite_note'] }}</p>
            </div>
        </div>
        @include('company.product-plans')
    </article>
</section>
