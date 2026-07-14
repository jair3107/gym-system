<?php

namespace App\Http\Controllers;

use App\Enums\EmpleadoEstado;
use App\Enums\UserRole;
use App\Http\Requests\StoreEmpleadoRequest;
use App\Http\Requests\UpdateEmpleadoRequest;
use App\Models\Empleado;
use App\Models\Puesto;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EmpleadoController extends Controller
{
    public function index(Request $request): View
    {
        $empleados = Empleado::query()
            ->with(['user.puestos'])
            ->buscar($request->input('buscar'))
            ->estado($request->input('estado'))
            ->join('users', 'users.id', '=', 'empleados.user_id')
            ->orderBy('users.name')
            ->select('empleados.*')
            ->paginate(15)
            ->withQueryString();

        return view('empleados.index', [
            'empleados' => $empleados,
            'estados' => EmpleadoEstado::cases(),
            'filtros' => $request->only(['buscar', 'estado']),
        ]);
    }

    public function create(): View
    {
        return view('empleados.create', $this->formData());
    }

    public function store(StoreEmpleadoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $empleado = DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::Empleado,
            ]);

            $user->puestos()->sync($data['puestos']);

            return $user->empleado()->create([
                'telefono' => $data['telefono'] ?? null,
                'fecha_contratacion' => $data['fecha_contratacion'],
                'salario' => $data['salario'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'foto' => $this->guardarFoto($request),
                'estado' => $data['estado'],
                'notas' => $data['notas'] ?? null,
            ]);
        });

        return redirect()
            ->route('empleados.show', $empleado)
            ->with('status', 'Empleado registrado correctamente.');
    }

    public function show(Empleado $empleado): View
    {
        $empleado->load(['user.puestos', 'user.alumnos.user', 'user.pacientes.user']);

        return view('empleados.show', ['empleado' => $empleado]);
    }

    public function edit(Empleado $empleado): View
    {
        $empleado->load('user.puestos');

        return view('empleados.edit', array_merge($this->formData(), ['empleado' => $empleado]));
    }

    public function update(UpdateEmpleadoRequest $request, Empleado $empleado): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $empleado) {
            $empleado->user->update(array_filter([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => filled($data['password'] ?? null) ? Hash::make($data['password']) : null,
            ], fn ($value) => $value !== null));

            $empleado->user->puestos()->sync($data['puestos']);

            $empleado->fill([
                'telefono' => $data['telefono'] ?? null,
                'fecha_contratacion' => $data['fecha_contratacion'],
                'salario' => $data['salario'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'estado' => $data['estado'],
                'notas' => $data['notas'] ?? null,
            ]);

            if ($request->hasFile('foto')) {
                $this->borrarFoto($empleado);
                $empleado->foto = $this->guardarFoto($request);
            }

            $empleado->save();
        });

        return redirect()
            ->route('empleados.show', $empleado)
            ->with('status', 'Empleado actualizado correctamente.');
    }

    public function destroy(Empleado $empleado): RedirectResponse
    {
        DB::transaction(function () use ($empleado) {
            $this->borrarFoto($empleado);
            // Al borrar el user, el perfil y los puestos caen por FK cascade.
            $empleado->user->delete();
        });

        return redirect()
            ->route('empleados.index')
            ->with('status', 'Empleado eliminado correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(): array
    {
        return [
            'estados' => EmpleadoEstado::cases(),
            'puestos' => Puesto::orderBy('nombre')->get(),
        ];
    }

    protected function guardarFoto(Request $request): ?string
    {
        if (! $request->hasFile('foto')) {
            return null;
        }

        return $request->file('foto')->store('empleados', 'public');
    }

    protected function borrarFoto(Empleado $empleado): void
    {
        if ($empleado->foto) {
            Storage::disk('public')->delete($empleado->foto);
        }
    }
}
