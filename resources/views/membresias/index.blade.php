<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Caja · Ventas
            </h2>
            <a href="{{ route('membresias.create') }}" class="btn-gold">
                + Nueva membresía
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
                <form method="GET" action="{{ route('membresias.index') }}" class="flex flex-wrap gap-3 items-end mb-6">
                    <div class="flex-1 min-w-48">
                        <x-input-label for="buscar" value="Buscar" />
                        <x-text-input id="buscar" name="buscar" type="text" class="mt-1 block w-full"
                                      :value="$filtros['buscar'] ?? ''" placeholder="Nombre o descripción" />
                    </div>
                    <x-primary-button>Filtrar</x-primary-button>
                    @if (!empty($filtros['buscar']))
                        <a href="{{ route('membresias.index') }}" class="text-sm text-zinc-500 hover:text-gold-400 underline">Limpiar</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06]">
                        <thead>
                            <tr class="text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Plan</th>
                                <th class="px-4 py-3">Vigencia</th>
                                <th class="px-4 py-3">Beneficios</th>
                                <th class="px-4 py-3 text-right">Precio</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @forelse ($membresias as $membresia)
                                <tr class="hover:bg-white/[0.02] transition">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-zinc-200">{{ $membresia->nombre }}</div>
                                        @if ($membresia->descripcion)
                                            <div class="text-sm text-zinc-500 line-clamp-1">{{ $membresia->descripcion }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-400">
                                        {{ $membresia->vigencia_texto }}
                                        @if ($membresia->renovable)
                                            <span class="block text-xs text-gold-400/80">auto-renovable</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-400">
                                        @if ($membresia->beneficios_count > 0)
                                            <span class="badge bg-gold-400/15 text-gold-300 ring-1 ring-gold-400/20">{{ $membresia->beneficios_count }} incluido{{ $membresia->beneficios_count === 1 ? '' : 's' }}</span>
                                        @else
                                            <span class="text-zinc-600">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-zinc-200 font-medium">${{ number_format($membresia->precio, 2) }}</td>
                                    <td class="px-4 py-3">
                                        @if ($membresia->activa)
                                            <span class="badge bg-green-500/15 text-green-400 ring-1 ring-green-500/20">Activa</span>
                                        @else
                                            <span class="badge bg-zinc-500/15 text-zinc-400 ring-1 ring-zinc-500/20">Inactiva</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                                        <a href="{{ route('membresias.edit', $membresia) }}" class="text-zinc-400 hover:text-white">Editar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500">
                                        Aún no hay membresías. Crea la primera con "Nueva membresía".
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $membresias->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
