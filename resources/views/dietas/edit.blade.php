<x-app-layout>
    <x-slot name="header">
        <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
            Editar dieta: <span class="text-gold-400">{{ $dieta->nombre }}</span>
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="card p-6 sm:p-8">
                <form method="POST" action="{{ route('dietas.update', $dieta) }}">
                    @csrf
                    @method('PUT')
                    @include('dietas._form')

                    <div class="flex items-center justify-end gap-4 mt-8">
                        <a href="{{ route('dietas.show', $dieta) }}" class="text-sm text-zinc-400 hover:text-white">Cancelar</a>
                        <x-primary-button>Actualizar</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="card p-6 border-red-500/20">
                <h3 class="text-sm font-brand font-semibold text-red-400 uppercase tracking-wider">Zona de peligro</h3>
                <p class="text-sm text-zinc-500 mt-1">Eliminar la dieta no se puede deshacer.</p>
                <form method="POST" action="{{ route('dietas.destroy', $dieta) }}" class="mt-4"
                      onsubmit="return confirm('¿Seguro que quieres eliminar la dieta {{ $dieta->nombre }}?');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Eliminar dieta</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
