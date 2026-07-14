@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-gold-400']) }}>
        {{ $status }}
    </div>
@endif
