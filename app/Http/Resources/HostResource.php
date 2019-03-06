<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;

/**
 * Class HostResource
 * @package App\Http\Resources
 *
 * @method string getJWT
 * @property int id
 * @property string name
 * @property User owner
 */
class HostResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'access_token' => $this->getJWT(),
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
