<?php

namespace App\Traits\Filament\Services;

trait GeneralService
{

    /**
     * Contoh method getMayam
     * 
     * @param float|int $gram Jumlah gram yang mau dikonversi ke mayam
     * @return float Jumlah mayam
     */
    public static function getMayam($gram = null)
    {
        // Misalnya: 1 mayam = 3.33 gram (silakan sesuaikan kalau ada aturan khusus)
        $mayamValue = 3.35;

        if ($gram <= 0) {
            return 0;
        }

        return round($gram / $mayamValue, 2);
    }
}
