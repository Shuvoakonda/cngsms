@props([
    'size' => 'md',
    'variant' => 'default',
    'company' => null,
])

@php
    use App\Models\Company;

    $company = $company ?? Company::current();

    $sizes = [
        'sm' => ['box' => 'h-8 w-8', 'img' => 'max-h-7 max-w-7'],
        'md' => ['box' => 'h-10 w-10', 'img' => 'max-h-9 max-w-9'],
        'lg' => ['box' => 'h-12 w-12', 'img' => 'max-h-11 max-w-11'],
        'xl' => ['box' => 'h-20 w-20', 'img' => 'max-h-[4.5rem] max-w-[4.5rem]'],
        'print' => ['box' => 'h-14 w-[100px]', 'img' => 'max-h-14 max-w-[100px]'],
    ];

    $sizeConfig = $sizes[$size] ?? $sizes['md'];
    $hasLogo = $company?->logoUrl();
@endphp

@if ($hasLogo)
    @if ($variant === 'sidebar')
        <div {{ $attributes->merge(['class' => "flex {$sizeConfig['box']} shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white p-0.5 ring-1 ring-white/10"]) }}>
            <img src="{{ $company->logoUrl() }}" alt="{{ $company->name }}" class="{{ $sizeConfig['img'] }} object-contain">
        </div>
    @elseif ($variant === 'print')
        <img {{ $attributes->merge(['class' => "{$sizeConfig['img']} object-contain grayscale"]) }} src="{{ $company->logoUrl() }}" alt="{{ $company->name }} logo">
    @else
        <div {{ $attributes->merge(['class' => "flex {$sizeConfig['box']} shrink-0 items-center justify-center overflow-hidden rounded-lg"]) }}>
            <img src="{{ $company->logoUrl() }}" alt="{{ $company->name }}" class="{{ $sizeConfig['img'] }} object-contain">
        </div>
    @endif
@else
    <x-application-logo {{ $attributes->merge(['class' => match ($size) {
        'sm' => 'h-8 w-auto shrink-0',
        'md' => 'h-10 w-auto shrink-0',
        'lg' => 'h-12 w-auto shrink-0',
        'xl' => 'h-20 w-auto shrink-0',
        'print' => 'h-14 w-auto shrink-0',
        default => 'h-10 w-auto shrink-0',
    }]) }} />
@endif
