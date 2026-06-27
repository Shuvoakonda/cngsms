@props(['messages' => null])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'rounded-lg bg-teal-50 px-4 py-3 text-sm font-medium text-teal-800 ring-1 ring-teal-200']) }}>
        {{ $messages }}
    </div>
@endif
