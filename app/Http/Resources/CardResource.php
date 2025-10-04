<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardResource extends JsonResource
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
            'board' => $this->board->board_name,
            'position' => $this->position,
            'priority' => $this->priority,
            'title' => $this->card_title,
            'description' => $this->description,
            'status' => $this->status,
            'created_by' => $this->user->username,
            'created_at' => $this->created_at,
            'actual_hours' => $this->actual_hours,
            'deadline' => $this->due_date,
            'estimate' => $this->estimated_hours,
        ];
    }
}
