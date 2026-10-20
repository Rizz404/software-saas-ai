<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The only source of truth for consultation availability. Stands in for a real
 * consultation scheduling adapter for V1: the AI must reach this table (or
 * its future adapter) through a tool call, never answer from its own memory.
 */
#[Fillable([
    'consultant_id',
    'consultation_date',
    'time_slot',
    'total_sessions',
    'reserved_sessions',
])]
class ConsultationSlot extends Model
{
    use HasFactory;

    protected $table = 'consultation_slots';

    protected function casts(): array
    {
        return [
            'consultation_date' => 'date',
            'total_sessions' => 'integer',
            'reserved_sessions' => 'integer',
        ];
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }

    public function availableSessions(): int
    {
        return max(0, min(1, $this->total_sessions) - $this->reserved_sessions);
    }

    public function isAvailable(): bool
    {
        return $this->availableSessions() > 0;
    }
}
