<?php

namespace App\Models;

use App\Modules\Consultants\Enums\ConsultantSpecialty;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'name',
    'slug',
    'description',
    'translations',
    'specialty',
    'min_participants',
    'max_participants',
    'features',
    'consultation_fee',
    'project_budget_from',
    'is_active',
    'sort_order',
])]
class Consultant extends Model
{
    use HasFactory, SoftDeletes;

    public const SPECIALTY_SOLUTION = ConsultantSpecialty::Solution->value;

    public const SPECIALTY_CLOUD = ConsultantSpecialty::Cloud->value;

    public const SPECIALTY_AI = ConsultantSpecialty::Ai->value;

    public const SPECIALTY_SECURITY = ConsultantSpecialty::Security->value;

    /**
     * @return list<string>
     */
    public static function specialties(): array
    {
        return ConsultantSpecialty::values();
    }

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'features' => 'array',
            'consultation_fee' => 'decimal:2',
            'project_budget_from' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ConsultantImage::class)->orderBy('sort_order');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(ConsultationSlot::class);
    }

    public function translatedName(string $locale): string
    {
        $value = $this->translations[$locale]['name'] ?? null;

        return filled($value) ? $value : $this->name;
    }

    public function translatedDescription(string $locale): ?string
    {
        $value = $this->translations[$locale]['description'] ?? null;

        return filled($value) ? $value : $this->description;
    }
}
