@props(['title' => null, 'meta' => null])

@php($company = \App\Models\Company::current())

<div class="report-print-header mb-6 hidden print:mb-6 print:block">
    <table class="w-full border-collapse">
        <tr>
            <td class="w-28 align-top pr-4">
                @if ($company->logoUrl())
                    <x-company-logo size="print" variant="print" />
                @endif
            </td>
            <td class="align-top">
                <p class="text-lg font-bold uppercase tracking-wide text-black">{{ $company->name }}</p>
                @if ($company->address)
                    <p class="mt-1 text-xs text-neutral-600">{{ $company->address }}</p>
                @endif
            </td>
            @if ($title)
                <td class="align-top text-right">
                    <p class="text-sm font-bold uppercase tracking-wide text-black">{{ $title }}</p>
                    @if ($meta)
                        <p class="mt-1 text-xs text-neutral-600">{{ $meta }}</p>
                    @endif
                    <p class="mt-2 text-[10px] text-neutral-500">{{ now()->format('d M Y, h:i A') }}</p>
                </td>
            @endif
        </tr>
    </table>
    <div class="mt-4 border-b-2 border-black"></div>
</div>
