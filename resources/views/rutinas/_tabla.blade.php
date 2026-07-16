{{-- Rutina por día: enfoque muscular + ejercicios (nombre / series / apuntes). --}}
@php
    $diasConPlan = collect(\App\Models\Rutina::DIAS)
        ->filter(fn ($label, $slug) => $rutina->tieneDia($slug));
@endphp

@if ($diasConPlan->isEmpty())
    <p class="text-sm text-zinc-500">Esta rutina aún no tiene ejercicios cargados.</p>
@else
    <div class="space-y-5">
        @foreach ($diasConPlan as $slug => $label)
            @php($ejercicios = $rutina->ejercicios($slug))
            <div class="overflow-x-auto rounded-lg border border-white/[0.06]">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-ink-800">
                            <th class="px-4 py-2.5 text-left border-b border-white/10">
                                <span class="font-brand font-semibold text-gold-400 uppercase tracking-wider">{{ $label }}</span>
                                @if ($rutina->enfoque($slug))
                                    <span class="text-zinc-300 font-medium">· {{ $rutina->enfoque($slug) }}</span>
                                @endif
                            </th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider border-b border-white/10 w-32">Series</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider border-b border-white/10 w-56">Apuntes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse ($ejercicios as $ej)
                            <tr class="hover:bg-white/[0.02] transition">
                                <td class="px-4 py-2 text-zinc-200">{{ $ej['nombre'] }}</td>
                                <td class="px-4 py-2 text-zinc-300 whitespace-nowrap">{{ $ej['series'] ?? '' ?: '—' }}</td>
                                <td class="px-4 py-2 text-zinc-400">{{ $ej['apuntes'] ?? '' ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-sm text-zinc-500">Sin ejercicios en este día.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
@endif
