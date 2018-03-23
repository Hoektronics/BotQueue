<?php

namespace App\Http\Resources;

use App\Enums\HostRequestStatusEnum;
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
            'data' => [
                'id' => $this->id,
                'status' => $this->status,
                'expires_at' => $this->expires_at,
                'claimer' => $this->whenLoaded('claimer', function () {
                    return [
                        'id' => $this->claimer->id,
                        'username' => $this->claimer->username,
                        'link' => url('/api/users', $this->claimer->id),
                    ];
                }),
            ],
            'links' => [
                'to_host' => $this->when($this->status == HostRequestStatusEnum::CLAIMED, function () {
                    return url("/host/requests/{$this->id}/access");
                })
            ]
        ];
    }
}
