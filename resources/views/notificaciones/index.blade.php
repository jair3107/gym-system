<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Notificaciones
            </h2>
            @if (auth()->user()->unreadNotifications->isNotEmpty())
                <form method="POST" action="{{ route('notificaciones.leer-todas') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="text-sm text-zinc-400 hover:text-white">Marcar todas como leídas</button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card divide-y divide-white/[0.06]">
                @forelse ($notificaciones as $n)
                    @php($data = $n->data)
                    <div class="flex items-start gap-3 p-4 {{ $n->read_at ? 'opacity-60' : '' }}">
                        <div class="mt-1 h-2 w-2 rounded-full flex-shrink-0 {{ $n->read_at ? 'bg-transparent' : 'bg-gold-400' }}"></div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-zinc-100">{{ $data['titulo'] ?? 'Notificación' }}</div>
                            <div class="text-sm text-zinc-400 mt-0.5">{{ $data['cuerpo'] ?? '' }}</div>
                            <div class="text-xs text-zinc-600 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                        </div>
                        @if (! $n->read_at)
                            <form method="POST" action="{{ route('notificaciones.leida', $n->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs text-gold-400 hover:text-gold-300 whitespace-nowrap">Marcar leída</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-zinc-500">
                        No tienes notificaciones.
                    </div>
                @endforelse
            </div>

            <div>
                {{ $notificaciones->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
