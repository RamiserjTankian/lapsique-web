<?php

namespace App\Filament\Resources\SessionCustomers;

use App\Filament\Resources\SessionCustomers\Pages\ListSessionCustomers;
use App\Filament\Resources\SessionCustomers\Pages\ViewSessionCustomer;
use App\Filament\Resources\SessionCustomers\RelationManagers\ContentBookingsRelationManager;
use App\Filament\Resources\SessionCustomers\Schemas\SessionCustomerForm;
use App\Filament\Resources\SessionCustomers\Schemas\SessionCustomerInfolist;
use App\Filament\Resources\SessionCustomers\Tables\SessionCustomersTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SessionCustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Clientes ERP';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes de sesiones';

    protected static UnitEnum|string|null $navigationGroup = 'Booking';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return SessionCustomerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SessionCustomerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SessionCustomersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ContentBookingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSessionCustomers::route('/'),
            'view' => ViewSessionCustomer::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->whereHas('contentBookings');
    }
}
