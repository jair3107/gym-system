<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Ficha de la rutina
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('rutinas.index') }}" class="text-sm text-zinc-400 hover:text-white">Volver</a>
                <a href="{{ route('rutinas.edit', $rutina) }}" class="btn-gold">Editar</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-brand font-semibold text-white">{{ $rutina->nombre }}</h3>
                        @if ($rutina->objetivo)
                            <p class="text-sm text-zinc-400 mt-1">{{ $rutina->objetivo }}</p>
                        @endif
                    </div>
                    @if ($rutina->activa)
                        <span class="badge bg-green-500/15 text-green-400 ring-1 ring-green-500/20">Activa</span>
                    @else
                        <span class="badge bg-zinc-500/15 text-zinc-400 ring-1 ring-zinc-500/20">Inactiva</span>
                    @endif
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-4 text-sm mt-6">
                    <div>
                        <dt class="text-zinc-500">Nivel</dt>
                        <dd class="text-zinc-200">{{ $rutina->nivel ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Duración por sesión</dt>
                        <dd class="text-zinc-200">{{ $rutina->duracion_min ? $rutina->duracion_min.' min' : '—' }}</dd>
                    </div>
                    @if (auth()->user()->isSuperAdmin())
                        <div>
                            <dt class="text-zinc-500">Entrenador</dt>
                            <dd class="text-zinc-200">{{ $rutina->entrenador?->name ?? '—' }}</dd>
                        </div>
                    @endif
                    <div class="sm:col-span-3">
                        <dt class="text-zinc-500">Descripción</dt>
                        <dd class="text-zinc-200 whitespace-pre-line">{{ $rutina->descripcion ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="card p-6">
                <h3 class="section-title mb-4">Plan semanal</h3>
                @include('rutinas._tabla', ['rutina' => $rutina])
            </div>
        </div>
    </div>
</x-app-layout>
