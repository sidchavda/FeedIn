<?php

namespace App\Http\Controllers;

use App\Models\State;
use Illuminate\Http\JsonResponse;

class StateController extends Controller
{
    public function getStatesByCountry($countryId): JsonResponse
    {
        $states = State::where('country_id', $countryId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'state_code']);
        
        return response()->json($states);
    }
}
