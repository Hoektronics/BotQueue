<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class HostResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'access_token' => (string)$this->getAccessToken()->convertToJWT(passport_private_key()),
            'host' => [
                'id' => $this->id,
                'name' => $this->name,
                'owner' => [
                    'id' => $this->owner->id,
                    'username' => $this->owner->username,
                    'link' => url('/api/users', $this->owner->id),
                ],
            ]
        ];
    }
}
