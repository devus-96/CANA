<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'ticket_type' => $this->ticket_type,
            'quantity'  => $this->quantity,
            'price' => $this->price,
            'event_date'  => $this->event_date,
            'member'    => $this->whenLoaded('member', function () {
                return [
                    'id'    => $this->member->id,
                    'name'  => $this->member->name,
                    'email' => $this->member->email,
                ];
            }),
            'event' => $this->whenLoaded('event', function () {
                return [
                    'event_name' => $this->event->name,
                ];
            }),
            'transaction'   => $this->whenLoaded('transaction', function () {
                return [
                    'method'    => $this->transaction->method,
                    'amount'    => $this->transaction->amount,
                    'status'    => $this->transaction->status,
                    'phone'     => $this->transaction->phone
                ];
            })

        ];
    }
}
