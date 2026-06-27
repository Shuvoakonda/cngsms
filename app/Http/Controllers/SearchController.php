<?php

namespace App\Http\Controllers;

use App\Services\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request, GlobalSearchService $search): JsonResponse
    {
        return response()->json([
            'results' => $search->search(
                $request->user(),
                (string) $request->query('q', ''),
            )->all(),
        ]);
    }
}
