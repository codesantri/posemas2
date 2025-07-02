<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TransactionMenus extends Widget
{
    protected static string $view = 'filament.widgets.transaction-menus';
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true;
    }

    /**
     * Data menu yang akan dikirim ke Blade view
     */
    public function getMenus(): array
    {
        return [
            [
                'title' => 'Tukar Tambah',
                'route' => 'filament.admin.shop.resources.change-adds.index',
                'icon' => 'icon/add.png',
            ],
            [
                'title' => 'Tukar Kurang',
                'route' => 'filament.admin.shop.resources.change-deducts.index',
                'icon' => 'icon/deduct.png',
            ],
            [
                'title' => 'Tukar Model',
                'route' => 'filament.admin.shop.resources.change-models.index',
                'icon' => 'icon/change.png',
            ],
            [
                'title' => 'Pembelian',
                'route' => 'filament.admin.shop.resources.purchases.index',
                'icon' => 'icon/buy.png',
            ],
            [
                'title' => 'Penjualan',
                'route' => 'filament.admin.shop.pages.products',
                'icon' => 'icon/sale.png',
            ],

            [
                'title' => 'Titip Emas',
                'route' => 'filament.admin.shop.resources.entrusts.index',
                'icon' => 'icon/titip.png',
            ],
        ];
    }
}
