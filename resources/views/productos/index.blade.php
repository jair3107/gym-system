<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Caja · Ventas
            </h2>
            <a href="{{ route('productos.create') }}" class="btn-gold">
                + Nuevo producto
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('ventas._tabs')

            @if (session('status'))
                <div class="bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card p-6">
                <form method="GET" action="{{ route('productos.index') }}" class="flex flex-wrap gap-3 items-end mb-6">
                    <div class="flex-1 min-w-48">
                        <x-input-label for="buscar" value="Buscar" />
                        <x-text-input id="buscar" name="buscar" type="text" class="mt-1 block w-full"
                                      :value="$filtros['buscar'] ?? ''" placeholder="Nombre o descripción" />
                    </div>
                    <x-primary-button>Filtrar</x-primary-button>
                    @if (!empty($filtros['buscar']))
                        <a href="{{ route('productos.index') }}" class="text-sm text-zinc-500 hover:text-gold-400 underline">Limpiar</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06]">
                        <thead>
                            <tr class="text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Producto</th>
                                <th class="px-4 py-3 text-right">Precio</th>
                                <th class="px-4 py-3 text-right">Stock</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @forelse ($productos as $producto)
                                <tr class="hover:bg-white/[0.02] transition">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-zinc-200">{{ $producto->nombre }}</div>
                                        @if ($producto->descripcion)
                                            <div class="text-sm text-zinc-500 line-clamp-1">{{ $producto->descripcion }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-zinc-200 font-medium">${{ number_format($producto->precio, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        @if ($producto->stock <= 0)
                                            <span class="badge bg-red-500/15 text-red-400 ring-1 ring-red-500/20">Agotado</span>
                                        @elseif ($producto->stock <= 5)
                                            <span class="text-amber-400 font-medium">{{ $producto->stock }}</span>
                                        @else
                                            <span class="text-zinc-300">{{ $producto->stock }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($producto->activo)
                                            <span class="badge bg-green-500/15 text-green-400 ring-1 ring-green-500/20">Activo</span>
                                        @else
                                            <span class="badge bg-zinc-500/15 text-zinc-400 ring-1 ring-zinc-500/20">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                                        <a href="{{ route('productos.edit', $producto) }}" class="text-zinc-400 hover:text-white">Editar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-500">
                                        Aún no hay productos. Crea el primero con "Nuevo producto".
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $productos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
