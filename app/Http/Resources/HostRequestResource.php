<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class HostRequestResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'expires_at' => $this->expires_at,
            'claimer' => $this->when($this->claimer_id !== null, function() {
                return [
                    'id' => $this->claimer->id,
                    'username' => $this->claimer->username,
                    'link' => url('/api/v2/users', $this->claimer->id),
                ];
            }),
        ];
    }
}
