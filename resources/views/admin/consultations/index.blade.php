<x-layouts.admin title="Demo requests">
    <div class="mb-4 flex gap-2 text-sm">
        @foreach (['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'] as $value => $label)
            <a href="{{ route('admin.consultations.index', $value ? ['status' => $value] : []) }}"
                class="rounded-full px-3 py-1 {{ $status === $value || (! $status && $value === '') ? 'bg-primary text-on-primary' : 'border border-border-strong bg-surface text-muted' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <x-ui.table :columns="['Reference', 'Client', 'Consultant / product plan', 'When', 'Consultation fee', 'Status', '']" :empty="$consultations->isEmpty()" empty-text="No demo requests found.">
        @foreach ($consultations as $consultation)
            <tr>
                <td class="px-4 py-3 font-mono text-xs">{{ $consultation->reference }}</td>
                <td class="px-4 py-3">
                    <p class="font-medium text-text">{{ $consultation->client_name }}</p>
                    <p class="text-xs text-muted">{{ $consultation->contact_type ? ucfirst($consultation->contact_type).': ' : '' }}{{ $consultation->client_phone ?? $consultation->client_email }}</p>
                    @if ($consultation->notes)<p class="mt-1 max-w-xs text-xs italic text-subtle">{{ $consultation->notes }}</p>@endif
                </td>
                <td class="px-4 py-3">{{ $consultation->consultant_name_snapshot }}
                        @if ($consultation->product_snapshot)
                            <p class="mt-1 text-xs text-muted">{{ $consultation->product_snapshot['name'] }} · {{ $consultation->currency_snapshot }} {{ number_format($consultation->product_snapshot['estimated_price'], 0, ',', '.') }} / month (demo)</p>
                        @endif
                        @if ($consultation->product_snapshot['selected_plan'] ?? null)
                            <p class="mt-1 text-xs text-muted">{{ $consultation->product_snapshot['selected_plan']['name'] }} · {{ $consultation->currency_snapshot }} {{ number_format($consultation->product_snapshot['selected_plan']['monthly_price'], 0, ',', '.') }} / month · {{ $consultation->product_snapshot['selected_plan']['seats'] }} users</p>
                        @endif<p class="text-xs text-subtle">{{ $consultation->participants }} participants{{ $consultation->goal ? ' · '.ucfirst($consultation->goal) : '' }}</p></td>
                <td class="px-4 py-3 text-xs text-muted">{{ $consultation->consultation_date->toFormattedDateString() }}<p>{{ $consultation->time_range }}</p></td>
                <td class="px-4 py-3">{{ (float) $consultation->consultation_fee_total > 0 ? $consultation->currency_snapshot.' '.number_format((float) $consultation->consultation_fee_total, 0, ',', '.') : 'Free' }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="match ($consultation->status) { 'pending' => 'warning', 'confirmed' => 'success', default => 'neutral' }">{{ ucfirst($consultation->status) }}</x-ui.badge>
                </td>
                <td class="px-4 py-3 text-right">
                    @if ($consultation->status === 'pending')
                        <form method="POST" action="{{ route('admin.consultations.status', $consultation) }}" class="inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit" class="text-success hover:underline">Confirm</button>
                        </form>
                    @endif
                    @if ($consultation->status !== 'cancelled')
                        <x-ui.confirm-form :action="route('admin.consultations.status', $consultation)" method="PATCH" message="Cancel this demo request?" confirm-label="Cancel demo" cancel-label="Keep" trigger-class="ml-3 text-danger hover:underline">
                            <x-slot:fields>
                                <input type="hidden" name="status" value="cancelled">
                            </x-slot:fields>
                            Cancel
                        </x-ui.confirm-form>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    <div class="mt-4">{{ $consultations->links() }}</div>
</x-layouts.admin>
