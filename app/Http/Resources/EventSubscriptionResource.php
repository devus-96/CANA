<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventSubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'status'      => $this->status,
            'event_id'    => $this->event_id,

            // Formatage propre des dates
            'reviewed_at' => $this->reviewed_at ? $this->reviewed_at->format('d/m/Y H:i') : null,
            'created_at'  => $this->created_at->format('d/m/Y H:i'),

            // On affiche le membre SEULEMENT s'il a été chargé avec load() ou with()
            // On peut même utiliser une autre ressource pour le membre !
            'member' => $this->whenLoaded('member', function() {
                return [
                    'id'    => $this->member->id,
                    'name'  => $this->member->name,
                    'email' => $this->member->email,
                    'phone' => $this->member->phone,
                ];
            }),

            // On peut aussi inclure les infos de celui qui a révisé
            'reviewer' => $this->whenLoaded('admin', function() {
                return [
                    'id'   => $this->admin->id,
                    'name' => $this->admin->name,
                ];
            }),
        ];
    }
}
