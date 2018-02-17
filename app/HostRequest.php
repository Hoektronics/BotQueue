<?php

namespace App;

use App\Enums\HostRequestStatusEnum;
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
 * @method static \Illuminate\Database\Query\Builder|\App\HostRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HostRequest whereExpiresAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HostRequest whereHostname($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HostRequest whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HostRequest whereLocalIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HostRequest whereLookupCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HostRequest whereRemoteIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HostRequest whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HostRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $claimer_id
 * @property-read \App\User|null $claimer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest whereClaimerId($value)
 */
class HostRequest extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

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
        'status' => HostRequestStatusEnum::Requested,
    ];

    public function claimer()
    {
        return $this->belongsTo(User::class);
    }
}
