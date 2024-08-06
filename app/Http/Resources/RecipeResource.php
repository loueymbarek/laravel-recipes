<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'ingredients' => $this->ingredients,
            'steps' => $this->steps,
            'preparation_time' => $this->preparation_time,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'name' => $this->user->name,
                ];
            }),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'name' => $this->category->name,
                ];
            }),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
