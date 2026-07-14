<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Ficha del empleado
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('empleados.index') }}" class="text-sm text-zinc-400 hover:text-white">Volver</a>
                <a href="{{ route('empleados.edit', $empleado) }}" class="btn-gold">Editar</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Cabecera --}}
            <div class="card p-6">
                <div class="flex items-center gap-4">
                    @if ($empleado->foto)
                        <img src="{{ Storage::url($empleado->foto) }}" alt="Foto" class="h-20 w-20 rounded-full object-cover ring-2 ring-gold-400/30">
                    @else
                        <div class="h-20 w-20 rounded-full bg-gold-400/15 flex items-center justify-center text-2xl font-brand font-semibold text-gold-300">
                            {{ strtoupper(substr($empleado->user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1">
                        <h3 class="text-lg font-brand font-semibold text-white">{{ $empleado->user->name }}</h3>
                        <p class="text-sm text-zinc-500">{{ $empleado->user->email }}</p>
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach ($empleado->user->puestos as $puesto)
                                <span class="badge bg-gold-400/10 text-gold-300 ring-1 ring-gold-400/20">{{ $puesto->nombre }}</span>
                            @endforeach
                            <span class="badge {{ $empleado->estado->badgeClasses() }}">{{ $empleado->estado->label() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Datos --}}
            <div class="card p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-zinc-500">Teléfono</dt>
                        <dd class="text-zinc-200">{{ $empleado->telefono ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Fecha de contratación</dt>
                        <dd class="text-zinc-200">{{ $empleado->fecha_contratacion?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Salario</dt>
                        <dd class="text-zinc-200">{{ $empleado->salario !== null ? '$'.number_format($empleado->salario, 2) : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Dirección</dt>
                        <dd class="text-zinc-200">{{ $empleado->direccion ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-zinc-500">Notas</dt>
                        <dd class="text-zinc-200 whitespace-pre-line">{{ $empleado->notas ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Clientes asignados --}}
            @if ($empleado->user->hasPuesto(\App\Models\Puesto::ENTRENADOR))
                <div class="card p-6">
                    <h3 class="font-brand text-lg text-zinc-100 mb-4">Alumnos (como entrenador)</h3>
                    @include('empleados._clientes-asignados', ['clientes' => $empleado->user->alumnos])
                </div>
            @endif

            @if ($empleado->user->hasPuesto(\App\Models\Puesto::NUTRIOLOGO))
                <div class="card p-6">
                    <h3 class="font-brand text-lg text-zinc-100 mb-4">Pacientes (como nutriólogo)</h3>
                    @include('empleados._clientes-asignados', ['clientes' => $empleado->user->pacientes])
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
