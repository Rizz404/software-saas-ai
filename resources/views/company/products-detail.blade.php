<x-company-stage :company="$company" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$product->translatedName($locale).' · '.$company->name">
    @if($products->isNotEmpty())
        <x-slot:sidebar>@include('company.products-nav')</x-slot:sidebar>
    @endif

    <div class="stage-panels">
        @include('company.products-scene')
    </div>
</x-company-stage>
