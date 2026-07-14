<x-app-layout>
    <x-slot name="header">
        <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
            Editar empleado: <span class="text-gold-400">{{ $empleado->user->name }}</span>
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="card p-6 sm:p-8">
                <form method="POST" action="{{ route('empleados.update', $empleado) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('empleados._form')

                    <div class="flex items-center justify-end gap-4 mt-8">
                        <a href="{{ route('empleados.show', $empleado) }}" class="text-sm text-zinc-400 hover:text-white">Cancelar</a>
                        <x-primary-button>Actualizar</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="card p-6 border-red-500/20">
                <h3 class="text-sm font-brand font-semibold text-red-400 uppercase tracking-wider">Zona de peligro</h3>
                <p class="text-sm text-zinc-500 mt-1">Eliminar el empleado borra también su cuenta de acceso y sus puestos. Esta acción no se puede deshacer.</p>
                <form method="POST" action="{{ route('empleados.destroy', $empleado) }}" class="mt-4"
                      onsubmit="return confirm('¿Seguro que quieres eliminar a {{ $empleado->user->name }}?');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Eliminar empleado</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
