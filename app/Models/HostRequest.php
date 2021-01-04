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
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $expires_at
 * @property string|null $local_ip
 * @property string|null $remote_ip
 * @property string|null $hostname
 * @property string $status
 * @property string|null $claimer_id
 * @property-read User|null $claimer
 * @method static \Illuminate\Database\Eloquent\Builder|HostRequest whereClaimerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HostRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HostRequest whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HostRequest whereHostname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HostRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HostRequest whereLocalIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HostRequest whereRemoteIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HostRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HostRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|HostRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HostRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HostRequest query()
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
     * @throws OauthHostClientNotSetup See HostAuthTrait::client
     * @throws Exception
     */
    public function toHost()
    {
        $host = Host::make([
            'local_ip' => $this->local_ip,
            'remote_ip' => $this->remote_ip,
            'name' => $this->hostname,
            'owner_id' => $this->claimer_id,
        ]);

        if (! file_exists(passport_private_key_path())) {
            throw new OauthHostKeysMissing('Private key for oauth is missing');
        }

        $rowsAffected = self::whereId($this->id)
            ->delete();

        if ($rowsAffected == 0) {
            throw new HostRequestAlreadyDeleted("Host request {$this->id} was already deleted and cannot become a host");
        }

        $host->save();

        return $host;
    }
}
