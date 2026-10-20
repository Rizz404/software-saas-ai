<x-company-stage :company="$company" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$consultant->translatedName($locale).' · '.$company->name">
    @if($consultants->isNotEmpty())
        <x-slot:sidebar>@include('company.consultants-nav')</x-slot:sidebar>
    @endif

    <div class="stage-panels">
        @include('company.consultants-scene')
    </div>
</x-company-stage>
