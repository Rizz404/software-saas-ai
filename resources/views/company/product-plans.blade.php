@if (! empty($product->subscription_plans))
    <section class="product-plans" aria-label="{{ $stageCopy['plans_heading'] }}">
        <p class="stage-eyebrow">{{ $company->currency }} / SAAS</p>
        <h3>{{ $stageCopy['plans_heading'] }}</h3>
        <div class="plan-grid">
            @foreach ($product->subscription_plans as $plan)
                <article class="plan-card {{ $plan['key'] === 'growth' ? 'is-featured' : '' }}">
                    <span class="plan-index">0{{ $loop->iteration }}</span>
                    <h4>{{ $plan['name'] }}</h4>
                    <p class="plan-price">{{ number_format((float) $plan['monthly_price'], 0, ',', '.') }}</p>
                    <span class="plan-period">{{ $company->currency }} {{ $stageCopy['per_month'] }}</span>
                    <p class="plan-seats">{{ $plan['seats'] }} {{ $stageCopy['seats'] }}</p>
                    <ul>@foreach ($plan['features'] as $feature)<li>{{ $terms['plan_feature'][$feature] ?? $feature }}</li>@endforeach</ul>
                    <a class="stage-action" href="{{ route('company.consultation', ['companySlug' => $company->slug, 'lang' => $locale, 'product' => $product->slug, 'plan' => $plan['key']]) }}" data-stage-exit>{{ $stageCopy['plan_demo'] }} <span aria-hidden="true">→</span></a>
                </article>
            @endforeach
        </div>
        <p class="detail-note">{{ $stageCopy['plan_note'] }}</p>
    </section>
@endif
