<x-app-layout>
    <x-slot name="header">
        <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
            Editar cliente: <span class="text-gold-400">{{ $cliente->user->name }}</span>
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="card p-6 sm:p-8">
                <form method="POST" action="{{ route('clientes.update', $cliente) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('clientes._form')

                    <div class="flex items-center justify-end gap-4 mt-8">
                        <a href="{{ route('clientes.show', $cliente) }}" class="text-sm text-zinc-400 hover:text-white">Cancelar</a>
                        <x-primary-button>Actualizar</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="card p-6 border-red-500/20">
                <h3 class="text-sm font-brand font-semibold text-red-400 uppercase tracking-wider">Zona de peligro</h3>
                <p class="text-sm text-zinc-500 mt-1">Eliminar el cliente borra también su cuenta de acceso. Esta acción no se puede deshacer.</p>
                <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" class="mt-4"
                      onsubmit="return confirm('¿Seguro que quieres eliminar a {{ $cliente->user->name }}?');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Eliminar cliente</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
