<?php

namespace App\Filament\Resources\Items\Schemas;

use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Filament\Resources\UnitOfMeasures\Schemas\UnitOfMeasureForm;
use App\Models\UnitOfMeasure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información General')
                    ->columnSpanFull()
                    ->description('Administre el nombre, identificadores y taxonomía básica del artículo.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->unique(ignoreRecord: true)
                            ->default(null),
                        Select::make('supplier_id')
                            ->label('Proveedor')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->createOptionForm(SupplierForm::fields()),
                        Select::make('unit_of_measure_id')
                            ->label('Unidad de Medida')
                            ->relationship('unitOfMeasure', 'name')
                            ->required()
                            ->native(false)
                            ->live()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(UnitOfMeasureForm::fields()),
                        Select::make('categories')
                            ->label('Categorías')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->native(false)
                            ->searchable()
                            ->columnSpanFull()
                            ->preload()
                            ->createOptionForm(CategoryForm::fields()),
                    ])
                    ->columns(2),

                Section::make('Stock y Precio')
                    ->columnSpanFull()
                    ->description('Configure los costos y niveles de stock permitidos.')
                    ->schema([
                        TextInput::make('unit_cost')
                            ->label('Costo Unitario')
                            ->required()
                            ->numeric()
                            ->default(0.0)
                            ->prefix('S/'),
                        TextInput::make('current_stock')
                            ->label('Existencia Actual')
                            ->required()
                            ->numeric()
                            ->default(0.0)
                            ->prefix(fn (Get $get) => UnitOfMeasure::find($get('unit_of_measure_id'))?->symbol ?? ''),
                        TextInput::make('minimum_stock')
                            ->label('Existencia Mínima')
                            ->required()
                            ->numeric()
                            ->default(0.0)
                            ->prefix(fn (Get $get) => UnitOfMeasure::find($get('unit_of_measure_id'))?->symbol ?? ''),
                    ])
                    ->columns(3),
            ]);
    }
}
