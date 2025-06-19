<?php

namespace App\Filament\Pages;

use App\Models\Producto;
use Filament\Actions\Action as HeaderAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Attributes\On;

class Dashboard extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Inicio';
    protected static string $view = 'filament.pages.dashboard';

    // Escuchamos eventos emitidos desde el modal
    public function aumentar($productoId)
    {
        $carrito = session('carrito', []);
        $carrito[$productoId] = ($carrito[$productoId] ?? 0) + 1;
        session(['carrito' => $carrito]);
    }

    public function reducir($productoId)
    {
        $carrito = session('carrito', []);
        if (isset($carrito[$productoId])) {
            $carrito[$productoId]--;
            if ($carrito[$productoId] <= 0) {
                unset($carrito[$productoId]);
            }
        }
        session(['carrito' => $carrito]);
    }

    public function eliminar($productoId)
    {
        $carrito = session('carrito', []);
        unset($carrito[$productoId]);
        session(['carrito' => $carrito]);
    }

    public function vaciar()
    {
        session()->forget('carrito');
    }

    public function getViewData(): array
    {
        $carritoSession = session('carrito', []);

        $productos = Producto::whereIn('id', array_keys($carritoSession))->get();

        foreach ($productos as $producto) {
            $cantidad = $carritoSession[$producto->id] ?? 1;

            $precioUnitario = $producto->precio_venta;

            if (
                !is_null($producto->precio_mayorista) &&
                !is_null($producto->cantidad_mayorista) &&
                $cantidad >= $producto->cantidad_mayorista
            ) {
                $precioUnitario = $producto->precio_mayorista;
            }

            $producto->precio_aplicado = $precioUnitario;
            $producto->cantidad = $cantidad;
            $producto->subtotal = $precioUnitario * $cantidad;
        }

        return [
            'carrito' => $productos,
        ];
    }


    protected function getHeaderActions(): array
    {
        $cantidad = array_sum(session('carrito', []));

        return [
            HeaderAction::make('verCarrito')
                ->label('Ver carrito')
                ->icon('heroicon-o-shopping-cart')
                ->color('success')
                ->badge($cantidad > 0 ? (string) $cantidad : null)
                ->modalHeading('Productos en el carrito')
                ->modalContent(fn() => view('filament.pages.partials.carrito-modal', [
                    'carrito' => $this->getViewData()['carrito'],
                ])),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Producto::query())
            ->columns([
                Stack::make([
                    ImageColumn::make('imagen')
                        ->label('')
                        ->circular()
                        ->height(80)
                        ->width(80),

                    TextColumn::make('nombre')
                        ->label('Producto')
                        ->weight('bold')
                        ->searchable(),

                    TextColumn::make('precio_venta')
                        ->label('Precio')
                        ->money('HNL'),

                    TextColumn::make('stock')
                        ->label('Stock disponible')
                        ->getStateUsing(function ($record) {
                            $carrito = session('carrito', []);
                            $enCarrito = $carrito[$record->id] ?? 0;
                            return max(0, $record->stock - $enCarrito);
                        })
                        ->color(fn($state) => $state > 0 ? 'success' : 'danger'),
                ]),
            ])
            ->actions([
                Action::make('agregarCarrito')
                    ->label('Agregar al carrito')
                    ->button()
                    ->color('primary')
                    ->disabled(function ($record) {
                        $carrito = session('carrito', []);
                        $enCarrito = $carrito[$record->id] ?? 0;
                        return ($record->stock - $enCarrito) <= 0;
                    })
                    ->action(function ($record) {
                        $carrito = session('carrito', []);
                        $carrito[$record->id] = ($carrito[$record->id] ?? 0) + 1;
                        session(['carrito' => $carrito]);

                        Notification::make()
                            ->title('Producto agregado al carrito')
                            ->success()
                            ->send();

                        $this->dispatch('refresh'); // Opcional: refresca visualmente si hay Livewire
                    }),
                    
            ])
            ->searchable()
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ]);
    }
}
