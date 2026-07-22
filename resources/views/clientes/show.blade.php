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
                        @php($vigente = $cliente->membresiaVigente())
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <span class="badge {{ $cliente->estado->badgeClasses() }}">
                                {{ $cliente->estado->label() }}
                            </span>
                            @if ($vigente)
                                <span class="badge bg-gold-400/15 text-gold-300 ring-1 ring-gold-400/20">
                                    {{ $vigente->membresia?->nombre ?? 'Membresía' }} · vence {{ $vigente->fecha_fin->format('d/m/Y') }}
                                    ({{ $vigente->dias_restantes }} {{ $vigente->dias_restantes === 1 ? 'día' : 'días' }})
                                </span>
                            @else
                                <span class="badge bg-zinc-500/15 text-zinc-400 ring-1 ring-zinc-500/20">Sin membresía vigente</span>
                            @endif
                        </div>
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

            {{-- Membresía / Suscripción --}}
            @php($suscripcion = $cliente->membresiaVigente())
            @php($suscripcion?->load(['membresia.beneficios.beneficiable', 'canjes.beneficiable']))
            @php($puedeVenta = auth()->user()->puedeGestionarVentas())

            @if ($suscripcion || $puedeVenta)
                <div class="card p-6">
                    <h3 class="section-title mb-4">Membresía / Suscripción</h3>

                    @if ($suscripcion)
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-white/[0.06]">
                            <div>
                                <div class="text-zinc-100 font-medium">{{ $suscripcion->membresia?->nombre ?? 'Membresía' }}</div>
                                <div class="text-sm text-zinc-500">
                                    Vence {{ $suscripcion->fecha_fin->format('d/m/Y') }} · {{ $suscripcion->dias_restantes }} {{ $suscripcion->dias_restantes === 1 ? 'día' : 'días' }} restantes
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($suscripcion->auto_renovacion)
                                    <span class="badge bg-gold-400/15 text-gold-300 ring-1 ring-gold-400/20">Auto-renovación activa</span>
                                @else
                                    <span class="badge bg-zinc-500/15 text-zinc-400 ring-1 ring-zinc-500/20">Sin auto-renovación</span>
                                @endif
                                @if ($puedeVenta && ($suscripcion->membresia?->renovable ?? false))
                                    <form method="POST" action="{{ route('suscripciones.auto-renovacion', $suscripcion) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="auto_renovacion" value="{{ $suscripcion->auto_renovacion ? 0 : 1 }}">
                                        <button type="submit" class="text-sm text-gold-400 hover:text-gold-300">
                                            {{ $suscripcion->auto_renovacion ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <x-input-error :messages="$errors->get('auto_renovacion')" class="mt-2" />

                        @if ($suscripcion->auto_renovacion)
                            <p class="text-xs text-zinc-500 mt-2">
                                Autocobro con:
                                <span class="text-zinc-300">
                                    {{ $suscripcion->tarjeta ? $suscripcion->tarjeta->marca_label.' '.$suscripcion->tarjeta->mascara : ($cliente->tarjetaPrimaria()?->mascara ?? 'tarjeta primaria') }}
                                </span>
                            </p>
                        @endif

                        @php($beneficios = $suscripcion->membresia?->beneficios ?? collect())
                        <div class="mt-4">
                            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-2">Beneficios incluidos</p>
                            @forelse ($beneficios as $b)
                                <div class="flex items-center justify-between py-2 border-b border-white/[0.04] last:border-0">
                                    <div class="text-sm text-zinc-300">
                                        {{ $b->beneficiable?->nombre ?? '— (eliminado) —' }}
                                        <span class="text-xs text-zinc-600">· {{ $b->tipoLabel() }}</span>
                                    </div>
                                    @if ($puedeVenta && $b->beneficiable)
                                        <form method="POST" action="{{ route('canjes.store', $suscripcion) }}">
                                            @csrf
                                            <input type="hidden" name="beneficiable_type" value="{{ $b->beneficiable_type }}">
                                            <input type="hidden" name="beneficiable_id" value="{{ $b->beneficiable_id }}">
                                            <button type="submit" class="text-sm text-gold-400 hover:text-gold-300">Entregar</button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-zinc-500">Este plan no incluye beneficios.</p>
                            @endforelse
                            <x-input-error :messages="$errors->get('beneficio')" class="mt-2" />
                        </div>

                        @if ($suscripcion->canjes->isNotEmpty())
                            <div class="mt-4">
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-2">Entregas registradas</p>
                                @foreach ($suscripcion->canjes()->latest()->take(8)->get() as $canje)
                                    <div class="flex items-center justify-between py-1.5 text-sm">
                                        <span class="text-zinc-400">{{ $canje->descripcion }} @if ($canje->cantidad > 1)<span class="text-zinc-600">×{{ $canje->cantidad }}</span>@endif</span>
                                        <span class="text-xs text-zinc-600">{{ $canje->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <p class="text-sm text-zinc-500">
                            Sin membresía vigente.
                            <a href="{{ route('ventas.create') }}" class="text-gold-400 hover:text-gold-300">Registrar una venta</a>.
                        </p>
                    @endif
                </div>
            @endif

            {{-- Métodos de pago (tarjetas) --}}
            @if ($puedeVenta)
                @php($tarjetas = $cliente->tarjetas()->orderByDesc('primaria')->latest()->get())
                <div class="card p-6">
                    <h3 class="section-title mb-1">Métodos de pago</h3>
                    <p class="text-xs text-zinc-500 mb-4">
                        Se requiere una tarjeta primaria para activar la auto-renovación.
                        Por seguridad solo se guardan los últimos 4 dígitos.
                    </p>

                    <div class="space-y-2 mb-6">
                        @forelse ($tarjetas as $tarjeta)
                            <div class="flex flex-wrap items-center justify-between gap-3 p-3 rounded-lg border border-white/[0.05] bg-ink-800/60">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-zinc-200">{{ $tarjeta->marca_label }} {{ $tarjeta->mascara }}</span>
                                    <span class="text-xs text-zinc-500">{{ $tarjeta->tipo->label() }} · vence {{ $tarjeta->vence }}</span>
                                    @if ($tarjeta->estaVencida())
                                        <span class="badge bg-red-500/15 text-red-400 ring-1 ring-red-500/20">Vencida</span>
                                    @endif
                                    @if ($tarjeta->primaria)
                                        <span class="badge bg-gold-400/15 text-gold-300 ring-1 ring-gold-400/20">Primaria</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    @unless ($tarjeta->primaria)
                                        <form method="POST" action="{{ route('tarjetas.primaria', $tarjeta) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-sm text-gold-400 hover:text-gold-300">Hacer primaria</button>
                                        </form>
                                    @endunless
                                    <form method="POST" action="{{ route('tarjetas.destroy', $tarjeta) }}"
                                          onsubmit="return confirm('¿Eliminar esta tarjeta?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-zinc-500 hover:text-red-400">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">El cliente no tiene tarjetas guardadas.</p>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('tarjetas.store', $cliente) }}" class="border-t border-white/[0.06] pt-4">
                        @csrf
                        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-3">Agregar tarjeta</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <x-input-label for="titular" value="Nombre del titular" />
                                <x-text-input id="titular" name="titular" type="text" class="mt-1 block w-full"
                                              :value="old('titular')" placeholder="Como aparece en la tarjeta" />
                                <x-input-error :messages="$errors->get('titular')" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="numero" value="Número de tarjeta" />
                                <x-text-input id="numero" name="numero" type="text" inputmode="numeric" autocomplete="off"
                                              class="mt-1 block w-full" :value="old('numero')" placeholder="•••• •••• •••• ••••" />
                                <x-input-error :messages="$errors->get('numero')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="tipo" value="Tipo" />
                                <select id="tipo" name="tipo" class="select mt-1 w-full">
                                    @foreach (\App\Enums\TipoTarjeta::cases() as $t)
                                        <option value="{{ $t->value }}" @selected(old('tipo') === $t->value)>{{ $t->label() }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <x-input-label for="mes_exp" value="Mes" />
                                    <x-text-input id="mes_exp" name="mes_exp" type="number" min="1" max="12" class="mt-1 block w-full"
                                                  :value="old('mes_exp')" placeholder="MM" />
                                </div>
                                <div>
                                    <x-input-label for="anio_exp" value="Año" />
                                    <x-text-input id="anio_exp" name="anio_exp" type="number" min="2024" max="2100" class="mt-1 block w-full"
                                                  :value="old('anio_exp')" placeholder="AAAA" />
                                </div>
                                <div>
                                    <x-input-label for="cvv" value="CVV" />
                                    <x-text-input id="cvv" name="cvv" type="text" inputmode="numeric" autocomplete="off" class="mt-1 block w-full"
                                                  placeholder="•••" />
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-error :messages="$errors->get('mes_exp')" class="mt-1" />
                                <x-input-error :messages="$errors->get('anio_exp')" class="mt-1" />
                                <x-input-error :messages="$errors->get('cvv')" class="mt-1" />
                            </div>
                        </div>
                        <div class="flex justify-end mt-4">
                            <x-primary-button>Guardar tarjeta</x-primary-button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Panel de entrenamiento (para el entrenador del alumno) --}}
            @if (!empty($esEntrenadorDelCliente))
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="section-title">Entrenamiento</h3>
                        <a href="{{ route('rutinas.index') }}" class="text-sm text-zinc-400 hover:text-white">Mis rutinas</a>
                    </div>

                    @if ($cliente->estado === \App\Enums\ClienteEstado::Activo)
                        <form method="POST" action="{{ route('seguimientos-entrenamiento.asignar', $cliente) }}"
                              class="flex flex-wrap gap-3 items-end mb-6 pb-6 border-b border-white/[0.06]">
                            @csrf
                            <div class="flex-1 min-w-56">
                                <x-input-label for="rutina_id" value="Asignar una de mis rutinas" />
                                <select id="rutina_id" name="rutina_id" class="select mt-1 block w-full" required>
                                    <option value="">— Selecciona una rutina —</option>
                                    @foreach ($misRutinas as $rutina)
                                        <option value="{{ $rutina->id }}">{{ $rutina->nombre }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('rutina_id')" class="mt-2" />
                                @if ($misRutinas->isEmpty())
                                    <p class="mt-1 text-xs text-zinc-500">No tienes rutinas activas. Crea una en la sección Rutinas.</p>
                                @endif
                            </div>
                            <x-primary-button>Asignar y dar seguimiento</x-primary-button>
                        </form>
                    @else
                        <p class="text-sm text-zinc-500 mb-4">Solo puedes asignar rutinas a alumnos activos.</p>
                    @endif

                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-2">Seguimientos</p>
                    @forelse ($seguimientosEntrenamiento as $seg)
                        <div class="flex items-center justify-between py-2 border-b border-white/[0.04] last:border-0">
                            <div>
                                <div class="text-sm text-zinc-200">{{ $seg->rutina?->nombre ?? '— sin rutina —' }}</div>
                                <div class="text-xs text-zinc-500">Desde {{ $seg->fecha_inicio->format('d/m/Y') }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="badge {{ $seg->estado->badgeClasses() }}">{{ $seg->estado->label() }}</span>
                                <a href="{{ route('seguimientos-entrenamiento.show', $seg) }}" class="text-sm text-gold-400 hover:text-gold-300">Ver</a>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">Aún no hay seguimientos de entrenamiento para este alumno.</p>
                    @endforelse
                </div>
            @endif

            {{-- Panel de nutrición (para el nutriólogo del paciente) --}}
            @if (!empty($esNutriologoDelCliente))
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="section-title">Nutrición</h3>
                        <a href="{{ route('dietas.index') }}" class="text-sm text-zinc-400 hover:text-white">Mis dietas</a>
                    </div>

                    @if ($cliente->estado === \App\Enums\ClienteEstado::Activo)
                        <form method="POST" action="{{ route('seguimientos-nutricion.asignar', $cliente) }}"
                              class="flex flex-wrap gap-3 items-end mb-6 pb-6 border-b border-white/[0.06]">
                            @csrf
                            <div class="flex-1 min-w-56">
                                <x-input-label for="dieta_id" value="Asignar una de mis dietas" />
                                <select id="dieta_id" name="dieta_id" class="select mt-1 block w-full" required>
                                    <option value="">— Selecciona una dieta —</option>
                                    @foreach ($misDietas as $dieta)
                                        <option value="{{ $dieta->id }}">{{ $dieta->nombre }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('dieta_id')" class="mt-2" />
                                @if ($misDietas->isEmpty())
                                    <p class="mt-1 text-xs text-zinc-500">No tienes dietas activas. Crea una en la sección Dietas.</p>
                                @endif
                            </div>
                            <x-primary-button>Asignar y dar seguimiento</x-primary-button>
                        </form>
                    @else
                        <p class="text-sm text-zinc-500 mb-4">Solo puedes asignar dietas a pacientes activos.</p>
                    @endif

                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-2">Seguimientos</p>
                    @forelse ($seguimientosNutricion as $seg)
                        <div class="flex items-center justify-between py-2 border-b border-white/[0.04] last:border-0">
                            <div>
                                <div class="text-sm text-zinc-200">{{ $seg->dieta?->nombre ?? '— sin dieta —' }}</div>
                                <div class="text-xs text-zinc-500">Desde {{ $seg->fecha_inicio->format('d/m/Y') }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="badge {{ $seg->estado->badgeClasses() }}">{{ $seg->estado->label() }}</span>
                                <a href="{{ route('seguimientos-nutricion.show', $seg) }}" class="text-sm text-gold-400 hover:text-gold-300">Ver</a>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">Aún no hay seguimientos de nutrición para este paciente.</p>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
