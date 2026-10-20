<?php

namespace App\Modules\Operations\Presenters;

/**
 * The admin products: where each entry leads and what it is called.
 */
final class AdminNavigation
{
    /**
     * @return list<array{route: string, label: string}>
     */
    public static function items(): array
    {
        return [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard'],
            ['route' => 'admin.products.index', 'label' => 'SaaS products'],
            ['route' => 'admin.consultants.index', 'label' => 'Consultants'],
            ['route' => 'admin.knowledge-items.index', 'label' => 'Knowledge base'],
            ['route' => 'admin.consultations.index', 'label' => 'Demo requests'],
            ['route' => 'admin.handovers.index', 'label' => 'Handovers'],
        ];
    }
}
