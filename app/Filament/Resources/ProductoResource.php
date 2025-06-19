<?php

namespace App\Filament\Resources;

use App\Models\Producto;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\ProductoResource\Pages;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;





class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $pluralModelLabel = 'Productos';
    protected static ?string $navigationGroup = 'Productos y Servicios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('nombre')->required()->unique(ignoreRecord: true),
                    Toggle::make('estado')->label('Activo')->default(true),
                ]),

                Textarea::make('descripcion')->rows(3),

                Grid::make(3)->schema([
                    TextInput::make('stock')->numeric()->default(0)->required(),
                    TextInput::make('stock_minimo')->numeric()->default(0),
                    TextInput::make('vendidos')
                        ->label('Vendidos')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(false)
                        ->visible(fn(string $context) => $context === 'edit'),

                ]),

                Grid::make(3)->schema([
                    TextInput::make('precio_compra')
                        ->label('Precio compra')
                        ->required()
                        ->numeric()
                        ->live(debounce: 500)

                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $precio = floatval($get('precio_venta') ?? 0);
                            $isv = floatval($get('isv') ?? 0);
                            $precioConIsv = $precio + ($precio * $isv / 100);
                            $ganancia = $precioConIsv - floatval($state ?? 0);

                            $set('precio_venta_con_isv', round($precioConIsv, 2));
                            $set('ganancia_unidad', round($ganancia, 2));
                        }),

                    TextInput::make('precio_venta')
                        ->label('Precio venta')
                        ->required()
                        ->numeric()
                        ->live(debounce: 500)

                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $isv = floatval($get('isv') ?? 0);
                            $compra = floatval($get('precio_compra') ?? 0);
                            $precioConIsv = floatval($state) + (floatval($state) * $isv / 100);
                            $ganancia = $precioConIsv - $compra;

                            $set('precio_venta_con_isv', round($precioConIsv, 2));
                            $set('ganancia_unidad', round($ganancia, 2));
                        }),

                    TextInput::make('isv')
                        ->label('ISV %')
                        ->default(0)
                        ->numeric()
                        ->live(debounce: 500)

                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $precio = floatval($get('precio_venta') ?? 0);
                            $compra = floatval($get('precio_compra') ?? 0);
                            $precioConIsv = $precio + ($precio * floatval($state) / 100);
                            $ganancia = $precioConIsv - $compra;

                            $set('precio_venta_con_isv', round($precioConIsv, 2));
                            $set('ganancia_unidad', round($ganancia, 2));
                        }),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('precio_mayorista')
                        ->label('Precio al por mayor')
                        ->numeric()
                        ->default(null),

                    TextInput::make('cantidad_mayorista')
                        ->label('Cantidad mínima para precio mayorista')
                        ->numeric()
                        ->default(null),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('precio_venta_con_isv')
                        ->label('Precio con ISV')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('ganancia_unidad')
                        ->label('Ganancia por unidad')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),
                ]),

                Grid::make(2)->schema([
                    Grid::make(2)->schema([
                        Select::make('categoria')
                            ->label('Categoría')
                            ->options([
                                'Producto' => 'Producto',
                                'Servicio' => 'Servicio',
                            ])
                            ->required()
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $precio = floatval($get('precio_venta') ?? 0);
                                $isv = floatval($get('isv') ?? 0);
                                $compra = floatval($get('precio_compra') ?? 0);
                                $precioConIsv = $precio + ($precio * $isv / 100);
                                $ganancia = $precioConIsv - $compra;

                                $set('precio_venta_con_isv', round($precioConIsv, 2));
                                $set('ganancia_unidad', round($ganancia, 2));
                            }),


                        TextInput::make('marca')
                            ->label('Marca')
                            ->live(debounce: 500)

                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $precio = floatval($get('precio_venta') ?? 0);
                                $isv = floatval($get('isv') ?? 0);
                                $compra = floatval($get('precio_compra') ?? 0);
                                $precioConIsv = $precio + ($precio * $isv / 100);
                                $ganancia = $precioConIsv - $compra;

                                $set('precio_venta_con_isv', round($precioConIsv, 2));
                                $set('ganancia_unidad', round($ganancia, 2));
                            }),
                    ]),

                ]),

                FileUpload::make('imagen')
                    ->image()
                    ->imagePreviewHeight('250')
                    ->directory('productos')
                    ->label('Imagen')
                    ->required(fn(string $context) => $context === 'create')
                    ->preserveFilenames(false)
                    ->saveUploadedFileUsing(function ($file, $record) {
                        $nombre = $record?->nombre ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $nombreSanitizado = Str::slug($nombre);

                        $fileName = "{$nombreSanitizado}.{$extension}";
                        $path = "productos/{$fileName}";

                        // Usar putFileAs para evitar file_get_contents
                        Storage::disk('public')->putFileAs(
                            'productos', // carpeta destino
                            $file,       // archivo
                            $fileName    // nombre final
                        );

                        return $path; // esto guarda la ruta en la base de datos
                    }),

                Hidden::make('created_by')->default(fn() => Auth::id()),
                Hidden::make('propietario_id')->default(fn() => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('categoria'),
                TextColumn::make('marca'),
                TextColumn::make('stock')->sortable(),
                TextColumn::make('alerta_stock')
                    ->label('Alerta')
                    ->badge()
                    ->color(
                        fn($record) =>
                        $record->stock <= $record->stock_minimo
                            ? 'danger'
                            : 'success'
                    )
                    ->getStateUsing(
                        fn($record) =>
                        $record->stock <= $record->stock_minimo
                            ? 'Stock bajo'
                            : 'Satisfactorio'
                    ),
                TextColumn::make('precio_venta')->money('HNL'),
                TextColumn::make('ganancia_unidad')->money('HNL'),
                IconColumn::make('estado')->boolean()->label('Estado'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        true => 'Activos',
                        false => 'Inactivos',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return Producto::query()
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->count() ?: null;
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->orderByRaw('(stock <= stock_minimo) DESC')
            ->orderByDesc('id'); // puedes cambiar a nombre o stock si prefieres
    }
}
