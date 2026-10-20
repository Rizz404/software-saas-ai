<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;

final class GetProductTool implements Tool
{
    public function name(): string
    {
        return 'get_product';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Read one SaaS product by its slug from search_products. Return its scope, monthly demo prices and subscription plans, prerequisites, delivery model, and availability. Use for questions about a specific product.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_slug' => ['type' => 'string'],
                    ],
                    'required' => ['product_slug'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $item = CatalogSummaries::productBySlug($context->company, (string) ($arguments['product_slug'] ?? ''));

        if (! $item) {
            return new ToolResult('Product not found.');
        }

        $detail = [
            ...CatalogSummaries::product($item, $context->company, $context->locale),
            'description' => $item->translatedDescription($context->locale),
            'trial_days' => $item->trial_days,
            'note' => 'Prices are demo monthly subscriptions per workspace. Seat limits are per plan. Activation, trial, taxes, and contracts require team agreement. No billing occurs here.',
        ];

        return ToolResult::json($detail, ['type' => 'products_detail', 'item' => $detail]);
    }
}
