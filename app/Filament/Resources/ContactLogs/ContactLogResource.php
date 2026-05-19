<?php

namespace App\Filament\Resources\ContactLogs;

use App\Filament\Resources\ContactLogs\Pages\CreateContactLog;
use App\Filament\Resources\ContactLogs\Pages\EditContactLog;
use App\Filament\Resources\ContactLogs\Pages\ListContactLogs;
use App\Filament\Resources\ContactLogs\Pages\ViewContactLog;
use App\Filament\Resources\ContactLogs\Schemas\ContactLogForm;
use App\Filament\Resources\ContactLogs\Tables\ContactLogsTable;
use App\Models\ContactLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ContactLogResource extends Resource
{
    protected static ?string $model = ContactLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;
    
    protected static ?string $navigationLabel = 'Contact Logs';
    
    protected static UnitEnum|string|null $navigationGroup = 'Marketing';
    
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return ContactLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactLogsTable::configure($table);
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
            'index' => ListContactLogs::route('/'),
            'create' => CreateContactLog::route('/create'),
            'view' => ViewContactLog::route('/{record}'),
            'edit' => EditContactLog::route('/{record}/edit'),
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
}
