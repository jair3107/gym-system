{{-- Tabla semanal de la dieta: filas = tiempos de comida, columnas = días. --}}
<div class="overflow-x-auto">
    <table class="min-w-full border-separate border-spacing-0 text-sm">
        <thead>
            <tr>
                <th class="sticky left-0 z-10 bg-ink-800 px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider border-b border-white/10">
                    Comida
                </th>
                @foreach (\App\Models\Dieta::DIAS as $dia => $label)
                    <th class="px-4 py-3 text-left text-xs font-medium text-gold-400 uppercase tracking-wider border-b border-white/10 min-w-40">
                        {{ $label }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach (\App\Models\Dieta::TIEMPOS as $tiempo => $tiempoLabel)
                <tr class="align-top">
                    <th class="sticky left-0 z-10 bg-ink-800 px-4 py-3 text-left border-b border-white/[0.06] whitespace-nowrap">
                        <div class="font-medium text-zinc-200">{{ $tiempoLabel }}</div>
                        @if ($dieta->horario($tiempo))
                            <div class="text-xs text-zinc-500 mt-0.5">🕐 {{ $dieta->horario($tiempo) }}</div>
                        @endif
                    </th>
                    @foreach (\App\Models\Dieta::DIAS as $dia => $diaLabel)
                        <td class="px-4 py-3 border-b border-white/[0.06] text-zinc-300 whitespace-pre-line">
                            {{ $dieta->contenido($dia, $tiempo) ?? '—' }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
