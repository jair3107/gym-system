@php
    $fmt = fn ($v) => $v === null ? null : rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
    $mediciones = $seguimiento->mediciones; // ya ordenadas: más reciente primero
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Seguimiento de nutrición
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('seguimientos-nutricion.index') }}" class="text-sm text-zinc-400 hover:text-white">Volver</a>
                <a href="{{ route('seguimientos-nutricion.edit', $seguimiento) }}" class="btn-gold">Editar</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Encabezado --}}
            <div class="card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-brand font-semibold text-white">{{ $seguimiento->cliente->user->name }}</h3>
                        <p class="text-sm text-zinc-400 mt-1">
                            {{ $seguimiento->fecha_inicio->format('d/m/Y') }}
                            @if ($seguimiento->fecha_fin) → {{ $seguimiento->fecha_fin->format('d/m/Y') }} @endif
                        </p>
                    </div>
                    <span class="badge {{ $seguimiento->estado->badgeClasses() }}">{{ $seguimiento->estado->label() }}</span>
                </div>

                <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 text-sm">
                    <div>
                        <dt class="text-zinc-500">Peso inicial</dt>
                        <dd class="text-zinc-200 text-lg font-brand">{{ $fmt($seguimiento->peso_inicial) ?? '—' }} <span class="text-xs text-zinc-500">kg</span></dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Peso actual</dt>
                        <dd class="text-zinc-200 text-lg font-brand">{{ $fmt($seguimiento->peso_actual) ?? '—' }} <span class="text-xs text-zinc-500">kg</span></dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Meta</dt>
                        <dd class="text-zinc-200 text-lg font-brand">{{ $fmt($seguimiento->peso_meta) ?? '—' }} <span class="text-xs text-zinc-500">kg</span></dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Cambio</dt>
                        <dd class="text-lg font-brand {{ $seguimiento->cambio_peso < 0 ? 'text-green-400' : ($seguimiento->cambio_peso > 0 ? 'text-amber-400' : 'text-zinc-200') }}">
                            @if ($seguimiento->cambio_peso !== null)
                                {{ $seguimiento->cambio_peso > 0 ? '+' : '' }}{{ $fmt($seguimiento->cambio_peso) }} <span class="text-xs text-zinc-500">kg</span>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>

                @if ($seguimiento->notas)
                    <div class="mt-6 text-sm">
                        <dt class="text-zinc-500">Notas</dt>
                        <dd class="text-zinc-200 whitespace-pre-line mt-1">{{ $seguimiento->notas }}</dd>
                    </div>
                @endif
            </div>

            {{-- Plan de la dieta --}}
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="section-title">Plan de la dieta</h3>
                    @if ($seguimiento->dieta)
                        <a href="{{ route('dietas.show', $seguimiento->dieta) }}" class="text-sm text-gold-400 hover:text-gold-300">{{ $seguimiento->dieta->nombre }}</a>
                    @endif
                </div>
                @if ($seguimiento->dieta)
                    @include('dietas._tabla', ['dieta' => $seguimiento->dieta])
                @else
                    <p class="text-sm text-zinc-500">No hay una dieta asignada a este seguimiento.</p>
                @endif
            </div>

            {{-- Progreso / mediciones --}}
            <div class="card p-6">
                <h3 class="section-title mb-4">Progreso (mediciones de peso)</h3>

                {{-- Registrar medición --}}
                <form method="POST" action="{{ route('seguimientos-nutricion.mediciones.store', $seguimiento) }}"
                      class="flex flex-wrap gap-3 items-end mb-6 pb-6 border-b border-white/[0.06]">
                    @csrf
                    <div>
                        <x-input-label for="fecha" value="Fecha" />
                        <x-text-input id="fecha" name="fecha" type="date" class="mt-1"
                                      :value="old('fecha', now()->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('fecha')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="peso" value="Peso (kg)" />
                        <x-text-input id="peso" name="peso" type="number" step="0.01" min="20" max="400" class="mt-1"
                                      :value="old('peso')" placeholder="Ej. 80.4" required />
                        <x-input-error :messages="$errors->get('peso')" class="mt-2" />
                    </div>
                    <div class="flex-1 min-w-40">
                        <x-input-label for="notas_medicion" value="Nota (opcional)" />
                        <x-text-input id="notas_medicion" name="notas" type="text" class="mt-1 block w-full"
                                      :value="old('notas')" />
                        <x-input-error :messages="$errors->get('notas')" class="mt-2" />
                    </div>
                    <x-primary-button>Registrar</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06]">
                        <thead>
                            <tr class="text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3 text-right">Peso</th>
                                <th class="px-4 py-3 text-right">Cambio</th>
                                <th class="px-4 py-3">Nota</th>
                                <th class="px-4 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @forelse ($mediciones as $i => $medicion)
                                @php
                                    // El registro cronológicamente anterior (fecha menor) o el peso inicial.
                                    $anterior = $mediciones[$i + 1]->peso ?? $seguimiento->peso_inicial;
                                    $delta = $anterior !== null ? round((float) $medicion->peso - (float) $anterior, 2) : null;
                                @endphp
                                <tr class="hover:bg-white/[0.02] transition">
                                    <td class="px-4 py-3 text-sm text-zinc-300 whitespace-nowrap">{{ $medicion->fecha->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-200 text-right whitespace-nowrap">{{ $fmt($medicion->peso) }} kg</td>
                                    <td class="px-4 py-3 text-sm text-right whitespace-nowrap {{ $delta < 0 ? 'text-green-400' : ($delta > 0 ? 'text-amber-400' : 'text-zinc-500') }}">
                                        @if ($delta !== null && $delta != 0)
                                            {{ $delta > 0 ? '+' : '' }}{{ $fmt($delta) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-400">{{ $medicion->notas ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('mediciones-nutricion.destroy', $medicion) }}"
                                              onsubmit="return confirm('¿Eliminar esta medición?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-zinc-500 hover:text-red-400">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-500">
                                        Aún no hay mediciones. Registra la primera arriba.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
