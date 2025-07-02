<?php

namespace App\Traits\Filament\Action;

use Filament\Actions\Action;

trait SubmitAction
{
    public static function create(): Action
    {
        return Action::make('create')
            ->label('Simpan dan Proses')
            ->submit(null)
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi?')
            ->modalSubheading('Untuk menghindari kesalahan, mohon cek ulang data Anda.')
            ->modalButton('Ya, Lanjutkan')
            ->action(function (array $arguments, Action $action) {
                $livewire = $action->getLivewire();
                $livewire->create();
            });
    }

    public static function update(): Action
    {
        return Action::make('update')
            ->label('Simpan Perubahan')
            ->submit(null)
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi?')
            ->modalSubheading('Pastikan perubahan data sudah benar sebelum melanjutkan.')
            ->modalButton('Ya, Lanjutkan')
            ->action(function (array $arguments, Action $action) {
                $livewire = $action->getLivewire();
                $livewire->save();
            });
    }
}
