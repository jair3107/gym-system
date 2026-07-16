@php($seguimiento = $seguimiento ?? null)

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Alumno y rutina --}}
    <div class="md:col-span-2">
        <h3 class="section-title">Alumno y rutina</h3>
    </div>

    <div>
        <x-input-label for="cliente_id" value="Alumno" />
        @if ($seguimiento)
            {{-- El alumno no se cambia al editar. --}}
            <div class="input mt-1 block bg-ink-700/40 text-zinc-300 cursor-not-allowed">
                {{ $seguimiento->cliente->user->name }}
            </div>
        @else
            <select id="cliente_id" name="cliente_id" class="select mt-1 block" required>
                <option value="">— Selecciona —</option>
                @foreach ($alumnos as $alumno)
                    <option value="{{ $alumno->id }}" @selected(old('cliente_id') == $alumno->id)>
                        {{ $alumno->user->name }}
                    </option>
                @endforeach
            </select>
            @if ($alumnos->isEmpty())
                <p class="mt-1 text-xs text-zinc-500">No tienes alumnos asignados todavía.</p>
            @endif
        @endif
        <x-input-error :messages="$errors->get('cliente_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="rutina_id" value="Rutina asignada" />
        <select id="rutina_id" name="rutina_id" class="select mt-1 block">
            <option value="">— Sin rutina —</option>
            @foreach ($rutinas as $rutina)
                <option value="{{ $rutina->id }}" @selected(old('rutina_id', $seguimiento?->rutina_id) == $rutina->id)>
                    {{ $rutina->nombre }}
                </option>
            @endforeach
        </select>
        @if ($rutinas->isEmpty())
            <p class="mt-1 text-xs text-zinc-500">No tienes rutinas activas. Crea una en la sección Rutinas.</p>
        @endif
        <x-input-error :messages="$errors->get('rutina_id')" class="mt-2" />
    </div>

    {{-- Periodo --}}
    <div class="md:col-span-2 mt-4">
        <h3 class="section-title">Periodo y estado</h3>
    </div>

    <div>
        <x-input-label for="fecha_inicio" value="Fecha de inicio" />
        <x-text-input id="fecha_inicio" name="fecha_inicio" type="date" class="mt-1 block w-full"
                      :value="old('fecha_inicio', $seguimiento?->fecha_inicio?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('fecha_inicio')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="fecha_fin" value="Fecha de fin (opcional)" />
        <x-text-input id="fecha_fin" name="fecha_fin" type="date" class="mt-1 block w-full"
                      :value="old('fecha_fin', $seguimiento?->fecha_fin?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('fecha_fin')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="estado" value="Estado" />
        <select id="estado" name="estado" class="select mt-1 block">
            @foreach ($estados as $estado)
                <option value="{{ $estado->value }}" @selected(old('estado', $seguimiento?->estado?->value ?? 'activo') === $estado->value)>
                    {{ $estado->label() }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('estado')" class="mt-2" />
    </div>

    {{-- Metas físicas --}}
    <div class="md:col-span-2 mt-4">
        <h3 class="section-title">Metas de peso y masa muscular</h3>
    </div>

    <div>
        <x-input-label for="peso_inicial" value="Peso inicial (kg)" />
        <x-text-input id="peso_inicial" name="peso_inicial" type="number" step="0.01" min="20" max="400" class="mt-1 block w-full"
                      :value="old('peso_inicial', $seguimiento?->peso_inicial)" placeholder="Ej. 82.5" />
        <x-input-error :messages="$errors->get('peso_inicial')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="peso_meta" value="Peso meta (kg)" />
        <x-text-input id="peso_meta" name="peso_meta" type="number" step="0.01" min="20" max="400" class="mt-1 block w-full"
                      :value="old('peso_meta', $seguimiento?->peso_meta)" placeholder="Ej. 78" />
        <x-input-error :messages="$errors->get('peso_meta')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="masa_inicial" value="Masa muscular inicial (kg)" />
        <x-text-input id="masa_inicial" name="masa_inicial" type="number" step="0.01" min="0" max="200" class="mt-1 block w-full"
                      :value="old('masa_inicial', $seguimiento?->masa_inicial)" placeholder="Ej. 34.0" />
        <x-input-error :messages="$errors->get('masa_inicial')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="masa_meta" value="Masa muscular meta (kg)" />
        <x-text-input id="masa_meta" name="masa_meta" type="number" step="0.01" min="0" max="200" class="mt-1 block w-full"
                      :value="old('masa_meta', $seguimiento?->masa_meta)" placeholder="Ej. 38.0" />
        <x-input-error :messages="$errors->get('masa_meta')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="notas" value="Notas" />
        <textarea id="notas" name="notas" rows="3" class="textarea mt-1 block">{{ old('notas', $seguimiento?->notas) }}</textarea>
        <x-input-error :messages="$errors->get('notas')" class="mt-2" />
    </div>
</div>
