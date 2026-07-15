@php($dieta = $dieta ?? null)

<div class="space-y-6">
    {{-- Datos generales --}}
    <div>
        <h3 class="section-title mb-4">Datos de la dieta</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <x-input-label for="nombre" value="Nombre de la dieta" />
                <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                              :value="old('nombre', $dieta?->nombre)" required autofocus
                              placeholder="Ej. Dieta hipocalórica 1500 kcal" />
                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="objetivo" value="Objetivo" />
                <x-text-input id="objetivo" name="objetivo" type="text" class="mt-1 block w-full"
                              :value="old('objetivo', $dieta?->objetivo)"
                              placeholder="Ej. Pérdida de grasa" />
                <x-input-error :messages="$errors->get('objetivo')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="calorias_objetivo" value="Calorías objetivo (kcal/día)" />
                <x-text-input id="calorias_objetivo" name="calorias_objetivo" type="number" min="0" class="mt-1 block w-full"
                              :value="old('calorias_objetivo', $dieta?->calorias_objetivo)"
                              placeholder="Ej. 1500" />
                <x-input-error :messages="$errors->get('calorias_objetivo')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="descripcion" value="Descripción / notas" />
                <textarea id="descripcion" name="descripcion" rows="2"
                          class="textarea mt-1 block">{{ old('descripcion', $dieta?->descripcion) }}</textarea>
                <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="activa" value="0">
                    <input type="checkbox" name="activa" value="1"
                           class="rounded bg-ink-700 border-white/20 text-gold-400 focus:ring-gold-400/30"
                           @checked(old('activa', $dieta?->activa ?? true))>
                    <span class="text-sm text-zinc-300">Dieta activa</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Plan semanal --}}
    <div>
        <h3 class="section-title mb-2">Plan semanal</h3>
        <p class="text-xs text-zinc-500 mb-4">
            Escribe los alimentos de cada comida por día. El horario se define una vez por comida
            (las colaciones aceptan horas específicas, p. ej. <span class="text-zinc-400">7:00, 7:30</span>;
            desayuno/comida/cena suelen ser un rango, p. ej. <span class="text-zinc-400">8:00 - 9:00</span>).
        </p>

        <div class="overflow-x-auto rounded-lg border border-white/[0.06]">
            <table class="min-w-full border-separate border-spacing-0 text-sm">
                <thead>
                    <tr>
                        <th class="sticky left-0 z-10 bg-ink-800 px-3 py-2 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider border-b border-white/10 min-w-48">
                            Comida / Horario
                        </th>
                        @foreach (\App\Models\Dieta::DIAS as $dia => $diaLabel)
                            <th class="px-3 py-2 text-left text-xs font-medium text-gold-400 uppercase tracking-wider border-b border-white/10 min-w-44">
                                {{ $diaLabel }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach (\App\Models\Dieta::TIEMPOS as $tiempo => $tiempoLabel)
                        <tr class="align-top">
                            <th class="sticky left-0 z-10 bg-ink-800 px-3 py-2 text-left border-b border-white/[0.06]">
                                <div class="font-medium text-zinc-200">{{ $tiempoLabel }}</div>
                                <input type="text" name="plan[horarios][{{ $tiempo }}]"
                                       value="{{ old("plan.horarios.$tiempo", $dieta?->horario($tiempo)) }}"
                                       placeholder="Horario"
                                       class="input mt-1 block w-full !py-1 text-xs">
                            </th>
                            @foreach (\App\Models\Dieta::DIAS as $dia => $diaLabel)
                                <td class="px-2 py-2 border-b border-white/[0.06]">
                                    <textarea name="plan[dias][{{ $dia }}][{{ $tiempo }}]" rows="2"
                                              placeholder="—"
                                              class="textarea block w-full !py-1 text-xs">{{ old("plan.dias.$dia.$tiempo", $dieta?->contenido($dia, $tiempo)) }}</textarea>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
