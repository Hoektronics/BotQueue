<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class HostRequestResource.
 *
 * @property int id
 * @property string status
 * @property array expires_at
 * @property User claimer
 */
class HostRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
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
        ];
    }

    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
