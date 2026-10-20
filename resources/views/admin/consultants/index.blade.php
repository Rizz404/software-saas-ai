<x-layouts.admin title="Consultants">
    <x-slot:actions>
        <x-ui.link-button :href="route('admin.consultants.create')" size="sm">Add consultant</x-ui.link-button>
    </x-slot:actions>

    <x-ui.table :columns="['Name', 'Type', 'Capacity', 'Fee', 'Images', 'Status', '']" :empty="$consultants->isEmpty()" empty-text="No consultants yet.">
        @foreach ($consultants as $consultant)
            <tr>
                <td class="px-4 py-3 font-medium text-text">{{ $consultant->name }}</td>
                <td class="px-4 py-3 text-muted">{{ \Illuminate\Support\Str::headline($consultant->specialty) }}</td>
                <td class="px-4 py-3">{{ $consultant->min_participants }}–{{ $consultant->max_participants }} participants</td>
                <td class="px-4 py-3">{{ (float) $consultant->consultation_fee > 0 ? $company->currency.' '.number_format((float) $consultant->consultation_fee, 0, ',', '.') : 'Free' }}</td>
                <td class="px-4 py-3">{{ $consultant->images_count }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="$consultant->is_active ? 'success' : 'neutral'">{{ $consultant->is_active ? 'Active' : 'Hidden' }}</x-ui.badge>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.consultants.edit', $consultant) }}" class="text-muted hover:underline">Edit</a>
                    <x-ui.confirm-form :action="route('admin.consultants.destroy', $consultant)" message="Delete this consultant?" confirm-label="Delete" trigger-class="ml-3 text-danger hover:underline">Delete</x-ui.confirm-form>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
