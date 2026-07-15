<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Dietas
            </h2>
            <a href="{{ route('dietas.create') }}" class="btn-gold">
                + Nueva dieta
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card p-6">
                <form method="GET" action="{{ route('dietas.index') }}" class="flex flex-wrap gap-3 items-end mb-6">
                    <div class="flex-1 min-w-48">
                        <x-input-label for="buscar" value="Buscar" />
                        <x-text-input id="buscar" name="buscar" type="text" class="mt-1 block w-full"
                                      :value="$filtros['buscar'] ?? ''" placeholder="Nombre u objetivo" />
                    </div>
                    <x-primary-button>Filtrar</x-primary-button>
                    @if (!empty($filtros['buscar']))
                        <a href="{{ route('dietas.index') }}" class="text-sm text-zinc-500 hover:text-gold-400 underline">Limpiar</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06]">
                        <thead>
                            <tr class="text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Dieta</th>
                                <th class="px-4 py-3">Objetivo</th>
                                <th class="px-4 py-3">Calorías</th>
                                @if (auth()->user()->isSuperAdmin())
                                    <th class="px-4 py-3">Nutriólogo</th>
                                @endif
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @forelse ($dietas as $dieta)
                                <tr class="hover:bg-white/[0.02] transition">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-zinc-200">{{ $dieta->nombre }}</div>
                                        @if ($dieta->descripcion)
                                            <div class="text-sm text-zinc-500 line-clamp-1">{{ $dieta->descripcion }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-400">{{ $dieta->objetivo ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-400">{{ $dieta->calorias_objetivo ? $dieta->calorias_objetivo.' kcal' : '—' }}</td>
                                    @if (auth()->user()->isSuperAdmin())
                                        <td class="px-4 py-3 text-sm text-zinc-400">{{ $dieta->nutriologo?->name ?? '—' }}</td>
                                    @endif
                                    <td class="px-4 py-3">
                                        @if ($dieta->activa)
                                            <span class="badge bg-green-500/15 text-green-400 ring-1 ring-green-500/20">Activa</span>
                                        @else
                                            <span class="badge bg-zinc-500/15 text-zinc-400 ring-1 ring-zinc-500/20">Inactiva</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm space-x-3 whitespace-nowrap">
                                        <a href="{{ route('dietas.show', $dieta) }}" class="text-gold-400 hover:text-gold-300">Ver</a>
                                        <a href="{{ route('dietas.edit', $dieta) }}" class="text-zinc-400 hover:text-white">Editar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500">
                                        Aún no hay dietas. Crea la primera con "Nueva dieta".
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $dietas->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
