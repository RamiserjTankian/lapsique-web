<?php

namespace App\Filament\Resources\EmailTrackings;

use App\Filament\Resources\EmailTrackings\Pages\CreateEmailTracking;
use App\Filament\Resources\EmailTrackings\Pages\EditEmailTracking;
use App\Filament\Resources\EmailTrackings\Pages\ListEmailTrackings;
use App\Filament\Resources\EmailTrackings\Schemas\EmailTrackingForm;
use App\Filament\Resources\EmailTrackings\Tables\EmailTrackingsTable;
use App\Models\EmailTracking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmailTrackingResource extends Resource
{
    protected static ?string $model = EmailTracking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Email Trackings';

    protected static UnitEnum|string|null $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return EmailTrackingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailTrackingsTable::configure($table);
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
            'index' => ListEmailTrackings::route('/'),
            'create' => CreateEmailTracking::route('/create'),
            'edit' => EditEmailTracking::route('/{record}/edit'),
        ];
    }
}
