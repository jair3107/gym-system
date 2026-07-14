@php($puesto = $puesto ?? null)

<div class="space-y-5">
    <div>
        <x-input-label for="nombre" value="Nombre" />
        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                      :value="old('nombre', $puesto?->nombre)" required autofocus />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="slug" value="Slug (opcional)" />
        <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full"
                      :value="old('slug', $puesto?->slug)" placeholder="Se genera del nombre si lo dejas vacío" />
        <p class="mt-1 text-xs text-zinc-500">Identificador interno. Puestos con lógica especial: <code class="text-gold-400">admin</code>, <code class="text-gold-400">cajero</code>, <code class="text-gold-400">nutriologo</code>, <code class="text-gold-400">entrenador</code>.</p>
        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="descripcion" value="Descripción" />
        <x-text-input id="descripcion" name="descripcion" type="text" class="mt-1 block w-full"
                      :value="old('descripcion', $puesto?->descripcion)" />
        <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
    </div>
</div>
