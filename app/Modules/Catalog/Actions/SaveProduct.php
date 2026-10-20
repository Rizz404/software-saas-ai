<?php

namespace App\Modules\Catalog\Actions;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Support\Str;

/**
 * Creates a service or updates one. A new item gets a slug from its name; an
 * existing one keeps its slug so guest links never break.
 */
class SaveProduct
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Company $company, array $attributes, ?Product $product = null): Product
    {
        if ($product) {
            $product->update($attributes);

            return $product;
        }

        return $company->products()->create([...$attributes, 'slug' => $this->uniqueSlug($company, $attributes['name'])]);
    }

    private function uniqueSlug(Company $company, string $name): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $suffix = 1;

        while ($company->products()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
