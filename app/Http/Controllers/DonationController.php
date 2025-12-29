<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\Member;
use App\Rules\PhoneNumber;
use App\Models\Donation;
use App\Services\NoCashPayment;

class DonationController extends Controller
{
    public function index (Request $request) {
        try {
            $query = Donation::with('member')
            ->orderBy('created_at', 'desc');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            // Pagination avec paramètre optionnel
            $perPage = $request->get('per_page', 10);
            $donation = $query->paginate($perPage);

            return response()->json([
                'message' => "list of activities",
                'data' => $donation,
                'meta' => [
                    'current_page' => $donation->currentPage(),
                    'total' => $donation->total(),
                    'per_page' => $donation->perPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
             Log::error('Actuality index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve actualities',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show (Donation $donation) {
        try {
            if (!$donation) {
                return response()->json(['statut' => 'error', 'message' => 'Activity not found'], 404);
            }
            $donation->load('member');

            return response()->json([
                'message' => 'Activity details',
                'data'    => $donation
            ], 200);
        } catch (\Exception $e) {
            Log::error('Actuality index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve actualities',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function store (Request $request)
    {
        try {
             /** @var \App\Models\Member $member */
            // Récupérer l'utilisateur authentifié
            $member = auth()->guard('members')->user();
            // Si l'utilisateur est authentifié, utiliser son ID
            $userId = $member ? $member->id : null;

            Validator::make($request->all(), [
                'name'           => 'nullable|string|max:100',
                'phone'   => ['required', 'string', 'unique:', new PhoneNumber],
                'email'   => 'nullable|string|lowercase|email|max:255|unique:'.Member::class,
                'is_anonymous'  => 'nullable|boolean',
                'dedication'   => 'nullable|string',
                'price'          => 'required|numeric|min:0',
                'method' => 'required|in:ORANGE_MONEY,MTN_MOMO',
                'status' => 'nullable|in:PENDING,FAILED,SUCCEED',
            ]);

            $payment = Donation::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->phone,
                "amount" => $request->price,
                "status" => NoCashPayment::STATUS_PENDING,
            ]);

            // Determine the payment method
            $methode = null;
            if(strtolower($request->method) == "orange")
                $methode = NoCashPayment::OM_METHOD;
            else if(strtolower($request->method) == "mtn")
                $methode = NoCashPayment::MOMO_METHOD;

            // initialize transaction
            $result = NoCashPayment::init($payment->id, $payment->phone, $payment->amount, $methode);

            // check status if the process launched successfully
            if($result["status"] == "success"){ // if success
                // update transaction reference
                $payment->update([
                    "transaction_id"         => $result["data"],
                    'status'            => "0",
                ]);

                // return transaction reference
                return response()->json(["data" => $result["data"]], 200);

            }else{ // else
                // return error
                return response()->json(["data" => "0", "status" => $result["status"], "message" => $result["message"]], 404); // Impossible d'initialiser le paiement
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
                Log::error('Actuality index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve actualities',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function update (Donation $donation)
    {
        try {
            if ($donation) {
                $status = NoCashPayment::checkStatus($donation->transaction_id);

                switch($status){
                    case NoCashPayment::STATUS_SUCCESS:
                        $donation->update([ "status" => "2" ]);
                        break;
                    case NoCashPayment::STATUS_TIMEOUT:
                        // update transaction status to timeout
                        $donation->update([ "status" => "2" ]);
                        // return timeout code
                        return response()->json(["message" => "Délai dépassé"], 404);
                        break;
                    case NoCashPayment::STATUS_CANCELED:
                        // update transaction status to timeout
                        $donation->update([ "status" => "2" ]);
                        // return timeout code
                        return response()->json(["message" => "transaction annulee"], 404);
                        break;
                    case NoCashPayment::STATUS_FAILED:
                        // update transaction status to timeout
                        $donation->update([ "status" => "2" ]);
                        // return timeout code
                        return response()->json(["message" => "Echec de la transaction"], 404);
                        break;
                    default:
                        // return pending code
                        return response()->json(["data" => "transaction en cour"], 404); // Transaction en cours
                        break;
                }
            } else {
                return response()->json(["data" => "0"], 404);
            }
        } catch (\Exception $e) {
                Log::error('Actuality index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve actualities',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function refreshTransaction (Donation $donation) {
        try {
            if ($donation->status === '2') {
                $result = NoCashPayment::init($donation->id, $donation->phone, $donation->amount, $donation->method);
                if($result["status"] == "success"){ // if success
                    // update transaction reference
                    $donation->update([
                        "transaction_id"         => $result["data"],
                        'status'            => "0",
                    ]);

                    // return transaction reference
                    return response()->json(["data" => $result["data"]], 200);

                }else{ // else
                    // return error
                    return response()->json(["data" => "0", "status" => $result["status"], "message" => $result["message"]], 404); // Impossible d'initialiser le paiement
                }
            }
        } catch (\Exception $e) {
                Log::error('Actuality index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve actualities',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

}
