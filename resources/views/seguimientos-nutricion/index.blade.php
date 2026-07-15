<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Seguimientos de nutrición
            </h2>
            <a href="{{ route('seguimientos-nutricion.create') }}" class="btn-gold">
                + Nuevo seguimiento
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
                <form method="GET" action="{{ route('seguimientos-nutricion.index') }}" class="flex flex-wrap gap-3 items-end mb-6">
                    <div>
                        <x-input-label for="estado" value="Estado" />
                        <select id="estado" name="estado" class="select mt-1">
                            <option value="">Todos</option>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->value }}" @selected(($filtros['estado'] ?? '') === $estado->value)>
                                    {{ $estado->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>Filtrar</x-primary-button>
                    @if (!empty($filtros['estado']))
                        <a href="{{ route('seguimientos-nutricion.index') }}" class="text-sm text-zinc-500 hover:text-gold-400 underline">Limpiar</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06]">
                        <thead>
                            <tr class="text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Paciente</th>
                                <th class="px-4 py-3">Dieta</th>
                                <th class="px-4 py-3">Periodo</th>
                                <th class="px-4 py-3">Peso</th>
                                @if (auth()->user()->isSuperAdmin())
                                    <th class="px-4 py-3">Nutriólogo</th>
                                @endif
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @forelse ($seguimientos as $seguimiento)
                                <tr class="hover:bg-white/[0.02] transition">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-zinc-200">{{ $seguimiento->cliente->user->name }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-400">{{ $seguimiento->dieta?->nombre ?? '— sin dieta —' }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-400 whitespace-nowrap">
                                        {{ $seguimiento->fecha_inicio->format('d/m/Y') }}
                                        @if ($seguimiento->fecha_fin) → {{ $seguimiento->fecha_fin->format('d/m/Y') }} @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-400 whitespace-nowrap">
                                        @if ($seguimiento->peso_inicial !== null)
                                            {{ rtrim(rtrim($seguimiento->peso_inicial, '0'), '.') }}
                                            → {{ rtrim(rtrim($seguimiento->peso_actual, '0'), '.') }} kg
                                            @if ($seguimiento->cambio_peso !== null && $seguimiento->cambio_peso != 0)
                                                <span class="{{ $seguimiento->cambio_peso < 0 ? 'text-green-400' : 'text-amber-400' }}">
                                                    ({{ $seguimiento->cambio_peso > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($seguimiento->cambio_peso, 2), '0'), '.') }})
                                                </span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    @if (auth()->user()->isSuperAdmin())
                                        <td class="px-4 py-3 text-sm text-zinc-400">{{ $seguimiento->nutriologo?->name ?? '—' }}</td>
                                    @endif
                                    <td class="px-4 py-3">
                                        <span class="badge {{ $seguimiento->estado->badgeClasses() }}">{{ $seguimiento->estado->label() }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm space-x-3 whitespace-nowrap">
                                        <a href="{{ route('seguimientos-nutricion.show', $seguimiento) }}" class="text-gold-400 hover:text-gold-300">Ver</a>
                                        <a href="{{ route('seguimientos-nutricion.edit', $seguimiento) }}" class="text-zinc-400 hover:text-white">Editar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-zinc-500">
                                        Aún no hay seguimientos. Crea el primero con "Nuevo seguimiento".
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $seguimientos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
