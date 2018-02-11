<?php

namespace App\Http\Resources;

use App\Host;
use Illuminate\Http\Resources\Json\Resource;

class HostResource extends Resource
{
    protected $access_token;

    public function __construct(Host $host, $access_token) {
        parent::__construct($host);

        $this->access_token = $access_token;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'access_token' => $this->access_token,
            'host' => [
                'id' => $this->id,
                'name' => $this->name,
                'owner' => [
                    'id' => $this->owner->id,
                    'username' => $this->owner->username,
                    'link' => url('/api/v2/users', $this->owner->id),
                ],
            ]
        ];
    }
}
