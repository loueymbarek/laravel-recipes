<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RecipeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => RecipeResource::collection($this->collection),
            'meta' => [
                'total' => $this->collection->count(),
                'pagination' => [
                   
                ],
            ],
        ];
    }
}