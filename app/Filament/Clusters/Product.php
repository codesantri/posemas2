<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Product extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $clusterBreadcrumb = 'Master Produk';

    public static function getNavigationLabel(): string
    {
        return 'Manajemen Produk';
    }
}
