<?php

namespace App\Http\Controllers;

use App\Enums\ClienteEstado;
use App\Enums\Genero;
use App\Enums\UserRole;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Dieta;
use App\Models\Puesto;
use App\Models\Rutina;
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
        $clientes = $this->baseQuery($request->user())
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
        $user = auth()->user();

        if (! $user->puedeVerCliente($cliente)) {
            abort(403, 'Este cliente no está asignado a ti.');
        }

        $cliente->load(['user', 'entrenador.empleado', 'nutriologo.empleado']);

        $data = ['cliente' => $cliente];

        // Panel de entrenamiento: para el entrenador del alumno (o el dueño).
        $data['esEntrenadorDelCliente'] = $user->puedeGestionarEntrenamiento()
            && ($user->isSuperAdmin() || $cliente->entrenador_id === $user->id);

        if ($data['esEntrenadorDelCliente']) {
            $data['seguimientosEntrenamiento'] = $cliente->seguimientosEntrenamiento()
                ->with('rutina')->orderByDesc('fecha_inicio')->get();
            $data['misRutinas'] = $user->isSuperAdmin()
                ? Rutina::where('activa', true)->orderBy('nombre')->get()
                : $user->rutinas()->where('activa', true)->orderBy('nombre')->get();
        }

        // Panel de nutrición: para el nutriólogo del paciente (o el dueño).
        $data['esNutriologoDelCliente'] = $user->puedeGestionarNutricion()
            && ($user->isSuperAdmin() || $cliente->nutriologo_id === $user->id);

        if ($data['esNutriologoDelCliente']) {
            $data['seguimientosNutricion'] = $cliente->seguimientosNutricion()
                ->with('dieta')->orderByDesc('fecha_inicio')->get();
            $data['misDietas'] = $user->isSuperAdmin()
                ? Dieta::where('activa', true)->orderBy('nombre')->get()
                : $user->dietas()->where('activa', true)->orderBy('nombre')->get();
        }

        return view('clientes.show', $data);
    }

    /**
     * El entrenador/nutriólogo se autoasigna a un cliente que aún no lo tiene.
     */
    public function autoasignar(Cliente $cliente): RedirectResponse
    {
        $user = auth()->user();
        $cambios = [];

        if ($user->hasPuesto(Puesto::ENTRENADOR) && $cliente->entrenador_id === null) {
            $cambios['entrenador_id'] = $user->id;
        }

        if ($user->hasPuesto(Puesto::NUTRIOLOGO) && $cliente->nutriologo_id === null) {
            $cambios['nutriologo_id'] = $user->id;
        }

        if ($cambios === []) {
            return back()->with('status', 'No hay nada para autoasignar en este cliente.');
        }

        $cliente->update($cambios);

        return back()->with('status', 'Te has asignado a este cliente correctamente.');
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
     * Listado base según el rol: los gestores ven a todos; el entrenador ve a
     * sus alumnos y a los que no tienen entrenador; el nutriólogo, análogo con
     * sus pacientes. Quien no tenga puesto aplicable no ve a nadie.
     */
    protected function baseQuery(User $user)
    {
        $query = Cliente::query();

        if ($user->puedeGestionarClientes()) {
            return $query;
        }

        $entrena = $user->hasPuesto(Puesto::ENTRENADOR);
        $nutre = $user->hasPuesto(Puesto::NUTRIOLOGO);

        if (! $entrena && ! $nutre) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($user, $entrena, $nutre) {
            if ($entrena) {
                $q->orWhere('clientes.entrenador_id', $user->id)
                    ->orWhereNull('clientes.entrenador_id');
            }
            if ($nutre) {
                $q->orWhere('clientes.nutriologo_id', $user->id)
                    ->orWhereNull('clientes.nutriologo_id');
            }
        });
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
            'entrenadores' => User::conPuesto(Puesto::ENTRENADOR)->orderBy('name')->get(),
            'nutriologos' => User::conPuesto(Puesto::NUTRIOLOGO)->orderBy('name')->get(),
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
