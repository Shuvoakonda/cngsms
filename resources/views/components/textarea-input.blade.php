@props(['disabled' => false])

<textarea @disabled($disabled) {{ $attributes->merge(['class' => 'form-control min-h-[5rem] resize-y']) }}>{{ $slot }}</textarea>
