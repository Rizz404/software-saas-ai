<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyKnowledgeItem;
use App\Modules\Knowledge\Enums\KnowledgeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyKnowledgeItem>
 */
class CompanyKnowledgeItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'category' => KnowledgeCategory::General->value,
            'title' => fake()->sentence(3),
            'body' => fake()->paragraph(),
            'tags' => [],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inCategory(KnowledgeCategory $category): static
    {
        return $this->state(fn () => ['category' => $category->value]);
    }
}
