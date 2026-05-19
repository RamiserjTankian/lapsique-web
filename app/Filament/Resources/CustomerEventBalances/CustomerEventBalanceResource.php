<?php

namespace App\Filament\Resources\CustomerEventBalances;

use App\Filament\Resources\CustomerEventBalances\Pages\ListCustomerEventBalances;
use App\Filament\Resources\CustomerEventBalances\Pages\ViewCustomerEventBalance;
use App\Filament\Resources\CustomerEventBalances\RelationManagers\PosChargesRelationManager;
use App\Filament\Resources\CustomerEventBalances\Schemas\CustomerEventBalanceInfolist;
use App\Filament\Resources\CustomerEventBalances\Tables\CustomerEventBalancesTable;
use App\Models\CustomerEventBalance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class CustomerEventBalanceResource extends Resource
{
    protected static ?string $model = CustomerEventBalance::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Saldos';

    protected static ?string $modelLabel = 'Saldo de cliente';

    protected static ?string $pluralModelLabel = 'Saldos de clientes';

    protected static UnitEnum | string | null $navigationGroup = 'POS';

    protected static ?int $navigationSort = 2;

    public static function infolist(Schema $schema): Schema
    {
        return CustomerEventBalanceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerEventBalancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PosChargesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerEventBalances::route('/'),
            'view' => ViewCustomerEventBalance::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'customer',
                'event',
                'lastTicketOrder',
            ]);
    }
}
