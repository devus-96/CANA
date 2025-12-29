<?php

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Reservation;
use App\Http\Resources\ReservationResource;

class GetReservation extends Controller
{
    public function index (Request $request) {
        $query = Reservation::with('transaction')
        ->orderBy('created_at', 'desc');
        // filtre
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
         // Pagination avec paramètre optionnel
        $perPage = $request->get('per_page', 10);
        $reservation = $query->paginate($perPage);

        return response()->json([
            'message' => "list of activities",
            'data' => new ReservationResource($reservation),
            'meta' => [
                'current_page' => $reservation->currentPage(),
                'total' => $reservation->total(),
                'per_page' => $reservation->perPage(),
            ]
        ], 200);
    }

    public function show (Reservation $reservation) {
        try {
            if (!$reservation) {
                return response()->json([]);
            }
            $reservation->load('transaction', 'member', 'event');

            return response()->json([
                'message' => 'Activity details',
                'data'    => new ReservationResource($reservation)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Actuality index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve actualities',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
