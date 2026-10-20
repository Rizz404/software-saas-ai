<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\Product;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Catalog\Queries\SearchCatalog;

final class SearchCatalogTool implements Tool
{
    public function __construct(private readonly SearchCatalog $search) {}

    public function name(): string
    {
        return 'search_products';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Search the SaaS product catalog by keyword, category, solution tag, or monthly workspace budget. Return monthly demo prices, subscription plans, seats, prerequisites, trial days, and product availability. Never promise an active subscription.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'Keywords such as web, automation, cloud - optional'],
                        'category' => [
                            'type' => 'string',
                            'enum' => Product::categories(),
                            'description' => 'Optional category filter',
                        ],
                        'solution' => [
                            'type' => 'string',
                            'enum' => Product::SOLUTION_TAGS,
                            'description' => 'Optional solution requirement',
                        ],
                        'max_price' => ['type' => 'number', 'minimum' => 0, 'description' => 'Optional monthly budget per workspace, in the company currency'],
                    ],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $items = $this->search->handle(
            $context->company,
            (string) ($arguments['query'] ?? ''),
            $arguments['category'] ?? null,
            $arguments['solution'] ?? null,
            isset($arguments['max_price']) ? (float) $arguments['max_price'] : null,
        );

        if ($items->isEmpty()) {
            return new ToolResult('No product matches that request. Tell the guest honestly and suggest a nearby alternative from another search_products call, or call request_human_handover for custom requests.');
        }

        $results = $items->map(fn (Product $item) => CatalogSummaries::product($item, $context->company, $context->locale))->values()->all();

        return ToolResult::json($results, ['type' => 'products_results', 'items' => $results]);
    }
}
