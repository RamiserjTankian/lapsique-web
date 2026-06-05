<?php

namespace App\Filament\Resources\PageContentBlocks;

use App\Filament\Resources\PageContentBlocks\Pages\CreatePageContentBlock;
use App\Filament\Resources\PageContentBlocks\Pages\EditPageContentBlock;
use App\Filament\Resources\PageContentBlocks\Pages\ListPageContentBlocks;
use App\Filament\Resources\PageContentBlocks\Schemas\PageContentBlockForm;
use App\Filament\Resources\PageContentBlocks\Tables\PageContentBlocksTable;
use App\Models\PageContentBlock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PageContentBlockResource extends Resource
{
    protected static ?string $model = PageContentBlock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Bloques editoriales';

    protected static ?string $modelLabel = 'Bloque editorial';

    protected static ?string $pluralModelLabel = 'Bloques editoriales';

    protected static UnitEnum|string|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return PageContentBlockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PageContentBlocksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPageContentBlocks::route('/'),
            'create' => CreatePageContentBlock::route('/create'),
            'edit' => EditPageContentBlock::route('/{record}/edit'),
        ];
    }
}
