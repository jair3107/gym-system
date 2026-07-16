@php($rutina = $rutina ?? null)

<div class="space-y-6">
    {{-- Datos generales --}}
    <div>
        <h3 class="section-title mb-4">Datos de la rutina</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <x-input-label for="nombre" value="Nombre de la rutina" />
                <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                              :value="old('nombre', $rutina?->nombre)" required autofocus
                              placeholder="Ej. Fuerza full-body 3 días" />
                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="objetivo" value="Objetivo" />
                <x-text-input id="objetivo" name="objetivo" type="text" class="mt-1 block w-full"
                              :value="old('objetivo', $rutina?->objetivo)"
                              placeholder="Ej. Hipertrofia" />
                <x-input-error :messages="$errors->get('objetivo')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="nivel" value="Nivel" />
                <select id="nivel" name="nivel" class="select mt-1 block w-full">
                    <option value="">— Sin especificar —</option>
                    @foreach (['Principiante', 'Intermedio', 'Avanzado'] as $nivel)
                        <option value="{{ $nivel }}" @selected(old('nivel', $rutina?->nivel) === $nivel)>{{ $nivel }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('nivel')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="duracion_min" value="Duración por sesión (min)" />
                <x-text-input id="duracion_min" name="duracion_min" type="number" min="0" class="mt-1 block w-full"
                              :value="old('duracion_min', $rutina?->duracion_min)"
                              placeholder="Ej. 60" />
                <x-input-error :messages="$errors->get('duracion_min')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="descripcion" value="Descripción / notas" />
                <textarea id="descripcion" name="descripcion" rows="2"
                          class="textarea mt-1 block">{{ old('descripcion', $rutina?->descripcion) }}</textarea>
                <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="activa" value="0">
                    <input type="checkbox" name="activa" value="1"
                           class="rounded bg-ink-700 border-white/20 text-gold-400 focus:ring-gold-400/30"
                           @checked(old('activa', $rutina?->activa ?? true))>
                    <span class="text-sm text-zinc-300">Rutina activa</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Plan semanal --}}
    <div>
        <h3 class="section-title mb-2">Plan semanal</h3>
        <p class="text-xs text-zinc-500 mb-4">
            Escribe los ejercicios de cada bloque por día (p. ej. <span class="text-zinc-400">Sentadilla 4×8, Press banca 4×8</span>).
            La duración se define una vez por bloque (p. ej. <span class="text-zinc-400">10 min</span>).
        </p>

        <div class="overflow-x-auto rounded-lg border border-white/[0.06]">
            <table class="min-w-full border-separate border-spacing-0 text-sm">
                <thead>
                    <tr>
                        <th class="sticky left-0 z-10 bg-ink-800 px-3 py-2 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider border-b border-white/10 min-w-48">
                            Bloque / Duración
                        </th>
                        @foreach (\App\Models\Rutina::DIAS as $dia => $diaLabel)
                            <th class="px-3 py-2 text-left text-xs font-medium text-gold-400 uppercase tracking-wider border-b border-white/10 min-w-44">
                                {{ $diaLabel }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach (\App\Models\Rutina::BLOQUES as $bloque => $bloqueLabel)
                        <tr class="align-top">
                            <th class="sticky left-0 z-10 bg-ink-800 px-3 py-2 text-left border-b border-white/[0.06]">
                                <div class="font-medium text-zinc-200">{{ $bloqueLabel }}</div>
                                <input type="text" name="plan[duraciones][{{ $bloque }}]"
                                       value="{{ old("plan.duraciones.$bloque", $rutina?->duracion($bloque)) }}"
                                       placeholder="Duración"
                                       class="input mt-1 block w-full !py-1 text-xs">
                            </th>
                            @foreach (\App\Models\Rutina::DIAS as $dia => $diaLabel)
                                <td class="px-2 py-2 border-b border-white/[0.06]">
                                    <textarea name="plan[dias][{{ $dia }}][{{ $bloque }}]" rows="2"
                                              placeholder="—"
                                              class="textarea block w-full !py-1 text-xs">{{ old("plan.dias.$dia.$bloque", $rutina?->contenido($dia, $bloque)) }}</textarea>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
