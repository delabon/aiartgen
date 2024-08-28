<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'artist' => new UserResource($this->whenLoaded('user')),
            'url' => route('image.show', ['art' => $this->resource]),
            'createdAt' => $this->resource->created_at->format('Y-m-d H:i:s'),
            'updatedAt' => $this->resource->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
