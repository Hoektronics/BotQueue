<?php

namespace App;

use App\Enums\ClientRequestStatusEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * App\ClientRequest
 *
 * @property int $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $expires_at
 * @property string $local_ip
 * @property string $remote_ip
 * @property string $hostname
 * @property string $status
 * @property int $lookup_code
 * @method static \Illuminate\Database\Query\Builder|\App\ClientRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ClientRequest whereExpiresAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ClientRequest whereHostname($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ClientRequest whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ClientRequest whereLocalIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ClientRequest whereLookupCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ClientRequest whereRemoteIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ClientRequest whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ClientRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClientRequest extends Model
{
    protected $fillable = [
        'local_ip',
        'remote_ip',
        'hostname',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $attributes = [
        'status' => ClientRequestStatusEnum::Requested,
    ];
}
