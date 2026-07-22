<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMembresiaRequest;
use App\Http\Requests\UpdateMembresiaRequest;
use App\Models\Membresia;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembresiaController extends Controller
{
    /**
     * Catálogo de planes de membresía.
     */
    public function index(Request $request): View
    {
        $membresias = Membresia::query()
            ->withCount('beneficios')
            ->buscar($request->input('buscar'))
            ->orderByDesc('activa')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('membresias.index', [
            'membresias' => $membresias,
            'filtros' => $request->only(['buscar']),
        ]);
    }

    public function create(): View
    {
        return view('membresias.create', $this->formData());
    }

    public function store(StoreMembresiaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $membresia = Membresia::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'duracion_dias' => $data['duracion_dias'],
            'activa' => $data['activa'] ?? true,
            'renovable' => $data['renovable'] ?? false,
        ]);

        $this->sincronizarBeneficios($membresia, $data);

        return redirect()
            ->route('membresias.index')
            ->with('status', 'Membresía creada correctamente.');
    }

    public function edit(Membresia $membresia): View
    {
        $membresia->load('beneficios');

        return view('membresias.edit', array_merge($this->formData(), [
            'membresia' => $membresia,
            'beneficioProductos' => $membresia->beneficios->where('beneficiable_type', 'producto')->pluck('beneficiable_id')->all(),
            'beneficioServicios' => $membresia->beneficios->where('beneficiable_type', 'servicio')->pluck('beneficiable_id')->all(),
        ]));
    }

    public function update(UpdateMembresiaRequest $request, Membresia $membresia): RedirectResponse
    {
        $data = $request->validated();

        $membresia->update([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'duracion_dias' => $data['duracion_dias'],
            'activa' => $data['activa'] ?? false,
            'renovable' => $data['renovable'] ?? false,
        ]);

        $this->sincronizarBeneficios($membresia, $data);

        return redirect()
            ->route('membresias.index')
            ->with('status', 'Membresía actualizada correctamente.');
    }

    public function destroy(Membresia $membresia): RedirectResponse
    {
        $membresia->delete();

        return redirect()
            ->route('membresias.index')
            ->with('status', 'Membresía eliminada correctamente.');
    }

    /**
     * Catálogos de productos y servicios para elegir beneficios.
     *
     * @return array<string, mixed>
     */
    protected function formData(): array
    {
        return [
            'productos' => Producto::query()->where('activo', true)->orderBy('nombre')->get(),
            'servicios' => Servicio::query()->where('activo', true)->orderBy('nombre')->get(),
        ];
    }

    /**
     * Reescribe los beneficios incluidos del plan.
     *
     * @param  array<string, mixed>  $data
     */
    protected function sincronizarBeneficios(Membresia $membresia, array $data): void
    {
        $membresia->beneficios()->delete();

        $filas = [];
        foreach ($data['beneficio_productos'] ?? [] as $id) {
            $filas[] = ['beneficiable_type' => 'producto', 'beneficiable_id' => (int) $id];
        }
        foreach ($data['beneficio_servicios'] ?? [] as $id) {
            $filas[] = ['beneficiable_type' => 'servicio', 'beneficiable_id' => (int) $id];
        }

        if ($filas !== []) {
            $membresia->beneficios()->createMany($filas);
        }
    }
}
