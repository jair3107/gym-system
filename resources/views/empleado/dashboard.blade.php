<x-app-layout>
    <x-slot name="header">
        <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
            Panel de Empleado
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="card p-6 sm:p-8 relative overflow-hidden">
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-gold-400/10 blur-2xl"></div>
                <p class="text-sm text-gold-500 uppercase tracking-[0.3em] font-brand">AURUM Fitness Club</p>
                <h3 class="mt-2 text-2xl font-brand font-bold text-white">Hola, {{ auth()->user()->name }}</h3>
                @if ($rol = auth()->user()->rolParaMostrar())
                    <p class="mt-1 text-zinc-400">Rol: {{ $rol }}</p>
                @endif
                <a href="{{ route('clientes.index') }}" class="btn-gold mt-5">{{ auth()->user()->etiquetaClientes() }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
