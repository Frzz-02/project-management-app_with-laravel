<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class UserDetailResource extends JsonResource
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
            'name' => $this->full_name,
            'username' => $this->username,
            'status' => $this->current_task_status,
            'role' => $this->role,
            'created_at' => Carbon::parse($this->created_at)->diffForHumans(),
            'updated_at' => $this->updated_at,
        ];
    }
}
