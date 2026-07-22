@php
    $u = auth()->user();
    $tabs = [
        ['label' => 'Historial', 'route' => 'ventas.index', 'pattern' => 'ventas.index', 'ver' => $u->puedeGestionarVentas()],
        ['label' => 'Nueva venta', 'route' => 'ventas.create', 'pattern' => 'ventas.create', 'ver' => $u->puedeGestionarVentas()],
        ['label' => 'Membresías', 'route' => 'membresias.index', 'pattern' => 'membresias.*', 'ver' => $u->puedeGestionarMembresias()],
        ['label' => 'Productos', 'route' => 'productos.index', 'pattern' => 'productos.*', 'ver' => $u->puedeGestionarVentas()],
        ['label' => 'Servicios', 'route' => 'servicios.index', 'pattern' => 'servicios.*', 'ver' => $u->puedeGestionarVentas()],
    ];
@endphp

<div class="flex flex-wrap gap-2 border-b border-white/[0.06] pb-3">
    @foreach ($tabs as $tab)
        @continue(! $tab['ver'])
        @php($activo = request()->routeIs($tab['pattern']))
        <a href="{{ route($tab['route']) }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium transition
                  {{ $activo
                        ? 'bg-gold-400/15 text-gold-300 ring-1 ring-gold-400/20'
                        : 'text-zinc-400 hover:text-white hover:bg-white/5' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
