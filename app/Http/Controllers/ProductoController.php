<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductoController extends Controller
{
    /**
     * Catálogo de productos (con inventario).
     */
    public function index(Request $request): View
    {
        $productos = Producto::query()
            ->buscar($request->input('buscar'))
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('productos.index', [
            'productos' => $productos,
            'filtros' => $request->only(['buscar']),
        ]);
    }

    public function create(): View
    {
        return view('productos.create');
    }

    public function store(StoreProductoRequest $request): RedirectResponse
    {
        Producto::create($request->validated());

        return redirect()
            ->route('productos.index')
            ->with('status', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto): View
    {
        return view('productos.edit', ['producto' => $producto]);
    }

    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
        $producto->update($request->validated());

        return redirect()
            ->route('productos.index')
            ->with('status', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $producto->delete();

        return redirect()
            ->route('productos.index')
            ->with('status', 'Producto eliminado correctamente.');
    }
}
