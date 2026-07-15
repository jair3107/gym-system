@php($seguimiento = $seguimiento ?? null)

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Paciente y dieta --}}
    <div class="md:col-span-2">
        <h3 class="section-title">Paciente y dieta</h3>
    </div>

    <div>
        <x-input-label for="cliente_id" value="Paciente" />
        @if ($seguimiento)
            {{-- El paciente no se cambia al editar. --}}
            <div class="input mt-1 block bg-ink-700/40 text-zinc-300 cursor-not-allowed">
                {{ $seguimiento->cliente->user->name }}
            </div>
        @else
            <select id="cliente_id" name="cliente_id" class="select mt-1 block" required>
                <option value="">— Selecciona —</option>
                @foreach ($pacientes as $paciente)
                    <option value="{{ $paciente->id }}" @selected(old('cliente_id') == $paciente->id)>
                        {{ $paciente->user->name }}
                    </option>
                @endforeach
            </select>
            @if ($pacientes->isEmpty())
                <p class="mt-1 text-xs text-zinc-500">No tienes pacientes asignados todavía.</p>
            @endif
        @endif
        <x-input-error :messages="$errors->get('cliente_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="dieta_id" value="Dieta asignada" />
        <select id="dieta_id" name="dieta_id" class="select mt-1 block">
            <option value="">— Sin dieta —</option>
            @foreach ($dietas as $dieta)
                <option value="{{ $dieta->id }}" @selected(old('dieta_id', $seguimiento?->dieta_id) == $dieta->id)>
                    {{ $dieta->nombre }}
                </option>
            @endforeach
        </select>
        @if ($dietas->isEmpty())
            <p class="mt-1 text-xs text-zinc-500">No tienes dietas activas. Crea una en la sección Dietas.</p>
        @endif
        <x-input-error :messages="$errors->get('dieta_id')" class="mt-2" />
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

    {{-- Pesos --}}
    <div class="md:col-span-2 mt-4">
        <h3 class="section-title">Metas de peso</h3>
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
                      :value="old('peso_meta', $seguimiento?->peso_meta)" placeholder="Ej. 75" />
        <x-input-error :messages="$errors->get('peso_meta')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="notas" value="Notas" />
        <textarea id="notas" name="notas" rows="3" class="textarea mt-1 block">{{ old('notas', $seguimiento?->notas) }}</textarea>
        <x-input-error :messages="$errors->get('notas')" class="mt-2" />
    </div>
</div>
