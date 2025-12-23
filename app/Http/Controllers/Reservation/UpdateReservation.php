<?php

namespace App\Http\Controllers\Reservation;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Services\NoCashPayment;

class UpdateReservation extends Controller
{
    public function __invoke (Resquest $request)
    {
        $transaction = Payment::where("reference", "=", $request->payment_id)->first();

        if($transaction){
            $status = NoCashPayment::checkStatus($request->transaction_id);

            switch($status){
                case NoCashPaymentProvider::STATUS_SUCCESS:
                    break;
                case NoCashPaymentProvider::STATUS_TIMEOUT:
                    break;
                case NoCashPaymentProvider::STATUS_CANCELED:
                    break;
                case NoCashPaymentProvider::STATUS_FAILED:
                    break;
                default:
                    break;
            }
        }
    }
}
