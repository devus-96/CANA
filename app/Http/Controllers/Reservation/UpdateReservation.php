<?php

namespace App\Http\Controllers\Reservation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Member;
use App\Models\EventInstance;
use App\Models\Event;

use App\Http\Controllers\Controller;
use App\Services\NoCashPayment;
use App\Services\ReceiptGenerator;

class UpdateReservation extends Controller
{
     public function refreshTransaction (Request $request, Reservation $reservation) {
        try {
            $transaction = Payment::where("transaction_id", "=", $request->transaction_id)->first();

            if ($transaction->status === '2') {
                $result = NoCashPayment::init($transaction->id, $transaction->phone, $transaction->amount, $transaction->method);
                if($result["status"] == "success"){ // if success
                    // update transaction reference
                    $transaction->update([
                        "transaction_id"         => $result["data"],
                        'status'            => "0",
                    ]);

                    $reservation->update(['status' => '0']);

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

    public function update (Request $request, Reservation $reservation)
    {
        // int value
        $transaction = Payment::where("transaction_id", "=", $request->transaction_id)->first();
        //
        if($transaction){
            // si $result n'est pas null, verifier le status avec le nouveau transaction_id
            $status = NoCashPayment::checkStatus($request->transaction_id);
            // agir en fonction du status
            switch($status){
                case NoCashPayment::STATUS_SUCCESS:
                    $event_ocurrence = EventInstance::where('event_id', $reservation->event_id)->first();
                    $event = Event::where('id', $event_ocurrence->event_id);
                    $userData = null;

                    if ($reservation->user_id) {
                        $member = Member::where('id', $reservation->user_id)->first();

                        $userData = [
                            'name' => $member->name,
                            'email' => $member->email,
                            'phone' => $member->phone
                        ];
                    } else {
                        $userData = [
                            'name' => $reservation->name,
                            'email' => $reservation->email,
                            'phone' => $reservation->phone
                        ];
                    }
                    // Generer le reçu
                    $generator = new ReceiptGenerator($reservation, $event_ocurrence, $userData);
                    $transaction->update([ "status" => "1" ]);
                    $reservation->update(["status" => "1"]);
                    // gestion logistique
                    $reserved_spots = $event_ocurrence->increment('reserved_spots');
                    $available_spots = $event->max_capacity - $reserved_spots;
                    $event_ocurrence->available_spots = $available_spots;
                    $event_ocurrence->save();

                    return $generator->download();
                    break;
                case NoCashPayment::STATUS_TIMEOUT:
                    // update transaction status to timeout
                    $transaction->update([ "status" => "2" ]);

                    $reservation->update(["status" => "2"]);

                    // return timeout code
                    return response()->json(["message" => "Délai dépassé"], 404);
                    break;
                case NoCashPayment::STATUS_CANCELED:
                    // update transaction status to timeout
                    $transaction->update([ "status" => "2" ]);

                    $reservation->update(["status" => "2"]);

                    // return timeout code
                    return response()->json(["message" => "transaction annulee"], 404);
                    break;
                case NoCashPayment::STATUS_FAILED:
                    // update transaction status to timeout
                    $transaction->update([ "status" => "2" ]);

                    $reservation->update(["status" => "2"]);

                    // return timeout code
                    return response()->json(["message" => "Echec de la transaction"], 404);
                    break;
                default:
                    // return pending code
                    return response()->json(["data" => "transaction en cour"], 404); // Transaction en cours
                    break;
            }
        }
    }
}
