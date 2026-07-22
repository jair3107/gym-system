<?php

namespace App\Http\Controllers;

use App\Enums\ClienteEstado;
use App\Enums\MembresiaEstado;
use App\Enums\MetodoPago;
use App\Http\Requests\StoreVentaRequest;
use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VentaController extends Controller
{
    /**
     * Historial de ventas. El dueño ve todas; el cajero, las suyas.
     */
    public function index(Request $request): View
    {
        $ventas = $this->baseQuery()
            ->with(['cajero', 'cliente.user'])
            ->when($request->filled('metodo_pago'), fn ($q) => $q->where('metodo_pago', $request->input('metodo_pago')))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('ventas.index', [
            'ventas' => $ventas,
            'metodos' => MetodoPago::cases(),
            'filtros' => $request->only(['metodo_pago']),
        ]);
    }

    /**
     * Punto de venta: catálogos disponibles + carrito.
     */
    public function create(): View
    {
        $membresias = Membresia::query()->where('activa', true)->withCount('beneficios')->orderBy('nombre')->get();
        $productos = Producto::query()->where('activo', true)->where('stock', '>', 0)->orderBy('nombre')->get();
        $servicios = Servicio::query()->where('activo', true)->orderBy('nombre')->get();

        // Catálogo listo para el carrito de Alpine (evita lógica pesada en Blade).
        $catalogo = [
            'membresia' => $membresias->map(fn (Membresia $m) => [
                'id' => $m->id,
                'nombre' => $m->nombre,
                'precio' => (float) $m->precio,
                'extra' => 'Vigencia: '.$m->vigencia_texto.($m->beneficios_count ? ' · '.$m->beneficios_count.' beneficio'.($m->beneficios_count === 1 ? '' : 's') : ''),
                'stock' => null,
                'renovable' => $m->renovable,
            ])->values(),
            'producto' => $productos->map(fn (Producto $p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'precio' => (float) $p->precio,
                'extra' => null,
                'stock' => $p->stock,
            ])->values(),
            'servicio' => $servicios->map(fn (Servicio $s) => [
                'id' => $s->id,
                'nombre' => $s->nombre,
                'precio' => (float) $s->precio,
                'extra' => null,
                'stock' => null,
            ])->values(),
        ];

        return view('ventas.create', [
            'clientes' => Cliente::query()->with('user')->get()
                ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'catalogo' => $catalogo,
            'metodos' => MetodoPago::cases(),
            // Clientes con tarjeta primaria (habilitan la auto-renovación).
            'clientesConPrimaria' => \App\Models\Tarjeta::query()->where('primaria', true)
                ->pluck('cliente_id')->unique()->values(),
        ]);
    }

    /**
     * Registrar la venta: arma las líneas con precios del servidor, descuenta
     * inventario y, si incluye membresía, activa al cliente con su vigencia.
     */
    public function store(StoreVentaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $venta = DB::transaction(function () use ($data, $user) {
            $cliente = ! empty($data['cliente_id']) ? Cliente::find($data['cliente_id']) : null;

            $lineas = [];
            $total = 0;
            $membresiasVendidas = [];
            $porDescontar = [];  // producto_id => cantidad acumulada

            foreach ($data['items'] as $item) {
                $tipo = $item['tipo'];
                $cantidad = (int) $item['cantidad'];
                $vendible = $this->resolverVendible($tipo, (int) $item['id']);

                if ($tipo === 'membresia') {
                    if (! $cliente) {
                        throw ValidationException::withMessages([
                            'cliente_id' => 'Para vender una membresía debes seleccionar un cliente.',
                        ]);
                    }
                    // Una membresía por línea (la vigencia se otorga una vez).
                    $cantidad = 1;
                }

                if ($tipo === 'producto') {
                    $acumulado = $porDescontar[$vendible->id] ?? 0;
                    if ($vendible->stock < $acumulado + $cantidad) {
                        throw ValidationException::withMessages([
                            'items' => "No hay stock suficiente de «{$vendible->nombre}» (disponible: {$vendible->stock}).",
                        ]);
                    }
                    $porDescontar[$vendible->id] = $acumulado + $cantidad;
                }

                $precio = (float) $vendible->precio;
                $subtotal = round($precio * $cantidad, 2);
                $total += $subtotal;

                $lineas[] = [
                    'vendible_type' => $tipo,
                    'vendible_id' => $vendible->id,
                    'descripcion' => $vendible->nombre,
                    'precio_unitario' => $precio,
                    'cantidad' => $cantidad,
                    'subtotal' => $subtotal,
                ];

                if ($tipo === 'membresia') {
                    $membresiasVendidas[] = $vendible;
                }
            }

            $venta = Venta::create([
                'cajero_id' => $user->id,
                'cliente_id' => $cliente?->id,
                'total' => round($total, 2),
                'metodo_pago' => $data['metodo_pago'],
                'notas' => $data['notas'] ?? null,
            ]);

            $venta->items()->createMany($lineas);

            // Descontar inventario de productos vendidos.
            foreach ($porDescontar as $productoId => $cantidad) {
                Producto::where('id', $productoId)->decrement('stock', $cantidad);
            }

            // Tarjeta primaria del cliente (para el autocobro de la renovación).
            $tarjetaPrimaria = $cliente?->tarjetaPrimaria();

            // Registrar la vigencia de cada membresía y activar al cliente.
            foreach ($membresiasVendidas as $membresia) {
                $inicio = now()->startOfDay();
                // La auto-renovación solo aplica si el plan la permite.
                $autoRenovacion = ! empty($data['auto_renovacion']) && $membresia->renovable;

                // Sin tarjeta primaria no se puede activar la auto-renovación.
                if ($autoRenovacion && ! $tarjetaPrimaria) {
                    throw ValidationException::withMessages([
                        'auto_renovacion' => 'El cliente no tiene una tarjeta primaria; no se puede activar la auto-renovación.',
                    ]);
                }

                $cliente->membresiasCliente()->create([
                    'membresia_id' => $membresia->id,
                    'venta_id' => $venta->id,
                    'cajero_id' => $user->id,
                    'fecha_inicio' => $inicio->toDateString(),
                    'fecha_fin' => $inicio->copy()->addDays($membresia->duracion_dias)->toDateString(),
                    'estado' => MembresiaEstado::Activa,
                    'auto_renovacion' => $autoRenovacion,
                    'metodo_pago' => $autoRenovacion ? MetodoPago::Tarjeta : $data['metodo_pago'],
                    'tarjeta_id' => $autoRenovacion ? $tarjetaPrimaria->id : null,
                ]);
            }

            if ($membresiasVendidas !== [] && $cliente) {
                $cliente->update(['estado' => ClienteEstado::Activo]);
            }

            return $venta;
        });

        return redirect()
            ->route('ventas.show', $venta)
            ->with('status', 'Venta registrada correctamente.');
    }

    /**
     * Ticket de la venta.
     */
    public function show(Venta $venta): View
    {
        $this->autorizarAcceso($venta);
        $venta->load(['cajero', 'cliente.user', 'items', 'membresiasCliente.membresia']);

        return view('ventas.show', ['venta' => $venta]);
    }

    /**
     * Cancelar una venta: reintegra el stock y anula las membresías generadas.
     */
    public function destroy(Venta $venta): RedirectResponse
    {
        $this->autorizarAcceso($venta);
        $venta->load('items', 'cliente');

        DB::transaction(function () use ($venta) {
            // Reintegrar stock de los productos vendidos.
            foreach ($venta->items as $item) {
                if ($item->vendible_type === 'producto' && $item->vendible_id) {
                    Producto::where('id', $item->vendible_id)->increment('stock', $item->cantidad);
                }
            }

            // Anular las vigencias que originó esta venta.
            $venta->membresiasCliente()->update(['estado' => MembresiaEstado::Cancelada]);

            $cliente = $venta->cliente;
            $venta->delete();

            // Si el cliente se queda sin membresía vigente, pasa a inactivo.
            if ($cliente && ! $cliente->membresiaVigente()) {
                $cliente->update(['estado' => ClienteEstado::Inactivo]);
            }
        });

        return redirect()
            ->route('ventas.index')
            ->with('status', 'Venta cancelada. Se reintegró el inventario.');
    }

    /**
     * Recupera el elemento vendible activo o falla la validación.
     */
    protected function resolverVendible(string $tipo, int $id): Model
    {
        $mapa = [
            'membresia' => [Membresia::class, 'activa'],
            'producto' => [Producto::class, 'activo'],
            'servicio' => [Servicio::class, 'activo'],
        ];

        [$clase, $columna] = $mapa[$tipo];

        $registro = $clase::query()->where('id', $id)->where($columna, true)->first();

        if (! $registro) {
            throw ValidationException::withMessages([
                'items' => 'Uno de los conceptos seleccionados ya no está disponible.',
            ]);
        }

        return $registro;
    }

    /**
     * Base del listado según el rol: el dueño ve todo, el cajero lo suyo.
     */
    protected function baseQuery()
    {
        $user = auth()->user();

        return $user->isSuperAdmin()
            ? Venta::query()
            : Venta::query()->deCajero($user->id);
    }

    /**
     * El dueño accede a cualquier venta; el cajero solo a las suyas.
     */
    protected function autorizarAcceso(Venta $venta): void
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin() && $venta->cajero_id !== $user->id) {
            abort(403, 'Esta venta no te pertenece.');
        }
    }
}
