<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GoogleApiController extends Controller
{
    use ApiResponse;

    /**
     *  Calculates Distance of two states in km
     *  @return float
     */
    public function getDistanceInKm(Request $request): \Illuminate\Http\JsonResponse
    {
        $origin = $request->query('origin');
        $destination = $request->query('destination');
        $key = env('GOOGLE_API_KEY');

        $response = Http::get("https://maps.googleapis.com/maps/api/distancematrix/json", [
            'origins' => $origin,
            'destinations' => $destination,
            'key' => $key,
            'units' => 'metric',
        ]);

        return $this->success($response->json());
    }
}
