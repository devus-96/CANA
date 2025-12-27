<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
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
            'phone' => $this->phone,
            'admin_image' => $this->admin_image ? asset('storage/admin_image' . $this->id) : null,
            'biography' => $this->biography,
            'status' => $this->status,
            'parish' => $this->parish,
            'is_verified' => $this->is_verified,
            'role' => $this->whenLoaded('role', fn() => [
                'id' => $this->role->id,
                'name' => $this->role->name,
            ]),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
