<?php

namespace App\Http\Controllers\Reservation;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use App\Models\Event;
use App\Models\Reservation;
use App\Models\EventSubscriptions;
use App\Http\Controllers\Controller;
use App\Rules\PhoneNumber;
use App\Models\Member;
use App\Services\NoCashPayment;

class CreateReservation extends Controller
{
    public function __invoke (Request $request, Event $event)
    {
        $member = auth()->guard('members')->user();
        $userId = $member ? $member->id : null;

        Validator::make($request->all(), [
            'name'           => $userId ? 'nullable|string' : 'required|string|max:100',
            'phone'          => ['required', 'string', new PhoneNumber],
            'email'          => 'required|string|lowercase|email|max:255',
            'ticket_type'    => 'nullable|string',
            'quantity'       => 'required|integer|min:1',
            'price'          => 'required|numeric|min:0',
            'event_date'     => 'required|date|after_or_equal:today',
            'participants'   => 'nullable|array|max:' . $request->input('quantity', 1),
            'participants.*.name' => 'required_with:participants|string|max:100',

            'method' => 'nullable|in:ORANGE_MONEY,MTN_MOMO',
            'status' => 'nullable|in:PENDING,FAILED,SUCCEED',
        ]);

        // Vérifier l'unicité email/phone seulement pour les non-membres
        if (!$userId) {
            $validator->sometimes('email', 'unique:members,email', function ($input) {
                return !empty($input->email);
            });

            $validator->sometimes('phone', 'unique:members,phone', function ($input) {
                return !empty($input->phone);
            });
        }

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        if ($member) {
            $subscription = EventSubscriptions::where('member_id', $menber->id);
            if ($subscription->status === 'pending') {
                response()->json(['message' =>  'Votre demande de réservation est en attente de validation'], 403);
            } else if ($subscription->status === 'rejected') {
                response()->json(['message' =>  'Votre demande de réservation a été rejetée'], 403);
            }
        } else {
            $subscription = EventSubscriptions::where('phone', $request->phone)
                            ->where('email');

            if ($subscription->status === 'approved') {
                response()->json(['message' =>  'Votre demande de réservation est en attente de validation'], 403);
            } else if ($subscription->status === 'rejected') {
                response()->json(['message' =>  'Votre demande de réservation a été rejetée'], 403);
            }
        }

        $alreadyReserved = Reservation::where('event_id', $event->id)
                ->whereDate('event_date', $request->event_date)
                ->whereIn('status', ['pending', 'confirmed'])
                ->sum('quantity');

        $availableSpots = $event->max_capacity - $alreadyReserved;

        if ($event->quantity > $availableSpots) {
            return response()->json([
                'statut' => 'error',
                'message' => 'Places insuffisantes',
                'available' => $availableSpots,
                'requested' => $quantity
            ], 400);
        }

        if (!$event->is_free) {
            if (!$request->method) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Méthode de paiement requise pour les événements payants'
                ], 400);
            } else {

                DB::beginTransaction();

                try {
                    $payment = Payment::create([
                        "amount" => $request->price,
                        "method" => $request->method,
                        'phone' => $request->phone,
                        "status" => NoCashPayment::STATUS_PENDING,
                    ]);

                    $methode = null;
                    if(strtolower($request->method) == "orange")
                        $methode = NoCashPayment::OM_METHOD;
                    else if(strtolower($request->method) == "mtn")
                        $methode = NoCashPayment::MOMO_METHOD;

                    // initialize transaction
                    $result = NoCashPayment::init($payment->id, $payment->phone, $payment->amount, $methode);

                    if($result["status"] == "success"){ // if success
                        // update transaction reference
                        $payment->update([
                            'status'            => "1",
                        ]);

                        $code = Controller::generateReservationCode($member, $request->phone);

                        $reservation = Reservation::create([
                            "user_id" => $userId,
                            "name" => $request->name,
                            "email" => $request->email,
                            "phone" => $request->phone,
                            'code' => $code,
                            'event_id' => $event->id,
                            'ticket_type' => $request->ticket_type,
                            'quantity' => $request->quantity,
                            'price' => $request->price,
                            'event_date' => $request->event_date,
                            'payment_id' => $payment->id,
                            "participants" => $request->participants ? json_encode($request->participants) : null,
                            'status' => 'pending',
                        ]);

                         DB::commit();
                        // return transaction reference
                        return response()->json([
                            "transaction" => $result["data"],
                            'statut'  => 'success',
                            'data'    => $reservation,
                            'message' => 'Réservation en cour de validation'

                        ], 200);

                    }else{ // else
                        DB::rollBack();
                        return response()->json(["data" => "0", "status" => $result["status"], "message" => $result["message"]], 404); // Impossible d'initialiser le paiement
                    }

                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['message' => 'failed', 'error' => $e->getMessage()], 500);
                }
            }
        } else {
             $code = Controller::generateReservationCode($member, $request->phone);

             $reservation = Reservation::create([
                "user_id" => $userId,
                "name" => $request->name,
                "email" => $request->email,
                "phone" => $request->phone,
                'code' => $code,
                'event_id' => $event->id,
                'ticket_type' => $request->ticket_type,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'event_date' => $request->event_date,
                "participants" => $request->participants ? json_encode($request->participants) : null,
                'status' => 'pending',
            ]);

            return response()->json(['statut'  => 'success','data'    => $reservation,'message' => 'Réservation cree'], 200);
        }
    }
}
