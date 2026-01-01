<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MediaResource;

class ActivityResource extends JsonResource
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
            'name'        => $this->name,
            'description' => $this->description,
            'objectif'    => $this->objectif,
            'image_activity' => $this->image_activity,
            // On affiche les ressources associées
            'medias' => $this->whenLoaded('medias', function() {
                return [
                    MediaResource::colection($this->medias)
                ];
            }),
            'events' => $this->whenLoaded('events', function () {
                return [
                    'id'    => $this->id,
                    'name'  => $this->name,
                    'decription'    => $this->description,
                    'event_image'   => $this->event_image,
                    'is_free'   => $this->is_free
                ];
            }),
            // On affiche la catégorie associée
            'category'    => $this->whenLoaded('category', function() {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            // On affiche le responsable associé
            'responsable' => $this->whenLoaded('responsable', function() {
                return [
                    'id'    => $this->responsable->id,
                    'name'  => $this->responsable->name,
                    'email' => $this->responsable->email,
                ];
            }),
            // On affiche l'auteur associé
            'author'      => $this->whenLoaded('author', function() {
                return [
                    'id'   => $this->author->id,
                    'name' => $this->author->name,
                ];
            }),
        ];


    }
}
