<div class="divide-y divide-white/[0.06]">
    @forelse ($clientes as $cliente)
        <a href="{{ route('clientes.show', $cliente) }}" class="flex items-center gap-3 py-3 hover:bg-white/[0.02] px-2 -mx-2 rounded-lg transition">
            <span class="flex items-center justify-center h-8 w-8 rounded-full bg-gold-400/15 text-gold-300 text-xs font-brand font-semibold">
                {{ strtoupper(substr($cliente->user->name, 0, 1)) }}
            </span>
            <div class="flex-1">
                <div class="text-sm font-medium text-zinc-200">{{ $cliente->user->name }}</div>
                <div class="text-xs text-zinc-500">{{ $cliente->user->email }}</div>
            </div>
            <span class="badge {{ $cliente->estado->badgeClasses() }}">{{ $cliente->estado->label() }}</span>
        </a>
    @empty
        <p class="py-4 text-center text-sm text-zinc-500">Sin clientes asignados todavía.</p>
    @endforelse
</div>
