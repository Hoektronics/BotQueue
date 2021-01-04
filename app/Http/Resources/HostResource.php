<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Passport\Token;

/**
 * Class HostResource.
 *
 * @method Token token
 * @property int id
 * @property string name
 * @property User owner
 */
class HostResource extends JsonResource
{
    /**
     * @var string|null
     */
    private $accessToken;

    public function __construct($resource, $accessToken = null)
    {
        parent::__construct($resource);

        $this->accessToken = $accessToken;
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'access_token' => $this->when($this->accessToken !== null, $this->accessToken),
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
