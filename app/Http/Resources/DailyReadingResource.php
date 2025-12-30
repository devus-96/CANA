<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyReadingResource extends JsonResource
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
            'display_date' => $this->display_date,
            'verse' => $this->verse,
            'meditation' => $this->meditation,
            'biblical_reference' => $this->biblical_reference,
            'liturgical_category' => $this->liturgical_category,
            'status' => $this->when($request->user() && $request->user()->isAdmin(), $this->status),
            'audio_url' => $this->audio_url,
            'audio_duration' => $this->audio_duration,
            'author' => $this->whenLoaded('author', function () {
                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                ];
            }),
            'engagement' => [
                'shares_count' => $this->shares_count,
                'views_count' => $this->views_count,
                'likes_count' => $this->likes_count,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
