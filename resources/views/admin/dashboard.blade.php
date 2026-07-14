<x-app-layout>
    <x-slot name="header">
        <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
            Panel de Administrador
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Hero --}}
            <div class="card p-6 sm:p-8 relative overflow-hidden">
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-gold-400/10 blur-2xl"></div>
                <p class="text-sm text-gold-500 uppercase tracking-[0.3em] font-brand">AURUM Fitness Club</p>
                <h3 class="mt-2 text-2xl font-brand font-bold text-white">Hola, {{ auth()->user()->name }}</h3>
                <p class="mt-1 text-zinc-400">Este es el estado general de tu gimnasio hoy.</p>
                <a href="{{ route('clientes.create') }}" class="btn-gold mt-5">+ Nuevo cliente</a>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @php($stats = [
                    ['label' => 'Clientes totales', 'value' => $totalClientes, 'accent' => 'text-gold-400'],
                    ['label' => 'Activos', 'value' => $clientesActivos, 'accent' => 'text-green-400'],
                    ['label' => 'Suspendidos', 'value' => $clientesSuspendidos, 'accent' => 'text-red-400'],
                    ['label' => 'Empleados', 'value' => $totalEmpleados, 'accent' => 'text-zinc-200'],
                ])
                @foreach ($stats as $stat)
                    <div class="card p-5">
                        <p class="text-xs uppercase tracking-wider text-zinc-500">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-3xl font-brand font-bold {{ $stat['accent'] }}">{{ $stat['value'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Últimos clientes --}}
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-brand text-lg text-zinc-100">Últimos clientes registrados</h3>
                    <a href="{{ route('clientes.index') }}" class="text-sm text-gold-400 hover:text-gold-300">Ver todos →</a>
                </div>

                <div class="divide-y divide-white/[0.06]">
                    @forelse ($ultimosClientes as $cliente)
                        <a href="{{ route('clientes.show', $cliente) }}" class="flex items-center gap-3 py-3 hover:bg-white/[0.02] px-2 -mx-2 rounded-lg transition">
                            <span class="flex items-center justify-center h-9 w-9 rounded-full bg-gold-400/15 text-gold-300 text-sm font-brand font-semibold">
                                {{ strtoupper(substr($cliente->user->name, 0, 1)) }}
                            </span>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-zinc-200">{{ $cliente->user->name }}</div>
                                <div class="text-xs text-zinc-500">{{ $cliente->user->email }}</div>
                            </div>
                            <span class="badge {{ $cliente->estado->badgeClasses() }}">{{ $cliente->estado->label() }}</span>
                        </a>
                    @empty
                        <p class="py-6 text-center text-sm text-zinc-500">Aún no hay clientes registrados.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
