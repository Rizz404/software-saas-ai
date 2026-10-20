<x-company-stage :company="$company" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :nav="false" :title="$labels['consultation_heading'].' · '.$company->name">
    <div class="stage-panels">
        @include('company.consultation')
    </div>
</x-company-stage>
