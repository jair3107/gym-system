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

    {{-- Plan semanal: por día, un enfoque muscular y su lista de ejercicios --}}
    @php($planInicial = $planInicial ?? [])

    <div x-data="{
            dias: @js($planInicial),
            siguienteId: 0,
            init() { this.dias.forEach(d => d.ejercicios.forEach(e => { e._id = this.siguienteId++ })) }
         }">
        <h3 class="section-title mb-2">Plan semanal</h3>
        <p class="text-xs text-zinc-500 mb-4">
            Para cada día de entrenamiento escribe el enfoque muscular y agrega sus ejercicios
            (nombre, series y apuntes). Deja vacíos los días de descanso.
        </p>

        <div class="space-y-4">
            <template x-for="dia in dias" :key="dia.slug">
                <div class="rounded-lg border border-white/[0.06] overflow-hidden">
                    <div class="flex flex-wrap items-center gap-3 bg-ink-800 px-4 py-3 border-b border-white/10">
                        <span class="font-brand font-semibold text-gold-400 uppercase tracking-wider min-w-24" x-text="dia.label"></span>
                        <input type="text"
                               :name="`plan[dias][${dia.slug}][enfoque]`"
                               x-model="dia.enfoque"
                               placeholder="Enfoque (ej. Pecho - Tríceps)"
                               class="input !py-1 text-sm flex-1 min-w-48">
                    </div>

                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                <th class="px-4 py-2">Ejercicio</th>
                                <th class="px-4 py-2 w-32">Series</th>
                                <th class="px-4 py-2 w-56">Apuntes</th>
                                <th class="px-2 py-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(ej, i) in dia.ejercicios" :key="ej._id">
                                <tr class="border-t border-white/[0.04]">
                                    <td class="px-3 py-1.5">
                                        <input type="text" :name="`plan[dias][${dia.slug}][ejercicios][${i}][nombre]`"
                                               x-model="ej.nombre" placeholder="Ej. Press banca"
                                               class="input !py-1 text-sm block w-full">
                                    </td>
                                    <td class="px-3 py-1.5">
                                        <input type="text" :name="`plan[dias][${dia.slug}][ejercicios][${i}][series]`"
                                               x-model="ej.series" placeholder="10, 8, 8, 6"
                                               class="input !py-1 text-sm block w-full">
                                    </td>
                                    <td class="px-3 py-1.5">
                                        <input type="text" :name="`plan[dias][${dia.slug}][ejercicios][${i}][apuntes]`"
                                               x-model="ej.apuntes" placeholder="Opcional"
                                               class="input !py-1 text-sm block w-full">
                                    </td>
                                    <td class="px-2 py-1.5 text-center">
                                        <button type="button" @click="dia.ejercicios.splice(i, 1)"
                                                class="text-zinc-500 hover:text-red-400" title="Quitar">✕</button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="dia.ejercicios.length === 0">
                                <td colspan="4" class="px-4 py-2 text-xs text-zinc-500">Sin ejercicios. Agrega el primero abajo.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="px-4 py-2 border-t border-white/[0.06]">
                        <button type="button"
                                @click="dia.ejercicios.push({ _id: siguienteId++, nombre: '', series: '', apuntes: '' })"
                                class="text-sm text-gold-400 hover:text-gold-300">
                            + Agregar ejercicio
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
