<?php

namespace App\Filament\Widgets;

use App\Models\Producto;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class CarritoWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        // Obtenemos los IDs del carrito desde la sesión
        $carrito = session('carrito', []);

        // $carrito es un arreglo asociativo [id => cantidad].
        // Debemos obtener solo las llaves (IDs) para filtrar
        $ids = array_keys($carrito);

        return Producto::query()->whereIn('id', $ids);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('nombre')->label('Producto'),
            TextColumn::make('precio_venta')->label('Precio')->money('HNL'),
        ];
    }
}
