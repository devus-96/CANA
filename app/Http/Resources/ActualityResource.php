<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActualityResource extends JsonResource
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
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'image_url' => $this->actuality_image,
            'slug' => $this->slug,
            'status' => $this->status,
            'views_count' => $this->views_count,
            'shares_count' => $this->shares_count,
            'likes_count' => $this->likes_count,
            'author' => $this->whenLoaded('author', fn() => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                // autres champs si nécessaire
            ]),
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
