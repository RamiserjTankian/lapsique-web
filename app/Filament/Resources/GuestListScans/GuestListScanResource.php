<?php

namespace App\Filament\Resources\GuestListScans;

use App\Filament\Resources\GuestListScans\Pages\ListGuestListScans;
use App\Filament\Resources\GuestListScans\Tables\GuestListScansTable;
use App\Models\GuestListScan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

class GuestListScanResource extends Resource
{
    protected static ?string $model = GuestListScan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Escaneos QR';

    protected static UnitEnum|string|null $navigationGroup = 'Eventos';

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return GuestListScansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuestListScans::route('/'),
        ];
    }
}
