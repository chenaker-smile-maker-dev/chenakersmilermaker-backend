<?php

namespace App\Filament\Admin\Resources\Doctors;

use App\Filament\Admin\AdminNavigation;
use App\Filament\Admin\Resources\Doctors\Pages\CreateDoctor;
use App\Filament\Admin\Resources\Doctors\Pages\EditDoctor;
use App\Filament\Admin\Resources\Doctors\Pages\EditDoctorContact;
use App\Filament\Admin\Resources\Doctors\Pages\EditDoctorPhoto;
use App\Filament\Admin\Resources\Doctors\Pages\ListDoctors;
use App\Filament\Admin\Resources\Doctors\Pages\ManageDoctorAppointments;
use App\Filament\Admin\Resources\Doctors\Pages\ViewDoctor;
use App\Filament\Admin\Resources\Doctors\Pages\ManageDoctorSchedules;
use App\Filament\Admin\Resources\Doctors\RelationManagers\AppointmentsRelationManager;
use App\Filament\Admin\Resources\Doctors\RelationManagers\SchedulesRelationManager;
use App\Filament\Admin\Resources\Doctors\Schemas\DoctorForm;
use App\Filament\Admin\Resources\Doctors\Schemas\DoctorInfolist;
use App\Filament\Admin\Resources\Doctors\Tables\DoctorsTable;
use App\Models\Doctor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    public static function getNavigationGroup(): ?string
    {
        return  __(AdminNavigation::DOCTORS_RESOURCE['group']);
    }

    public static function getModelLabel(): string
    {
        return 'Médecin';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Médecins';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static string|BackedEnum|null $navigationIcon = AdminNavigation::DOCTORS_RESOURCE['icon'];
    protected static ?int $navigationSort = AdminNavigation::DOCTORS_RESOURCE['sort'];
    protected static ?string $recordTitleAttribute = 'display_name';
    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return $record->display_name;
    }
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'specialty'];
    }
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Specialty' => $record->specialty,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return DoctorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DoctorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DoctorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // AppointmentsRelationManager::class,
            // SchedulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDoctors::route('/'),
            'create' => CreateDoctor::route('/create'),
            'view' => ViewDoctor::route('/{record}'),
            'edit' => EditDoctor::route('/{record}/edit'),
            'edit-contact' => EditDoctorContact::route('/{record}/edit-contact'),
            'edit-photo' => EditDoctorPhoto::route('/{record}/edit-photo'),
            'manage-schedules' => ManageDoctorSchedules::route('/{record}/schedules'),
            'manage-appointments' => ManageDoctorAppointments::route('/{record}/appointments'),
        ];
    }

    public static function sidebar(Model $record): FilamentPageSidebar
    {
        $TITLE = $record->display_name;
        $DESCRIPTION = $record->specialty ?? 'Doctor Details';
        $DOCTOR_INFOS_GROUP = 'Edit Doctor Informations';
        $DOCTOR_SCHEDULES_GROUP = 'Manage Doctor Schedules';

        return FilamentPageSidebar::make()
            ->setTitle($TITLE)
            ->setDescription($DESCRIPTION)
            // ->topbarNavigation()
            ->sidebarNavigation()
            ->setNavigationItems([
                PageNavigationItem::make('View Doctor')
                    ->icon('heroicon-o-eye')
                    // ->group($DOCTOR_INFOS_GROUP)
                    ->isActiveWhen(fn() => request()->routeIs(ViewDoctor::getRouteName()))
                    ->url(fn() => ViewDoctor::getUrl(['record' => $record->id])),

                PageNavigationItem::make('Edit Doctor')
                    ->icon('heroicon-o-user')
                    ->group($DOCTOR_INFOS_GROUP)
                    ->isActiveWhen(fn() => request()->routeIs(EditDoctor::getRouteName()))
                    ->url(fn() => EditDoctor::getUrl(['record' => $record->id])),

                PageNavigationItem::make('Edit Doctor Contact')
                    ->icon('heroicon-o-phone')
                    ->group($DOCTOR_INFOS_GROUP)
                    ->isActiveWhen(fn() => request()->routeIs(EditDoctorContact::getRouteName()))
                    ->url(fn() => EditDoctorContact::getUrl(['record' => $record->id])),

                PageNavigationItem::make('Edit Doctor Photo')
                    ->icon('heroicon-o-camera')
                    ->group($DOCTOR_INFOS_GROUP)
                    ->isActiveWhen(fn() => request()->routeIs(EditDoctorPhoto::getRouteName()))
                    ->url(fn() => EditDoctorPhoto::getUrl(['record' => $record->id])),

                PageNavigationItem::make('Manage Doctor Schedules')
                    ->icon('heroicon-o-clock')
                    ->group($DOCTOR_SCHEDULES_GROUP)
                    ->isActiveWhen(fn() => request()->routeIs(ManageDoctorSchedules::getRouteName()))
                    ->url(fn() => ManageDoctorSchedules::getUrl(['record' => $record->id])),

                PageNavigationItem::make('Manage Doctor Appointments')
                    ->icon('heroicon-o-calendar-days')
                    ->group($DOCTOR_SCHEDULES_GROUP)
                    ->isActiveWhen(fn() => request()->routeIs(ManageDoctorAppointments::getRouteName()))
                    ->url(fn() => ManageDoctorAppointments::getUrl(['record' => $record->id])),

            ]);
    }


    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
