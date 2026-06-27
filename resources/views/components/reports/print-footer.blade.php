<div class="report-print-footer mt-6 hidden border-t border-black pt-3 text-xs text-neutral-600 print:mt-8 print:block">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <p>Generated on {{ now()->format('d M Y, h:i A') }} by {{ auth()->user()?->name }}</p>
        @if ($summary ?? null)
            <p class="font-bold uppercase tracking-wide text-black">{{ $summary }}</p>
        @endif
    </div>
</div>
