<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Ticket #{{ str_pad($venta->id, 5, '0', STR_PAD_LEFT) }}
            </h2>
            <a href="{{ route('ventas.index') }}" class="text-sm text-zinc-400 hover:text-white">← Volver al historial</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card p-6 sm:p-8 space-y-6">
                {{-- Encabezado --}}
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="font-brand text-lg font-bold tracking-[0.25em] text-gold-300">AURUM</div>
                        <div class="text-sm text-zinc-500 mt-1">{{ $venta->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <span class="badge {{ $venta->metodo_pago->badgeClasses() }}">{{ $venta->metodo_pago->label() }}</span>
                </div>

                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-zinc-500">Cliente</dt>
                        <dd class="text-zinc-200 mt-0.5">{{ $venta->cliente?->nombre ?? 'Mostrador' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Atendió</dt>
                        <dd class="text-zinc-200 mt-0.5">{{ $venta->cajero?->name ?? '—' }}</dd>
                    </div>
                </dl>

                {{-- Conceptos --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06]">
                        <thead>
                            <tr class="text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                <th class="px-3 py-2">Concepto</th>
                                <th class="px-3 py-2 text-right">Precio</th>
                                <th class="px-3 py-2 text-right">Cant.</th>
                                <th class="px-3 py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @foreach ($venta->items as $item)
                                <tr>
                                    <td class="px-3 py-2 text-zinc-200">{{ $item->descripcion }}</td>
                                    <td class="px-3 py-2 text-right text-zinc-400">${{ number_format($item->precio_unitario, 2) }}</td>
                                    <td class="px-3 py-2 text-right text-zinc-400">{{ $item->cantidad }}</td>
                                    <td class="px-3 py-2 text-right text-zinc-200 font-medium">${{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-white/[0.1]">
                                <td colspan="3" class="px-3 py-3 text-right text-zinc-400">Total</td>
                                <td class="px-3 py-3 text-right text-xl font-brand font-bold text-gold-300">${{ number_format($venta->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($venta->membresiasCliente->isNotEmpty())
                    <div class="rounded-lg border border-white/[0.06] bg-ink-800/60 p-4 space-y-2">
                        <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Suscripción</div>
                        @foreach ($venta->membresiasCliente as $sub)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-300">{{ $sub->membresia?->nombre ?? 'Membresía' }} · vence {{ $sub->fecha_fin->format('d/m/Y') }}</span>
                                @if ($sub->auto_renovacion)
                                    <span class="badge bg-gold-400/15 text-gold-300 ring-1 ring-gold-400/20">Auto-renovación</span>
                                @else
                                    <span class="badge bg-zinc-500/15 text-zinc-400 ring-1 ring-zinc-500/20">Sin renovación</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($venta->automatica)
                    <div class="text-xs text-blue-400">Venta generada automáticamente por renovación de suscripción.</div>
                @endif

                @if ($venta->notas)
                    <div class="text-sm">
                        <div class="text-zinc-500">Notas</div>
                        <div class="text-zinc-300 mt-0.5">{{ $venta->notas }}</div>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('ventas.destroy', $venta) }}"
                  onsubmit="return confirm('¿Cancelar esta venta? Se reintegrará el inventario y se anularán las membresías generadas.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">Cancelar venta</button>
            </form>
        </div>
    </div>
</x-app-layout>
