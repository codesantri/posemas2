<?php

namespace App\Filament\Clusters\Shop\Pages;

use App\Models\Cart;
use App\Models\Sale;
use App\Models\Product;
use Filament\Pages\Page;
use App\Filament\Clusters\Shop;
use Filament\Pages\SubNavigationPosition;
use App\Traits\Filament\Action\HeaderAction;
use App\Traits\Filament\Services\FormService;
use App\Traits\Filament\Services\GeneralService;

class ShopPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.shop.products';

    protected static ?string $cluster = Shop::class;

    protected static ?string $title = 'Produk Penjualan';
    protected static ?string $breadcrumb = '';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'products';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    public $page = 1;
    public string $search = '';
    public ?int $typeId = null;
    public ?int $totalOrder = 0;
    public ?int $totalCheckout = 0;

    public function mount(): void
    {
        $this->countOrder();
    }

    public function getProductsProperty()
    {
        return Product::with(['karat', 'type'])
            ->when(
                $this->search,
                fn($query) =>
                $query->where('name', 'like', '%' . $this->search . '%')
            )
            ->when(
                $this->typeId,
                fn($query) =>
                $query->where('type_id', $this->typeId)
            )
            ->orderBy('name')
            ->paginate(12, ['*'], 'page', $this->page);
    }

    public function updatingPage($value)
    {
        $this->page = $value;
    }

    public function updatingSearch()
    {
        $this->page = 1; // Reset to first page when search changes
    }

    public function updatingTypeId()
    {
        $this->page = 1; // Reset to first page when category changes
    }

    public function addToCart($id)
    {
        FormService::addToCart($id);
        $this->countOrder();
    }

    /**
     * Convert gram weight to mayam.
     *
     * @param float|int|null $gram
     * @return float
     */
    public function getMayam($gram = null)
    {

        return GeneralService::getMayam($gram);
    }

    public function countOrder()
    {
        $this->totalOrder = Cart::count();

        $this->totalCheckout = Sale::whereHas('transaction', function ($q) {
            $q->where('status', 'pending');
        })->count();
    }



    public function gotoCart()
    {
        $this->redirect(route('filament.admin.shop.resources.sales.create'));
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::getAddProductAction(),
            HeaderAction::getMenu(),
        ];
    }
}
