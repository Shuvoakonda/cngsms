@props(['title' => null, 'meta' => null, 'summary' => null])

<table class="report-print-layout w-full border-collapse">
    <thead>
        <tr>
            <td class="report-print-layout-slot">
                <x-reports.print-header :title="$title" :meta="$meta" />
            </td>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td class="report-print-layout-slot">
                <x-reports.print-footer :summary="$summary" />
            </td>
        </tr>
    </tfoot>
    <tbody>
        <tr>
            <td class="report-print-layout-slot">
                {{ $slot }}
            </td>
        </tr>
    </tbody>
</table>
