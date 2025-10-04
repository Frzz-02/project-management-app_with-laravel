<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {   
        $long_time = round(Carbon::parse($this->created_at)->diffInDays(Carbon::now())) .' days ago';
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->full_name,
            // 'email' => $this->email,
            'created_at' => $long_time,
            // 'updated_at' => $this->updated_at,
        ];
    }
}
