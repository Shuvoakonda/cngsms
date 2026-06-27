@props(['paginator' => null, 'title' => null])

<div {{ $attributes->merge(['class' => 'data-table-card']) }}>
    @if ($title)
        <div class="border-b border-slate-200 px-4 py-4 lg:px-5">
            <h2 class="font-semibold text-slate-900">{{ $title }}</h2>
        </div>
    @endif

    <div class="data-table-scroll">
        <table class="data-table">
            {{ $slot }}
        </table>
    </div>

    @if ($paginator && $paginator->hasPages())
        <div class="data-table-footer">
            {{ $paginator->links() }}
        </div>
    @endif
</div>
