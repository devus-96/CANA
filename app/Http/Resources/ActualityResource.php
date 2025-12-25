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
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'slug' => $this->slug,
            'status' => $this->status,
            'views_count' => $this->when($request->user()?->isAdmin(), $this->views_count),
            'shares_count' => $this->shares_count,
            'likes_count' => $this->likes_count,
            'author' => $this->whenLoaded('admin', fn() => [
                'id' => $this->admin->id,
                'name' => $this->admin->name,
                // autres champs si nécessaire
            ]),
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
