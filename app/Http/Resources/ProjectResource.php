<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_name' => $this->project_name,
            'description' => $this->description,
            'created_by' => $this->created_by,
            'deadline' => $this->deadline,
            'created_at' => $this->created_at,
            'member' => ProjectMemberResource::collection($this->whenLoaded('members')),
            'board_data' => BoardResource::collection($this->whenLoaded('boards')),
        ];
    }
}
