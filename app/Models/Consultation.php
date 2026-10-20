<?php

namespace App\Models;

use App\Modules\Consultation\Enums\ConsultationGoal;
use App\Modules\Consultation\Enums\ConsultationStatus;
use App\Modules\Consultation\Support\ConsultationAvailability;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reference',
    'company_id',
    'consultant_id',
    'conversation_id',
    'client_name',
    'client_email',
    'client_phone',
    'contact_type',
    'consultation_date',
    'time_slot',
    'participants',
    'goal',
    'consultation_fee_total',
    'status',
    'notes',
    'product_id',
    'product_snapshot',
    'consultant_name_snapshot',
    'currency_snapshot',
    'duration_minutes',
])]
class Consultation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = ConsultationStatus::Pending->value;

    public const STATUS_CONFIRMED = ConsultationStatus::Confirmed->value;

    public const STATUS_CANCELLED = ConsultationStatus::Cancelled->value;

    /**
     * ConsultationGoals a guest can mention so the team can prepare the consultation.
     */
    public const GOALS = [
        ConsultationGoal::Birthday->value,
        ConsultationGoal::Date->value,
        ConsultationGoal::Family->value,
        ConsultationGoal::Meeting->value,
        ConsultationGoal::Work->value,
    ];

    protected function casts(): array
    {
        return [
            'consultation_date' => 'date',
            'consultation_fee_total' => 'decimal:2',
            'product_snapshot' => 'array',
            'duration_minutes' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * "11:00–13:00" for a consultation that starts at 11:00.
     */
    public function getTimeRangeAttribute(): string
    {
        return ConsultationAvailability::slotRange($this->time_slot);
    }

    /**
     * A short, unambiguous, non-sequential code participants can quote to staff.
     */
    public static function generateReference(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $reference = 'SAAS-'.collect(range(1, 6))
                ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
                ->implode('');
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }
}
