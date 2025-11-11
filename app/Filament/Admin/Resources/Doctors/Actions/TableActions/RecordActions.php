<?php

namespace App\Filament\Admin\Resources\Doctors\Actions\TableActions;

use App\Actions\Doctor\DeleteSchedule;
use App\Actions\Doctor\UpdateSchedule;
use App\Filament\Admin\Resources\Doctors\Schemas\EditAvailabilityRuleSchema;
use App\Filament\Admin\Resources\Doctors\Schemas\EditBlockTimeSchema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class RecordActions
{
    public static function editAction(): Action
    {
        return Action::make('edit')
            ->label('Edit')
            ->icon('heroicon-o-pencil')
            ->form(function ($record) {
                $isBlockTime = $record->schedule_type->value === 'blocked';

                if ($isBlockTime) {
                    return EditBlockTimeSchema::get($record);
                } else {
                    return EditAvailabilityRuleSchema::get($record);
                }
            })
            ->action(function ($record, array $data) {
                try {
                    (new UpdateSchedule())($record, $data);

                    Notification::make()
                        ->success()
                        ->title('Schedule Updated')
                        ->body('The schedule has been successfully updated.')
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title('Error')
                        ->body('Failed to update schedule: ' . $e->getMessage())
                        ->send();
                }
            });
    }

    public static function deleteAction(): Action
    {
        return Action::make('delete')
            ->label('Delete')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function ($record) {
                try {
                    (new DeleteSchedule())($record);

                    Notification::make()
                        ->success()
                        ->title('Schedule Deleted')
                        ->body('The schedule has been successfully deleted.')
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title('Error')
                        ->body('Failed to delete schedule: ' . $e->getMessage())
                        ->send();
                }
            });
    }
}
