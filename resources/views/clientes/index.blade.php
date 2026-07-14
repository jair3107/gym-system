<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Clientes
            </h2>
            <a href="{{ route('clientes.create') }}" class="btn-gold">
                + Nuevo cliente
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
                <form method="GET" action="{{ route('clientes.index') }}" class="flex flex-wrap gap-3 items-end mb-6">
                    <div class="flex-1 min-w-48">
                        <x-input-label for="buscar" value="Buscar" />
                        <x-text-input id="buscar" name="buscar" type="text" class="mt-1 block w-full"
                                      :value="$filtros['buscar'] ?? ''" placeholder="Nombre o email" />
                    </div>
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
                    @if (!empty($filtros['buscar']) || !empty($filtros['estado']))
                        <a href="{{ route('clientes.index') }}" class="text-sm text-zinc-500 hover:text-gold-400 underline">Limpiar</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06]">
                        <thead>
                            <tr class="text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Cliente</th>
                                <th class="px-4 py-3">Teléfono</th>
                                <th class="px-4 py-3">Entrenador</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @forelse ($clientes as $cliente)
                                <tr class="hover:bg-white/[0.02] transition">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center justify-center h-8 w-8 rounded-full bg-gold-400/15 text-gold-300 text-xs font-brand font-semibold">
                                                {{ strtoupper(substr($cliente->user->name, 0, 1)) }}
                                            </span>
                                            <div>
                                                <div class="font-medium text-zinc-200">{{ $cliente->user->name }}</div>
                                                <div class="text-sm text-zinc-500">{{ $cliente->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-400">{{ $cliente->telefono ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-400">{{ $cliente->entrenador?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="badge {{ $cliente->estado->badgeClasses() }}">
                                            {{ $cliente->estado->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm space-x-3 whitespace-nowrap">
                                        <a href="{{ route('clientes.show', $cliente) }}" class="text-gold-400 hover:text-gold-300">Ver</a>
                                        <a href="{{ route('clientes.edit', $cliente) }}" class="text-zinc-400 hover:text-white">Editar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-500">
                                        No hay clientes que coincidan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $clientes->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
