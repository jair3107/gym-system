<x-app-layout>
    @php
        $u = auth()->user();
        $puedeGestionar = $u->puedeGestionarClientes();
        $puedeAutoasignar = ! $puedeGestionar && (
            ($u->hasPuesto(\App\Models\Puesto::ENTRENADOR) && is_null($cliente->entrenador_id))
            || ($u->hasPuesto(\App\Models\Puesto::NUTRIOLOGO) && is_null($cliente->nutriologo_id))
        );
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Ficha del cliente
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('clientes.index') }}" class="text-sm text-zinc-400 hover:text-white">Volver</a>
                @if ($puedeAutoasignar)
                    <form method="POST" action="{{ route('clientes.autoasignar', $cliente) }}">
                        @csrf
                        <button type="submit" class="btn-gold">Asignármelo</button>
                    </form>
                @endif
                @if ($puedeGestionar)
                    <a href="{{ route('clientes.edit', $cliente) }}" class="btn-gold">
                        Editar
                    </a>
                @endif
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

            <div class="card p-6">
                <div class="flex items-center gap-4">
                    @if ($cliente->foto)
                        <img src="{{ Storage::url($cliente->foto) }}" alt="Foto" class="h-20 w-20 rounded-full object-cover ring-2 ring-gold-400/30">
                    @else
                        <div class="h-20 w-20 rounded-full bg-gold-400/15 flex items-center justify-center text-2xl font-brand font-semibold text-gold-300">
                            {{ strtoupper(substr($cliente->user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h3 class="text-lg font-brand font-semibold text-white">{{ $cliente->user->name }}</h3>
                        <p class="text-sm text-zinc-500">{{ $cliente->user->email }}</p>
                        <span class="badge mt-2 {{ $cliente->estado->badgeClasses() }}">
                            {{ $cliente->estado->label() }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-zinc-500">Teléfono</dt>
                        <dd class="text-zinc-200">{{ $cliente->telefono ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Fecha de nacimiento</dt>
                        <dd class="text-zinc-200">
                            {{ $cliente->fecha_nacimiento?->format('d/m/Y') ?? '—' }}
                            @if ($cliente->edad !== null) <span class="text-zinc-500">({{ $cliente->edad }} años)</span> @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Género</dt>
                        <dd class="text-zinc-200">{{ $cliente->genero?->label() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Dirección</dt>
                        <dd class="text-zinc-200">{{ $cliente->direccion ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Fecha de ingreso</dt>
                        <dd class="text-zinc-200">{{ $cliente->fecha_ingreso?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Contacto de emergencia</dt>
                        <dd class="text-zinc-200">
                            {{ $cliente->contacto_emergencia_nombre ?? '—' }}
                            @if ($cliente->contacto_emergencia_telefono) · {{ $cliente->contacto_emergencia_telefono }} @endif
                        </dd>
                    </div>
                    @php($puedeVerEmpleados = auth()->user()->puedeGestionarEmpleados())
                    <div>
                        <dt class="text-zinc-500">Entrenador</dt>
                        <dd class="text-zinc-200">
                            @if ($cliente->entrenador && $puedeVerEmpleados && $cliente->entrenador->empleado)
                                <a href="{{ route('empleados.show', $cliente->entrenador->empleado) }}" class="text-gold-400 hover:text-gold-300">{{ $cliente->entrenador->name }}</a>
                            @else
                                {{ $cliente->entrenador?->name ?? '—' }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Nutriólogo</dt>
                        <dd class="text-zinc-200">
                            @if ($cliente->nutriologo && $puedeVerEmpleados && $cliente->nutriologo->empleado)
                                <a href="{{ route('empleados.show', $cliente->nutriologo->empleado) }}" class="text-gold-400 hover:text-gold-300">{{ $cliente->nutriologo->name }}</a>
                            @else
                                {{ $cliente->nutriologo?->name ?? '—' }}
                            @endif
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-zinc-500">Notas</dt>
                        <dd class="text-zinc-200 whitespace-pre-line">{{ $cliente->notas ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
