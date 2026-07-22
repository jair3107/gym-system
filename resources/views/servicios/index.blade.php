<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Caja · Ventas
            </h2>
            <a href="{{ route('servicios.create') }}" class="btn-gold">
                + Nuevo servicio
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
                <form method="GET" action="{{ route('servicios.index') }}" class="flex flex-wrap gap-3 items-end mb-6">
                    <div class="flex-1 min-w-48">
                        <x-input-label for="buscar" value="Buscar" />
                        <x-text-input id="buscar" name="buscar" type="text" class="mt-1 block w-full"
                                      :value="$filtros['buscar'] ?? ''" placeholder="Nombre o descripción" />
                    </div>
                    <x-primary-button>Filtrar</x-primary-button>
                    @if (!empty($filtros['buscar']))
                        <a href="{{ route('servicios.index') }}" class="text-sm text-zinc-500 hover:text-gold-400 underline">Limpiar</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06]">
                        <thead>
                            <tr class="text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Servicio</th>
                                <th class="px-4 py-3 text-right">Precio</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @forelse ($servicios as $servicio)
                                <tr class="hover:bg-white/[0.02] transition">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-zinc-200">{{ $servicio->nombre }}</div>
                                        @if ($servicio->descripcion)
                                            <div class="text-sm text-zinc-500 line-clamp-1">{{ $servicio->descripcion }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-zinc-200 font-medium">${{ number_format($servicio->precio, 2) }}</td>
                                    <td class="px-4 py-3">
                                        @if ($servicio->activo)
                                            <span class="badge bg-green-500/15 text-green-400 ring-1 ring-green-500/20">Activo</span>
                                        @else
                                            <span class="badge bg-zinc-500/15 text-zinc-400 ring-1 ring-zinc-500/20">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                                        <a href="{{ route('servicios.edit', $servicio) }}" class="text-zinc-400 hover:text-white">Editar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-500">
                                        Aún no hay servicios. Crea el primero con "Nuevo servicio".
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $servicios->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
