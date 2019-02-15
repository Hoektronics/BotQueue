<?php

namespace App;

use App\Enums\HostRequestStatusEnum;
use App\Exceptions\CannotConvertHostRequestToHost;
use App\ModelTraits\HostRequestDynamicAttributes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\HostRequest
 *
 * @property string $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $expires_at
 * @property string|null $local_ip
 * @property string|null $remote_ip
 * @property string|null $hostname
 * @property string $status
 * @property int|null $claimer_id
 * @property string|null $name
 * @property-read \App\User|null $claimer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest whereClaimerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest whereHostname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest whereLocalIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest whereRemoteIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HostRequest query()
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
