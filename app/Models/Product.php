<?php

namespace App\Models;

use App\Modules\Catalog\Enums\ProductCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One IT service offered by the company. The price here is the only price the
 * AI SaaS Consultant may ever quote, and only after reading it through a tool call.
 */
#[Fillable([
    'company_id',
    'category',
    'name',
    'slug',
    'description',
    'translations',
    'price',
    'subscription_plans',
    'image_url',
    'tags',
    'prerequisites',
    'delivery_model',
    'trial_days',
    'is_featured',
    'is_unavailable',
    'is_active',
    'sort_order',
])]
class Product extends Model
{
    use HasFactory, SoftDeletes;

    public const CATEGORY_WEB = ProductCategory::Web->value;

    public const CATEGORY_MOBILE = ProductCategory::Mobile->value;

    public const CATEGORY_AI_AUTOMATION = ProductCategory::AiAutomation->value;

    public const CATEGORY_CLOUD = ProductCategory::Cloud->value;

    public const CATEGORY_SECURITY = ProductCategory::Security->value;

    public const CATEGORY_SUPPORT = ProductCategory::Support->value;

    /**
     * Solution traits a visitor can filter on.
     */
    public const SOLUTION_TAGS = ['scalable', 'cloud_native', 'automation', 'secure', 'managed'];

    /**
     * @return list<string>
     */
    public static function categories(): array
    {
        return ProductCategory::values();
    }

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'tags' => 'array',
            'prerequisites' => 'array',
            'price' => 'decimal:2',
            'subscription_plans' => 'array',
            'is_featured' => 'boolean',
            'is_unavailable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The picture to show: a full URL as stored, or a file under public/.
     */
    public function getImageSourceAttribute(): ?string
    {
        if (blank($this->image_url)) {
            return null;
        }

        return str_starts_with($this->image_url, 'http') ? $this->image_url : asset($this->image_url);
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? [], true);
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
