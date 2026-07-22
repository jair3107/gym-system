<x-app-layout>
    <x-slot name="header">
        <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
            Editar membresía
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="card p-6 sm:p-8">
                <form method="POST" action="{{ route('membresias.update', $membresia) }}">
                    @csrf
                    @method('PUT')
                    @include('membresias._form')

                    <div class="flex items-center justify-end gap-4 mt-8">
                        <a href="{{ route('membresias.index') }}" class="text-sm text-zinc-400 hover:text-white">Cancelar</a>
                        <x-primary-button>Guardar cambios</x-primary-button>
                    </div>
                </form>
            </div>

            <form method="POST" action="{{ route('membresias.destroy', $membresia) }}"
                  onsubmit="return confirm('¿Eliminar esta membresía del catálogo?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">Eliminar membresía</button>
            </form>
        </div>
    </div>
</x-app-layout>
