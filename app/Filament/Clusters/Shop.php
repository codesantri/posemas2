<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Shop extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Transaksi';
    protected static ?string $clusterBreadcrumb = 'Transaksi';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
