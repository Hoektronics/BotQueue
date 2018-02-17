<?php

namespace App\Oauth;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Client;

/**
 * App\Oauth\OauthHostClient
 *
 * @property int $id
 * @property int $client_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Laravel\Passport\Client $client
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Oauth\OauthHostClient whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Oauth\OauthHostClient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Oauth\OauthHostClient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Oauth\OauthHostClient whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OauthHostClient extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'oauth_host_clients';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get all of the authentication codes for the client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
