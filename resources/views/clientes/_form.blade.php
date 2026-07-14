@php($cliente = $cliente ?? null)

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Cuenta --}}
    <div class="md:col-span-2">
        <h3 class="section-title">Cuenta de acceso</h3>
    </div>

    <div>
        <x-input-label for="name" value="Nombre completo" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $cliente?->user->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                      :value="old('email', $cliente?->user->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" value="{{ $cliente ? 'Nueva contraseña (opcional)' : 'Contraseña' }}" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                      :required="! $cliente" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Confirmar contraseña" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full"
                      :required="! $cliente" autocomplete="new-password" />
    </div>

    {{-- Perfil --}}
    <div class="md:col-span-2 mt-4">
        <h3 class="section-title">Datos personales</h3>
    </div>

    <div>
        <x-input-label for="telefono" value="Teléfono" />
        <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full"
                      :value="old('telefono', $cliente?->telefono)" />
        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
        <x-text-input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="mt-1 block w-full"
                      :value="old('fecha_nacimiento', $cliente?->fecha_nacimiento?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="genero" value="Género" />
        <select id="genero" name="genero"
                class="select mt-1 block">
            <option value="">— Sin especificar —</option>
            @foreach ($generos as $genero)
                <option value="{{ $genero->value }}" @selected(old('genero', $cliente?->genero?->value) === $genero->value)>
                    {{ $genero->label() }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('genero')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="direccion" value="Dirección" />
        <x-text-input id="direccion" name="direccion" type="text" class="mt-1 block w-full"
                      :value="old('direccion', $cliente?->direccion)" />
        <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="foto" value="Foto" />
        @if ($cliente?->foto)
            <img src="{{ Storage::url($cliente->foto) }}" alt="Foto" class="h-20 w-20 rounded-full object-cover my-2">
        @endif
        <input id="foto" name="foto" type="file" accept="image/*"
               class="mt-1 block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gold-400/15 file:text-gold-300 file:font-medium hover:file:bg-gold-400/25 cursor-pointer" />
        <x-input-error :messages="$errors->get('foto')" class="mt-2" />
    </div>

    {{-- Contacto de emergencia --}}
    <div class="md:col-span-2 mt-4">
        <h3 class="section-title">Contacto de emergencia</h3>
    </div>

    <div>
        <x-input-label for="contacto_emergencia_nombre" value="Nombre" />
        <x-text-input id="contacto_emergencia_nombre" name="contacto_emergencia_nombre" type="text" class="mt-1 block w-full"
                      :value="old('contacto_emergencia_nombre', $cliente?->contacto_emergencia_nombre)" />
        <x-input-error :messages="$errors->get('contacto_emergencia_nombre')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="contacto_emergencia_telefono" value="Teléfono" />
        <x-text-input id="contacto_emergencia_telefono" name="contacto_emergencia_telefono" type="text" class="mt-1 block w-full"
                      :value="old('contacto_emergencia_telefono', $cliente?->contacto_emergencia_telefono)" />
        <x-input-error :messages="$errors->get('contacto_emergencia_telefono')" class="mt-2" />
    </div>

    {{-- Membresía / asignaciones --}}
    <div class="md:col-span-2 mt-4">
        <h3 class="section-title">Estado y asignaciones</h3>
    </div>

    <div>
        <x-input-label for="fecha_ingreso" value="Fecha de ingreso" />
        <x-text-input id="fecha_ingreso" name="fecha_ingreso" type="date" class="mt-1 block w-full"
                      :value="old('fecha_ingreso', $cliente?->fecha_ingreso?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('fecha_ingreso')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="estado" value="Estado" />
        <select id="estado" name="estado"
                class="select mt-1 block">
            @foreach ($estados as $estado)
                <option value="{{ $estado->value }}" @selected(old('estado', $cliente?->estado?->value ?? 'activo') === $estado->value)>
                    {{ $estado->label() }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('estado')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="entrenador_id" value="Entrenador asignado" />
        <select id="entrenador_id" name="entrenador_id"
                class="select mt-1 block">
            <option value="">— Ninguno —</option>
            @foreach ($empleados as $empleado)
                <option value="{{ $empleado->id }}" @selected(old('entrenador_id', $cliente?->entrenador_id) == $empleado->id)>
                    {{ $empleado->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('entrenador_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="nutriologo_id" value="Nutriólogo asignado" />
        <select id="nutriologo_id" name="nutriologo_id"
                class="select mt-1 block">
            <option value="">— Ninguno —</option>
            @foreach ($empleados as $empleado)
                <option value="{{ $empleado->id }}" @selected(old('nutriologo_id', $cliente?->nutriologo_id) == $empleado->id)>
                    {{ $empleado->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('nutriologo_id')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="notas" value="Notas" />
        <textarea id="notas" name="notas" rows="3"
                  class="select mt-1 block">{{ old('notas', $cliente?->notas) }}</textarea>
        <x-input-error :messages="$errors->get('notas')" class="mt-2" />
    </div>
</div>
