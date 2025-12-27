<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class NoCashPayment extends ServiceProvider
{

    public const STATUS_PENDING         = 0;
    public const STATUS_SUCCESS         = 1;
    public const STATUS_FAILED          = 2;
    public const STATUS_CANCELED        = 3;
    public const STATUS_TIMEOUT         = 4;

    public const OM_METHOD              = "ORANGE_MONEY";
    public const MOMO_METHOD            = "MTN_MOMO";

    /**
     * used to initialize payment process through NoCash API
     * @param {Integer} $phoneNumber the phone number of the user
     * @param {Integer} $amount the amount of the transaction
     * @param {String} $method te payment service to use for the transaction (OM or MOMO)
     *
     * @return array which contains what the API returns :
     * ["status", "data"] for success process, ["status", "message"] otherwise
     */
    public static function init($orderID, $phoneNumber, $amount, $method){
        // initialize the process
        $response = Http::post("https://api.nokash.app/lapas-on-trans/trans/api-payin-request/407", [
            'i_space_key' => config('app.nokash_i_space_key'),
            'app_space_key' => config('app.nokash_app_space_key'),
            'amount' => "".$amount."",
            'order_id' => $orderID,
            'country' => 'CM',
            "payment_type"=> "CM_MOBILEMONEY",
            'payment_method' => $method,
            "user_data" => [
                "user_phone" => "237".$phoneNumber,
            ]
        ]);

        // Handle the responseP
        if ($response->successful()) {
            // Update the financial transaction id.
            $data = $response->json();
            switch($data["status"]){
                case "REQUEST_OK":
                    return [
                        "status" => 'success',
                        "data" => $data['data']['id'],
                    ];
                    break;
                case "REQUEST_BAD_INFOS":
                    return [
                        'status' => 'error',
                        'message' => $data['message'],
                    ];
                    break;
                default:
                    return [
                        'status' => 'error',
                        'message' => $data['message'],
                    ];
            }

        } else {
            return [
                'status' => 'error',
                'message' => $response->body()
            ];
        }
    }

    /**
     * used to get transaction status from NoCash
     * @param {String} $transaction_id the ID of transaction provided by NoCash
     *
     * @return status code of the transaction from NoCash
     */
    public static function checkStatus($transaction_id){
        $response = Http::post("https://api.nokash.app/lapas-on-trans/trans/310/status-request?transaction_id=".$transaction_id);

        if($response->successful()){
            $status = $response->json()["data"]["status"];
            switch($status){
                case "SUCCESS":
                    return self::STATUS_SUCCESS;
                    break;
                case "PENDING":
                    return self::STATUS_PENDING;
                    break;
                case "FAILED":
                    return self::STATUS_FAILED;
                    break;
                case "CANCELED":
                    return self::STATUS_CANCELED;
                    break;
                case "TIMEOUT":
                    return self::STATUS_TIMEOUT;
                    break;
                default:
                    break;
            }

        }else{
            return self::STATUS_FAILED;
        }
    }

}
