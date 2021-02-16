<?php

namespace App\Models;

use App\Enums\HostRequestStatusEnum;
use App\Exceptions\HostRequestAlreadyDeleted;
use App\Exceptions\OauthHostClientNotSetup;
use App\Exceptions\OauthHostKeysMissing;
use App\ModelTraits\HostRequestDynamicAttributes;
use App\ModelTraits\UuidKey;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\HostRequest.
 *
 * @property string $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property string|null $local_ip
 * @property string|null $remote_ip
 * @property string|null $hostname
 * @property string $status
 * @property string|null $claimer_id
 * @property-read \App\Models\User|null $claimer
 * @method static Builder|HostRequest couldBeMine()
 * @method static Builder|HostRequest newModelQuery()
 * @method static Builder|HostRequest newQuery()
 * @method static Builder|HostRequest query()
 * @method static Builder|HostRequest whereClaimerId($value)
 * @method static Builder|HostRequest whereCreatedAt($value)
 * @method static Builder|HostRequest whereExpiresAt($value)
 * @method static Builder|HostRequest whereHostname($value)
 * @method static Builder|HostRequest whereId($value)
 * @method static Builder|HostRequest whereLocalIp($value)
 * @method static Builder|HostRequest whereRemoteIp($value)
 * @method static Builder|HostRequest whereStatus($value)
 * @method static Builder|HostRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HostRequest extends Model
{
    use UuidKey;
    use HostRequestDynamicAttributes;

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
        if (Carbon::now() > $this->expires_at) {
            return HostRequestStatusEnum::EXPIRED;
        }

        return $value;
    }

    public function claimer()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCouldBeMine(Builder $query)
    {
        $remote_ip = $_SERVER['REMOTE_ADDR'];

        return $query->where('remote_ip', $remote_ip);
    }

    /**
     * @return Host
     * @throws OauthHostKeysMissing
     * @throws HostRequestAlreadyDeleted
     * @throws Exception
     */
    public function toHost()
    {
        if (! file_exists(passport_private_key_path())) {
            throw new OauthHostKeysMissing('Private key for oauth is missing');
        }

        $host = Host::make([
            'local_ip' => $this->local_ip,
            'remote_ip' => $this->remote_ip,
            'name' => $this->hostname,
            'owner_id' => $this->claimer_id,
        ]);

        $rowsAffected = self::whereId($this->id)
            ->delete();

        if ($rowsAffected == 0) {
            throw new HostRequestAlreadyDeleted("Host request {$this->id} was already deleted and cannot become a host");
        }

        $host->save();

        return $host;
    }
}
