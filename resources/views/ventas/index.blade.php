<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Caja · Ventas
            </h2>
            <a href="{{ route('ventas.create') }}" class="btn-gold">
                + Nueva venta
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
                <form method="GET" action="{{ route('ventas.index') }}" class="flex flex-wrap gap-3 items-end mb-6">
                    <div>
                        <x-input-label for="metodo_pago" value="Método de pago" />
                        <select id="metodo_pago" name="metodo_pago" class="select mt-1">
                            <option value="">Todos</option>
                            @foreach ($metodos as $metodo)
                                <option value="{{ $metodo->value }}" @selected(($filtros['metodo_pago'] ?? '') === $metodo->value)>
                                    {{ $metodo->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>Filtrar</x-primary-button>
                    @if (!empty($filtros['metodo_pago']))
                        <a href="{{ route('ventas.index') }}" class="text-sm text-zinc-500 hover:text-gold-400 underline">Limpiar</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06]">
                        <thead>
                            <tr class="text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Folio</th>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Cliente</th>
                                @if (auth()->user()->isSuperAdmin())
                                    <th class="px-4 py-3">Cajero</th>
                                @endif
                                <th class="px-4 py-3">Pago</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @forelse ($ventas as $venta)
                                <tr class="hover:bg-white/[0.02] transition">
                                    <td class="px-4 py-3 font-medium text-zinc-200">#{{ str_pad($venta->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-400">{{ $venta->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-300">{{ $venta->cliente?->nombre ?? 'Mostrador' }}</td>
                                    @if (auth()->user()->isSuperAdmin())
                                        <td class="px-4 py-3 text-sm text-zinc-400">{{ $venta->cajero?->name ?? '—' }}</td>
                                    @endif
                                    <td class="px-4 py-3">
                                        <span class="badge {{ $venta->metodo_pago->badgeClasses() }}">{{ $venta->metodo_pago->label() }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-zinc-100 font-semibold">${{ number_format($venta->total, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                                        <a href="{{ route('ventas.show', $venta) }}" class="text-gold-400 hover:text-gold-300">Ver ticket</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-zinc-500">
                                        Aún no hay ventas registradas. Registra la primera con "Nueva venta".
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $ventas->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
