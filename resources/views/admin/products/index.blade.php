<x-layouts.admin title="Catalog">
    <x-slot:actions>
        <x-ui.link-button :href="route('admin.products.create')" size="sm">Add product</x-ui.link-button>
    </x-slot:actions>

    <x-ui.table :columns="['Name', 'Category', 'Price', 'Status', '']" :empty="$products->isEmpty()" empty-text="No products yet.">
        @foreach ($products as $product)
            <tr>
                <td class="px-4 py-3 font-medium text-text">{{ $product->name }}@if ($product->is_featured)<x-ui.badge tone="warning" class="ml-2">Featured</x-ui.badge>@endif</td>
                <td class="px-4 py-3 text-muted">{{ \Illuminate\Support\Str::headline($product->category) }}</td>
                <td class="px-4 py-3">{{ $company->currency }} {{ number_format((float) $product->price, 0, ',', '.') }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="$product->is_active ? 'success' : 'neutral'">{{ $product->is_active ? 'Active' : 'Hidden' }}</x-ui.badge>
                    @if ($product->is_unavailable)
                        <x-ui.badge tone="danger" class="ml-1">Unavailable</x-ui.badge>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.products.edit', $product) }}" class="text-muted hover:underline">Edit</a>
                    <x-ui.confirm-form :action="route('admin.products.destroy', $product)" message="Delete this product?" confirm-label="Delete" trigger-class="ml-3 text-danger hover:underline">Delete</x-ui.confirm-form>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
