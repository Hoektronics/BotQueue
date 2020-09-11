<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class HostResource.
 *
 * @method string getJWT
 * @property int id
 * @property string name
 * @property User owner
 */
class HostResource extends JsonResource
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
            'access_token' => $this->getJWT(),
            'host' => [
                'id' => $this->id,
                'name' => $this->name,
                'owner' => [
                    'id' => $this->owner->id,
                    'username' => $this->owner->username,
                    'link' => url('/api/users', $this->owner->id),
                ],
            ],
        ];
    }

    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
