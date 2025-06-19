<div class="space-y-4">
    @foreach ($carrito as $producto)
        <div
            class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 flex gap-4 items-center">

            <!-- Imagen del producto -->
            <div class="shrink-0">
                <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}"
                    class="w-20 h-20 object-cover rounded-full border" />
            </div>

            <!-- Detalles del producto -->
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    {{ $producto->nombre }}
                </h3>
                <p>
                    Precio: {{ number_format($producto->precio_aplicado, 2) }} HNL
                    @if ($producto->precio_aplicado == $producto->precio_mayorista)
                        <span class="text-sm text-green-600 font-semibold">(precio mayorista)</span>
                    @endif
                </p>

                <p class="text-gray-600 dark:text-gray-300">
                    Cantidad: {{ $producto->cantidad }}
                </p>
                <p class="text-gray-600 dark:text-gray-300">
                    Subtotal: {{ number_format($producto->subtotal, 2) }} HNL
                </p>

                <!-- Botones de acción -->
                <div class="mt-3 flex flex-wrap gap-2">
                    <x-filament::button color="success"
                        wire:click.prevent="call('aumentar', {{ $producto->id }})">+</x-filament::button>

                    <x-filament::button color="warning"
                        wire:click.prevent="call('reducir', {{ $producto->id }})">-</x-filament::button>

                    <x-filament::button color="danger"
                        wire:click.prevent="call('eliminar', {{ $producto->id }})">Eliminar</x-filament::button>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Total -->
<div class="text-right text-lg font-semibold text-gray-800 dark:text-white mt-6">
    Total: {{ number_format($carrito->sum(fn($p) => $p->subtotal), 2) }} HNL
</div>

<!-- Botón cancelar venta -->
<div class="text-right mt-4">
    <x-filament::button color="danger" wire:click.prevent="call('vaciar')">
        Cancelar venta / Vaciar carrito
    </x-filament::button>
</div>
