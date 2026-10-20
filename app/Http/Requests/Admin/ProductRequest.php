<?php

namespace App\Http\Requests\Admin;

use App\Modules\Catalog\Enums\ProductCategory;
use App\Support\Text;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Create or update a service. The route model binding and the policy decide
 * whose service it is; this decides what may be written.
 */
class ProductRequest extends FormRequest
{
    /**
     * A record of another company is "not found" before anything is validated.
     */
    public function authorize(): bool
    {
        $record = $this->route('product');

        if ($record) {
            Gate::authorize('update', $record);
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', 'string', Rule::in(ProductCategory::values())],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'translations.en.name' => ['nullable', 'string'],
            'translations.en.description' => ['nullable', 'string'],
            'translations.ja.name' => ['nullable', 'string'],
            'translations.ja.description' => ['nullable', 'string'],
            'subscription_plans' => ['required', 'array', 'size:3'],
            'subscription_plans.*' => ['required', 'array:key,name,monthly_price,seats,features'],
            'subscription_plans.*.key' => ['required', 'string', 'distinct', Rule::in(['starter', 'growth', 'scale'])],
            'subscription_plans.*.name' => ['required', 'string', 'max:60'],
            'subscription_plans.*.monthly_price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'subscription_plans.*.seats' => ['required', 'integer', 'min:1', 'max:100000'],
            'subscription_plans.*.features' => ['nullable', 'string', 'max:2000'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'tags' => ['nullable', 'string'],
            'prerequisites' => ['nullable', 'string'],
            'delivery_model' => ['nullable', Rule::in(['remote', 'hybrid', 'onsite'])],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_unavailable' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * The columns to write, without the slug (which only a new item gets).
     *
     * @return array<string, mixed>
     */
    public function productAttributes(): array
    {
        $data = $this->validated();

        return [
            'category' => $data['category'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'translations' => [
                'en' => ['name' => $data['translations']['en']['name'] ?? null, 'description' => $data['translations']['en']['description'] ?? null],
                'ja' => ['name' => $data['translations']['ja']['name'] ?? null, 'description' => $data['translations']['ja']['description'] ?? null],
            ],
            'price' => min(array_column($data['subscription_plans'], 'monthly_price')),
            'subscription_plans' => array_map(fn (array $plan): array => [
                'key' => $plan['key'],
                'name' => $plan['name'],
                'monthly_price' => (float) $plan['monthly_price'],
                'seats' => (int) $plan['seats'],
                'features' => array_values(array_filter(array_map('trim', preg_split('/\R/u', $plan['features'] ?? '')))),
            ], $data['subscription_plans']),
            'image_url' => $data['image_url'] ?? null,
            'tags' => Text::slugList($data['tags'] ?? null),
            'prerequisites' => Text::slugList($data['prerequisites'] ?? null),
            'delivery_model' => filled($data['delivery_model'] ?? null) ? Str::of($data['delivery_model'])->trim()->slug('_')->toString() : null,
            'trial_days' => $data['trial_days'] ?? null,
            'is_featured' => $this->boolean('is_featured'),
            'is_unavailable' => $this->boolean('is_unavailable'),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ];
    }
}
