<?php

namespace App;

use App\Enums\HostRequestStatusEnum;
use App\Exceptions\CannotConvertHostRequestToHost;
use App\ModelTraits\HostRequestDynamicAttributes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
 * @property string|null $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest whereName($value)
 */
class HostRequest extends Model
{
    use HostRequestDynamicAttributes;

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
        'expires_at',
    ];

    public function getStatusAttribute($value)
    {
        if (Carbon::now() > $this->expires_at)
            return HostRequestStatusEnum::EXPIRED;

        return $value;
    }

    public function claimer()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return Host
     * @throws CannotConvertHostRequestToHost
     */
    public function toHost()
    {
        $host = Host::make([
            'local_ip' => $this->local_ip,
            'remote_ip' => $this->remote_ip,
            'name' => $this->name,
            'owner_id' => $this->claimer_id,
        ]);

        try {
            $this->delete();

            $host->save();
        } catch (\Exception $e) {
            throw new CannotConvertHostRequestToHost("Unknown exception causing host to not be created");
        }

        return $host;
    }
}
