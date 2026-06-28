<div class="report-print-footer">
    <div class="report-print-footer-inner">
        <p class="report-print-footer-meta">Generated on {{ now()->format('d M Y, h:i A') }} by {{ auth()->user()?->name }}</p>
        @if ($summary ?? null)
            <p class="report-print-footer-summary">{{ $summary }}</p>
        @endif
    </div>
</div>
