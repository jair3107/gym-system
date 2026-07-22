@php($servicio = $servicio ?? null)

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <x-input-label for="nombre" value="Nombre del servicio" />
        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                      :value="old('nombre', $servicio?->nombre)" required autofocus
                      placeholder="Ej. Pase de día" />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="precio" value="Precio (MXN)" />
        <x-text-input id="precio" name="precio" type="number" step="0.01" min="0" class="mt-1 block w-full"
                      :value="old('precio', $servicio?->precio)" required
                      placeholder="Ej. 120.00" />
        <x-input-error :messages="$errors->get('precio')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="descripcion" value="Descripción" />
        <textarea id="descripcion" name="descripcion" rows="2"
                  class="textarea mt-1 block">{{ old('descripcion', $servicio?->descripcion) }}</textarea>
        <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1"
                   class="rounded bg-ink-700 border-white/20 text-gold-400 focus:ring-gold-400/30"
                   @checked(old('activo', $servicio?->activo ?? true))>
            <span class="text-sm text-zinc-300">Servicio activo (disponible para venta)</span>
        </label>
    </div>
</div>
