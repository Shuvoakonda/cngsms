<?php

namespace App\Support;

use Illuminate\Http\Request;

class OffcanvasDeepLinks
{
    /**
     * @param  array<string, mixed>  $config
     * @param  iterable<int, mixed>  $records
     * @param  callable(mixed): array<string, mixed>  $mapper
     * @return array<string, mixed>
     */
    public static function apply(array $config, Request $request, iterable $records, callable $mapper): array
    {
        $recordsById = [];

        foreach ($records as $record) {
            $recordsById[$record->id] = $mapper($record);
        }

        $editId = (int) $request->query('edit');

        return array_merge($config, [
            'deepLinkCreate' => $request->boolean('create'),
            'deepLinkEditId' => $editId > 0 ? $editId : null,
            'recordsById' => $recordsById,
        ]);
    }
}
