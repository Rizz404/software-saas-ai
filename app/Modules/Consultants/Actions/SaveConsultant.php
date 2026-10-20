<?php

namespace App\Modules\Consultants\Actions;

use App\Models\Company;
use App\Models\Consultant;
use Illuminate\Support\Str;

/**
 * Creates a consultant or updates one, together with the photos added or
 * removed in the same form. A new area gets a slug from its name; an existing
 * one keeps its slug so guest links never break.
 */
class SaveConsultant
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<int>  $imageIdsToDelete
     * @param  list<array{url: string, alt?: ?string, tags?: ?string}>  $newImages
     */
    public function handle(Company $company, array $attributes, array $imageIdsToDelete, array $newImages, ?Consultant $consultant = null): Consultant
    {
        if ($consultant) {
            $consultant->update($attributes);
        } else {
            $consultant = $company->consultants()->create([...$attributes, 'slug' => $this->uniqueSlug($company, $attributes['name'])]);
        }

        $this->syncImages($consultant, $imageIdsToDelete, $newImages);

        return $consultant;
    }

    private function uniqueSlug(Company $company, string $name): string
    {
        $base = Str::slug($name) ?: 'area';
        $slug = $base;
        $suffix = 1;

        while ($company->consultants()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  list<int>  $deleteIds
     * @param  list<array{url: string, alt?: ?string, tags?: ?string}>  $newImages
     */
    private function syncImages(Consultant $consultant, array $deleteIds, array $newImages): void
    {
        if ($deleteIds !== []) {
            $consultant->images()->whereIn('id', $deleteIds)->delete();
        }

        $sort = $consultant->images()->max('sort_order') + 1;

        foreach ($newImages as $row) {
            $consultant->images()->create([
                'image_url' => $row['url'],
                'alt_text' => $row['alt'] ?? $consultant->name,
                'tags' => collect(explode(',', $row['tags'] ?? ''))->map(fn ($t) => trim($t))->filter()->values()->all(),
                'sort_order' => $sort++,
            ]);
        }
    }
}
