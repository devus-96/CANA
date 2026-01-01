<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'description' => $this->description,
            'objectif' => $this->objectif,
            'type' => $this->type,
            'max_capacity' => $this->max_capacity,
            'price' => $this->price,
            'event_image'  => $this->event_image,
            'status' => $this->status,
            'is_free' => $this->is_free,
        ];
    }
}
