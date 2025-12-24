<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailingReadingResource extends JsonResource
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
            'date' => $this->display_date->format('d/m/Y'),
            'verse' => $this->verse,
            'meditation' => $this->meditation,
            'reference' => $this->biblical_reference,
            'category' => $this->liturgical_category,
            'author' => $this->when(
                $this->relationLoaded('author') && $this->author,
                $this->author->name
            ),
        ];
    }
}
