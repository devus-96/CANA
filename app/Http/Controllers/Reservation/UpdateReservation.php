<?php

namespace App\Http\Controllers\Reservation;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Member;
use App\Http\Controllers\Controller;
use App\Services\NoCashPayment;
use App\Services\ReceiptGenerator;

class UpdateReservation extends Controller
{
    public function __invoke (Request $request, Reservation $reservation)
    {
        // int value
        $result = null;
        $transaction = Payment::where("transaction_id", "=", $request->transaction_id)->first();
        // si la reservation a echoue, initialiser a nouveau la transaction
        if ($reservation->status === '2' && $transaction->status === '2') {
            $result = NoCashPayment::init($transaction->id, $transaction->phone, $transaction->amount, $transaction->method);
        }
        if($transaction){
            // si $result n'est pas null, verifier le status avec le nouveau transaction_id
            $status = $result ? NoCashPayment::checkStatus($result['data']) : NoCashPayment::checkStatus($request->transaction_id);
            // mettre a jour le transaction_id si necessaire
            if ($result) {
                $transaction->update(['transaction_id'], $result['data']);
            }
            // agir en fonction du status
            switch($status){
                case NoCashPayment::STATUS_SUCCESS:
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
                    $generator = new ReceiptGenerator($reservation,  $userData);
                    $transaction->update([ "status" => "1" ]);
                    $reservation->update(["status" => "1"]);

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
