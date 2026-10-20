@php($isEdit = $product->exists)

<x-layouts.admin :title="$isEdit ? 'Edit product' : 'Add product'">
    <form method="POST" action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}" class="space-y-8">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <x-ui.card heading="Basics">
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.select name="category" label="Category">
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(old('category', $product->category) === $category)>{{ \Illuminate\Support\Str::headline($category) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="name" label="Name (Indonesian, default)" :value="old('name', $product->name)" required class="sm:col-span-2" />
                <x-ui.textarea name="description" label="Description (Indonesian, default)" :value="old('description', $product->description)" class="sm:col-span-2" />
                <x-ui.input name="translations[en][name]" label="Name (English)" :value="old('translations.en.name', $product->translations['en']['name'] ?? '')" />
                <x-ui.input name="translations[ja][name]" label="Name (Japanese)" :value="old('translations.ja.name', $product->translations['ja']['name'] ?? '')" />
                <x-ui.textarea name="translations[en][description]" label="Description (English)" :value="old('translations.en.description', $product->translations['en']['description'] ?? '')" />
                <x-ui.textarea name="translations[ja][description]" label="Description (Japanese)" :value="old('translations.ja.description', $product->translations['ja']['description'] ?? '')" />
            </div>
        </x-ui.card>

        <x-ui.card heading="Monthly subscription plans">
            <p class="mt-3 text-sm text-muted">Prices are per workspace per month in {{ $company->currency }}. The lowest plan price appears in the catalog.</p>
            <div class="mt-4 grid gap-4 lg:grid-cols-3">
                @foreach (['starter' => 'Starter', 'growth' => 'Growth', 'scale' => 'Scale'] as $key => $name)
                    @php
                        $index = $loop->index;
                        $plan = collect($product->subscription_plans ?? [])->firstWhere('key', $key) ?? [];
                    @endphp
                    <fieldset class="space-y-4 rounded-xl border border-border p-4">
                        <legend class="px-2 text-sm font-semibold">{{ $name }}</legend>
                        <input type="hidden" name="subscription_plans[{{ $index }}][key]" value="{{ $key }}">
                        <x-ui.input name="subscription_plans[{{ $index }}][name]" label="Plan name" :value="old('subscription_plans.'.$index.'.name', $plan['name'] ?? $name)" required />
                        <x-ui.input name="subscription_plans[{{ $index }}][monthly_price]" type="number" label="Monthly price" min="0" step="1" :value="old('subscription_plans.'.$index.'.monthly_price', $plan['monthly_price'] ?? 0)" required />
                        <x-ui.input name="subscription_plans[{{ $index }}][seats]" type="number" label="Included users" min="1" :value="old('subscription_plans.'.$index.'.seats', $plan['seats'] ?? 5)" required />
                        <x-ui.textarea name="subscription_plans[{{ $index }}][features]" label="Features (one per line)" :value="old('subscription_plans.'.$index.'.features', implode(PHP_EOL, $plan['features'] ?? []))" />
                    </fieldset>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card heading="Details">
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 sm:grid-cols-4">
                <x-ui.input name="delivery_model" label="Delivery model" :value="old('delivery_model', $product->delivery_model)" placeholder="remote, hybrid, onsite" />
                <x-ui.input name="trial_days" type="number" label="Sample trial days" min="0" :value="old('trial_days', $product->trial_days)" />
                <x-ui.input name="sort_order" type="number" label="Sort order" :value="old('sort_order', $product->sort_order)" />
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.input name="tags" label="Tags (comma-separated)" :value="old('tags', implode(', ', $product->tags ?? []))" placeholder="scalable, secure, automation" />
                <x-ui.input name="prerequisites" label="Prerequisites (comma-separated)" :value="old('prerequisites', implode(', ', $product->prerequisites ?? []))" placeholder="business_brief, api_access" />
                <x-ui.input name="image_url" label="Image path or URL" :value="old('image_url', $product->image_url)" placeholder="images/scenes/lab.png or https://…" class="sm:col-span-2" />
            </div>
            <div class="mt-4 flex flex-wrap gap-6">
                <x-ui.checkbox name="is_featured" label="Featured" :checked="old('is_featured', $product->is_featured)" />
                <x-ui.checkbox name="is_unavailable" label="Temporarily unavailable" :checked="old('is_unavailable', $product->is_unavailable)" />
                <x-ui.checkbox name="is_active" label="Published (visible on the website)" :checked="old('is_active', $product->is_active)" />
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.link-button :href="route('admin.products.index')" variant="secondary">Cancel</x-ui.link-button>
            <x-ui.button type="submit">Save product</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
