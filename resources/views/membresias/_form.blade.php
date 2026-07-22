@php
    $membresia = $membresia ?? null;
    $productos = $productos ?? collect();
    $servicios = $servicios ?? collect();
    $selProductos = old('beneficio_productos', $beneficioProductos ?? []);
    $selServicios = old('beneficio_servicios', $beneficioServicios ?? []);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <x-input-label for="nombre" value="Nombre del plan" />
        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                      :value="old('nombre', $membresia?->nombre)" required autofocus
                      placeholder="Ej. Mensualidad" />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="precio" value="Precio (MXN)" />
        <x-text-input id="precio" name="precio" type="number" step="0.01" min="0" class="mt-1 block w-full"
                      :value="old('precio', $membresia?->precio)" required
                      placeholder="Ej. 650.00" />
        <x-input-error :messages="$errors->get('precio')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="duracion_dias" value="Vigencia (días)" />
        <x-text-input id="duracion_dias" name="duracion_dias" type="number" min="1" class="mt-1 block w-full"
                      :value="old('duracion_dias', $membresia?->duracion_dias)" required
                      placeholder="Ej. 30" />
        <x-input-error :messages="$errors->get('duracion_dias')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="descripcion" value="Descripción" />
        <textarea id="descripcion" name="descripcion" rows="2"
                  class="textarea mt-1 block">{{ old('descripcion', $membresia?->descripcion) }}</textarea>
        <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
    </div>

    <div class="md:col-span-2 flex flex-wrap gap-6">
        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="activa" value="0">
            <input type="checkbox" name="activa" value="1"
                   class="rounded bg-ink-700 border-white/20 text-gold-400 focus:ring-gold-400/30"
                   @checked(old('activa', $membresia?->activa ?? true))>
            <span class="text-sm text-zinc-300">Plan activo (disponible para venta)</span>
        </label>
        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="renovable" value="0">
            <input type="checkbox" name="renovable" value="1"
                   class="rounded bg-ink-700 border-white/20 text-gold-400 focus:ring-gold-400/30"
                   @checked(old('renovable', $membresia?->renovable ?? true))>
            <span class="text-sm text-zinc-300">Permite auto-renovación (suscripción)</span>
        </label>
    </div>
</div>

{{-- Beneficios incluidos --}}
<div class="mt-8">
    <h3 class="section-title mb-1">Beneficios incluidos</h3>
    <p class="text-xs text-zinc-500 mb-4">
        Productos y servicios que el cliente puede solicitar sin costo mientras su membresía esté activa
        (no acumulables).
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-2">Productos</p>
            <div class="space-y-2 max-h-56 overflow-y-auto rounded-lg border border-white/[0.06] p-3">
                @forelse ($productos as $producto)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="beneficio_productos[]" value="{{ $producto->id }}"
                               class="rounded bg-ink-700 border-white/20 text-gold-400 focus:ring-gold-400/30"
                               @checked(in_array($producto->id, $selProductos))>
                        <span class="text-sm text-zinc-300">{{ $producto->nombre }}</span>
                    </label>
                @empty
                    <p class="text-xs text-zinc-500">No hay productos en el catálogo.</p>
                @endforelse
            </div>
        </div>
        <div>
            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-2">Servicios</p>
            <div class="space-y-2 max-h-56 overflow-y-auto rounded-lg border border-white/[0.06] p-3">
                @forelse ($servicios as $servicio)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="beneficio_servicios[]" value="{{ $servicio->id }}"
                               class="rounded bg-ink-700 border-white/20 text-gold-400 focus:ring-gold-400/30"
                               @checked(in_array($servicio->id, $selServicios))>
                        <span class="text-sm text-zinc-300">{{ $servicio->nombre }}</span>
                    </label>
                @empty
                    <p class="text-xs text-zinc-500">No hay servicios en el catálogo.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
