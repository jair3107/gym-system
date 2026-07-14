@php($empleado = $empleado ?? null)
@php($puestosSeleccionados = old('puestos', $empleado?->user->puestos->pluck('id')->all() ?? []))

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Cuenta --}}
    <div class="md:col-span-2">
        <h3 class="section-title">Cuenta de acceso</h3>
    </div>

    <div>
        <x-input-label for="name" value="Nombre completo" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $empleado?->user->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                      :value="old('email', $empleado?->user->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" value="{{ $empleado ? 'Nueva contraseña (opcional)' : 'Contraseña' }}" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                      :required="! $empleado" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Confirmar contraseña" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full"
                      :required="! $empleado" autocomplete="new-password" />
    </div>

    {{-- Puestos --}}
    <div class="md:col-span-2 mt-4">
        <h3 class="section-title">Puestos</h3>
    </div>

    <div class="md:col-span-2">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach ($puestos as $puesto)
                <label class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/[0.02] px-3 py-2 cursor-pointer hover:bg-white/[0.05]">
                    <input type="checkbox" name="puestos[]" value="{{ $puesto->id }}"
                           @checked(in_array($puesto->id, $puestosSeleccionados))
                           class="rounded bg-ink-700 border-white/20 text-gold-500 focus:ring-gold-400/40 focus:ring-offset-ink-900">
                    <span class="text-sm text-zinc-200">{{ $puesto->nombre }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('puestos')" class="mt-2" />
    </div>

    {{-- Datos laborales --}}
    <div class="md:col-span-2 mt-4">
        <h3 class="section-title">Datos laborales</h3>
    </div>

    <div>
        <x-input-label for="fecha_contratacion" value="Fecha de contratación" />
        <x-text-input id="fecha_contratacion" name="fecha_contratacion" type="date" class="mt-1 block w-full"
                      :value="old('fecha_contratacion', $empleado?->fecha_contratacion?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('fecha_contratacion')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="salario" value="Salario" />
        <x-text-input id="salario" name="salario" type="number" step="0.01" min="0" class="mt-1 block w-full"
                      :value="old('salario', $empleado?->salario)" placeholder="0.00" />
        <x-input-error :messages="$errors->get('salario')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="estado" value="Estado" />
        <select id="estado" name="estado" class="select mt-1 block">
            @foreach ($estados as $estado)
                <option value="{{ $estado->value }}" @selected(old('estado', $empleado?->estado?->value ?? 'activo') === $estado->value)>
                    {{ $estado->label() }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('estado')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="telefono" value="Teléfono" />
        <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full"
                      :value="old('telefono', $empleado?->telefono)" />
        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="direccion" value="Dirección" />
        <x-text-input id="direccion" name="direccion" type="text" class="mt-1 block w-full"
                      :value="old('direccion', $empleado?->direccion)" />
        <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="foto" value="Foto" />
        @if ($empleado?->foto)
            <img src="{{ Storage::url($empleado->foto) }}" alt="Foto" class="h-20 w-20 rounded-full object-cover my-2">
        @endif
        <input id="foto" name="foto" type="file" accept="image/*"
               class="mt-1 block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gold-400/15 file:text-gold-300 file:font-medium hover:file:bg-gold-400/25 cursor-pointer" />
        <x-input-error :messages="$errors->get('foto')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="notas" value="Notas" />
        <textarea id="notas" name="notas" rows="3" class="textarea mt-1 block">{{ old('notas', $empleado?->notas) }}</textarea>
        <x-input-error :messages="$errors->get('notas')" class="mt-2" />
    </div>
</div>
