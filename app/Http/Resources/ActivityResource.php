<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'image_activity' => $this->image_activity ? asset('storage/image_activity/' . $this->id) : null,
            // On affiche les ressources associées
            'resource_activity' => $this->whenLoaded('resource_activity'),
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
            'author'      => $this->whenLoaded('authorRelation', function() {
                return [
                    'id'   => $this->authorRelation->id,
                    'name' => $this->authorRelation->name,
                ];
            }),
             // Actualités liées
            'news'        => $this->whenLoaded('news', function() {
                return $this->news->map(function($article) {
                    return [
                        'id'         => $article->id,
                        'title'      => $article->title,
                        'excerpt'    => $article->excerpt,
                        'published_at' => $article->published_at,
                    ];
                });
            }),
            // Événements liés
            'events'      => $this->whenLoaded('events', function() {
                return $this->events->map(function($event) {
                    return [
                        'id'         => $event->id,
                        'title'      => $event->title,
                        'start_date' => $event->start_date,
                        'end_date'   => $event->end_date,
                    ];
                });
            }),
        ];


    }
}
