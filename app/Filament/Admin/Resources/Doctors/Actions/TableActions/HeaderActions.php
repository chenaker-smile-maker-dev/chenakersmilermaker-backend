<?php

namespace App\Filament\Admin\Resources\Doctors\Actions\TableActions;

use App\Actions\Doctor\AddAvailabilityRule;
use App\Actions\Doctor\AddBlockTime;
use App\Filament\Admin\Resources\Doctors\Schemas\CreateAvailabilityRuleSchema;
use App\Filament\Admin\Resources\Doctors\Schemas\CreateBlockTimeSchema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class HeaderActions
{
    public static function addAvailabilityRuleAction(): Action
    {
        return Action::make('add-availability-rule')
            ->label('Add Availability Rule')
            ->icon('heroicon-o-plus-circle')
            ->form(CreateAvailabilityRuleSchema::get())
            ->action(function (array $data, $livewire) {
                try {
                    $doctor = $livewire->record;

                    if (!$doctor) {
                        throw new \Exception('Doctor not found');
                    }

                    (new AddAvailabilityRule())($doctor, $data['days_of_week'], $data['start_hour'], $data['end_hour'], $data['effective_from'], $data['effective_to'] ?? null, $data);

                    Notification::make()
                        ->success()
                        ->title('Availability Rule Added')
                        ->body('The availability rule has been successfully created.')
                        ->send();

                    $livewire->dispatch('refresh');
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title('Error')
                        ->body('Failed to add availability rule: ' . $e->getMessage())
                        ->send();
                }
            });
    }

    public static function addBlockTimeAction(): Action
    {
        return Action::make('add-block-time')
            ->label('Add Block Time')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->form(CreateBlockTimeSchema::get())
            ->action(function (array $data, $livewire) {
                try {
                    $doctor = $livewire->record;

                    if (!$doctor) {
                        throw new \Exception('Doctor not found');
                    }

                    (new AddBlockTime())($doctor, $data['reason'], $data['from_date'], $data['to_date'], $data['description'] ?? null, $data['block_specific_hours'] ? $data['block_start_time'] : null, $data['block_specific_hours'] ? $data['block_end_time'] : null, $data);

                    Notification::make()
                        ->success()
                        ->title('Block Time Added')
                        ->body('The block time has been successfully created.')
                        ->send();

                    $livewire->dispatch('refresh');
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title('Error')
                        ->body('Failed to add block time: ' . $e->getMessage())
                        ->send();
                }
            });
    }
}
