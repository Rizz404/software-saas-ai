@php($isEdit = $consultant->exists)

<x-layouts.admin :title="$isEdit ? 'Edit consultant' : 'Add consultant'">
    <form method="POST" action="{{ $isEdit ? route('admin.consultants.update', $consultant) : route('admin.consultants.store') }}" class="space-y-8">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <x-ui.card heading="Basics">
            <div class="mt-4 grid grid-cols-2 gap-4">
                <x-ui.input name="name" label="Name (Indonesian, default)" :value="old('name', $consultant->name)" required class="col-span-2" />
                <x-ui.textarea name="description" label="Description (Indonesian, default)" :value="old('description', $consultant->description)" class="col-span-2" />
                <x-ui.input name="translations[en][name]" label="Name (English)" :value="old('translations.en.name', $consultant->translations['en']['name'] ?? '')" />
                <x-ui.input name="translations[ja][name]" label="Name (Japanese)" :value="old('translations.ja.name', $consultant->translations['ja']['name'] ?? '')" />
                <x-ui.textarea name="translations[en][description]" label="Description (English)" :value="old('translations.en.description', $consultant->translations['en']['description'] ?? '')" />
                <x-ui.textarea name="translations[ja][description]" label="Description (Japanese)" :value="old('translations.ja.description', $consultant->translations['ja']['description'] ?? '')" />
            </div>
        </x-ui.card>

        <x-ui.card heading="Capacity & fees">
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <x-ui.select name="specialty" label="Specialty">
                    @foreach (\App\Models\Consultant::specialties() as $type)
                        <option value="{{ $type }}" @selected(old('specialty', $consultant->specialty) === $type)>{{ \Illuminate\Support\Str::headline($type) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="min_participants" type="number" label="Min participants" min="1" :value="old('min_participants', $consultant->min_participants)" required />
                <x-ui.input name="max_participants" type="number" label="Max participants" min="1" max="6" :value="old('max_participants', $consultant->max_participants)" required />
                <x-ui.input name="sort_order" type="number" label="Sort order" :value="old('sort_order', $consultant->sort_order)" />
                <x-ui.input name="consultation_fee" type="number" label="Consultation fee / 60 minutes" step="1000" min="0" :value="old('consultation_fee', $consultant->consultation_fee ?? 0)" required />
                <x-ui.input name="project_budget_from" type="number" label="Indicative project budget" step="1000" min="0" :value="old('project_budget_from', $consultant->project_budget_from)" />
            </div>
            <x-ui.input name="features" label="Features (comma-separated)" :value="old('features', implode(', ', $consultant->features ?? []))" placeholder="video_call, discovery, written_summary" class="mt-4" />
            <x-ui.checkbox name="is_active" label="Published (visible on the website)" :checked="old('is_active', $consultant->is_active)" class="mt-4" />
        </x-ui.card>

        <x-ui.card heading="Photos">
            @if ($isEdit && $consultant->images->isNotEmpty())
                <div class="mt-4 space-y-2">
                    @foreach ($consultant->images as $image)
                        <div class="flex items-center gap-3 rounded-control border border-border p-2">
                            <img src="{{ $image->image_source }}" alt="{{ $image->alt_text }}" class="h-14 w-20 rounded-sm object-cover">
                            <div class="flex-1 text-xs text-muted">
                                <p class="truncate">{{ $image->image_url }}</p>
                                <p>Tags: {{ implode(', ', $image->tags ?? []) }}</p>
                            </div>
                            <label class="flex items-center gap-1 text-xs text-danger">
                                <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" class="rounded-sm border-border-strong">
                                Delete
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif

            <p class="mt-4 text-xs text-muted">Add photos by path under public/ or by URL. Tag them so the AI SaaS Consultant can pick the right one — e.g. "discovery, video_call" or "architecture, cloud".</p>
            @for ($i = 0; $i < 3; $i++)
                <div class="mt-2 grid grid-cols-6 gap-2">
                    <input type="text" name="new_images[{{ $i }}][url]" aria-label="Photo {{ $i + 1 }} path or URL" placeholder="images/consultants/… or https://…" class="col-span-3 rounded-control border border-border-strong bg-surface px-3 py-2 text-sm text-text placeholder:text-subtle">
                    <input type="text" name="new_images[{{ $i }}][tags]" aria-label="Photo {{ $i + 1 }} tags" placeholder="discovery, video_call" class="col-span-2 rounded-control border border-border-strong bg-surface px-3 py-2 text-sm text-text placeholder:text-subtle">
                    <input type="text" name="new_images[{{ $i }}][alt]" aria-label="Photo {{ $i + 1 }} alt text" placeholder="Alt text" class="rounded-control border border-border-strong bg-surface px-3 py-2 text-sm text-text placeholder:text-subtle">
                </div>
            @endfor
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.link-button :href="route('admin.consultants.index')" variant="secondary">Cancel</x-ui.link-button>
            <x-ui.button type="submit">Save consultant</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
