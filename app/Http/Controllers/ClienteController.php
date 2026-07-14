<?php

namespace App\Http\Controllers;

use App\Enums\ClienteEstado;
use App\Enums\Genero;
use App\Enums\UserRole;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ClienteController extends Controller
{
    /**
     * Listado de clientes con búsqueda y filtro por estado.
     */
    public function index(Request $request): View
    {
        $clientes = Cliente::query()
            ->with(['user', 'entrenador', 'nutriologo'])
            ->buscar($request->input('buscar'))
            ->estado($request->input('estado'))
            ->join('users', 'users.id', '=', 'clientes.user_id')
            ->orderBy('users.name')
            ->select('clientes.*')
            ->paginate(15)
            ->withQueryString();

        return view('clientes.index', [
            'clientes' => $clientes,
            'estados' => ClienteEstado::cases(),
            'filtros' => $request->only(['buscar', 'estado']),
        ]);
    }

    /**
     * Formulario de alta de cliente.
     */
    public function create(): View
    {
        return view('clientes.create', $this->formData());
    }

    /**
     * Persistir un nuevo cliente (cuenta de usuario + perfil).
     */
    public function store(StoreClienteRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $cliente = DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::Cliente,
            ]);

            return $user->cliente()->create([
                'telefono' => $data['telefono'] ?? null,
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'genero' => $data['genero'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'foto' => $this->guardarFoto($request),
                'contacto_emergencia_nombre' => $data['contacto_emergencia_nombre'] ?? null,
                'contacto_emergencia_telefono' => $data['contacto_emergencia_telefono'] ?? null,
                'fecha_ingreso' => $data['fecha_ingreso'],
                'estado' => $data['estado'],
                'entrenador_id' => $data['entrenador_id'] ?? null,
                'nutriologo_id' => $data['nutriologo_id'] ?? null,
                'notas' => $data['notas'] ?? null,
            ]);
        });

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('status', 'Cliente registrado correctamente.');
    }

    /**
     * Ficha del cliente.
     */
    public function show(Cliente $cliente): View
    {
        $cliente->load(['user', 'entrenador', 'nutriologo']);

        return view('clientes.show', ['cliente' => $cliente]);
    }

    /**
     * Formulario de edición.
     */
    public function edit(Cliente $cliente): View
    {
        $cliente->load('user');

        return view('clientes.edit', array_merge($this->formData(), ['cliente' => $cliente]));
    }

    /**
     * Actualizar cuenta y perfil del cliente.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $cliente) {
            $cliente->user->update(array_filter([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => filled($data['password'] ?? null) ? Hash::make($data['password']) : null,
            ], fn ($value) => $value !== null));

            $cliente->fill([
                'telefono' => $data['telefono'] ?? null,
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'genero' => $data['genero'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'contacto_emergencia_nombre' => $data['contacto_emergencia_nombre'] ?? null,
                'contacto_emergencia_telefono' => $data['contacto_emergencia_telefono'] ?? null,
                'fecha_ingreso' => $data['fecha_ingreso'],
                'estado' => $data['estado'],
                'entrenador_id' => $data['entrenador_id'] ?? null,
                'nutriologo_id' => $data['nutriologo_id'] ?? null,
                'notas' => $data['notas'] ?? null,
            ]);

            if ($request->hasFile('foto')) {
                $this->borrarFoto($cliente);
                $cliente->foto = $this->guardarFoto($request);
            }

            $cliente->save();
        });

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('status', 'Cliente actualizado correctamente.');
    }

    /**
     * Eliminar el cliente y su cuenta de usuario asociada.
     */
    public function destroy(Cliente $cliente): RedirectResponse
    {
        DB::transaction(function () use ($cliente) {
            $this->borrarFoto($cliente);
            // Al borrar el user, el perfil cae por la FK cascadeOnDelete.
            $cliente->user->delete();
        });

        return redirect()
            ->route('clientes.index')
            ->with('status', 'Cliente eliminado correctamente.');
    }

    /**
     * Datos compartidos por los formularios create/edit.
     *
     * @return array<string, mixed>
     */
    protected function formData(): array
    {
        return [
            'estados' => ClienteEstado::cases(),
            'generos' => Genero::cases(),
            'empleados' => User::staff()->orderBy('name')->get(),
        ];
    }

    protected function guardarFoto(Request $request): ?string
    {
        if (! $request->hasFile('foto')) {
            return null;
        }

        return $request->file('foto')->store('clientes', 'public');
    }

    protected function borrarFoto(Cliente $cliente): void
    {
        if ($cliente->foto) {
            Storage::disk('public')->delete($cliente->foto);
        }
    }
}
