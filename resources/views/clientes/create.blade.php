<x-app-layout>
    <x-slot name="header">
        <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
            Nuevo cliente
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card p-6 sm:p-8">
                <form method="POST" action="{{ route('clientes.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('clientes._form')

                    <div class="flex items-center justify-end gap-4 mt-8">
                        <a href="{{ route('clientes.index') }}" class="text-sm text-zinc-400 hover:text-white">Cancelar</a>
                        <x-primary-button>Guardar cliente</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
