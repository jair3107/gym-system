<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-brand font-semibold text-xl text-zinc-100 leading-tight tracking-wide">
                Puestos
            </h2>
            <a href="{{ route('puestos.create') }}" class="btn-gold">+ Nuevo puesto</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06]">
                        <thead>
                            <tr class="text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Puesto</th>
                                <th class="px-4 py-3">Slug</th>
                                <th class="px-4 py-3">Empleados</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @forelse ($puestos as $puesto)
                                <tr class="hover:bg-white/[0.02] transition">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-zinc-200">{{ $puesto->nombre }}</div>
                                        @if ($puesto->descripcion)
                                            <div class="text-sm text-zinc-500">{{ $puesto->descripcion }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3"><code class="text-xs text-gold-400">{{ $puesto->slug }}</code></td>
                                    <td class="px-4 py-3 text-sm text-zinc-400">{{ $puesto->empleados_count }}</td>
                                    <td class="px-4 py-3 text-right text-sm space-x-3 whitespace-nowrap">
                                        <a href="{{ route('puestos.edit', $puesto) }}" class="text-zinc-400 hover:text-white">Editar</a>
                                        <form method="POST" action="{{ route('puestos.destroy', $puesto) }}" class="inline"
                                              onsubmit="return confirm('¿Eliminar el puesto {{ $puesto->nombre }}? Se quitará de los empleados que lo tengan.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-500">Aún no hay puestos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
