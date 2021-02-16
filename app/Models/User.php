<?php

namespace App\Models;

use App\Enums\HostRequestStatusEnum;
use App\Events\UserCreated;
use App\Exceptions\HostAlreadyClaimed;
use App\ModelTraits\CreatesMyCluster;
use App\ModelTraits\FirstUserIsPromotedToAdmin;
use App\ModelTraits\UuidKey;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * App\User.
 *
 * @property string $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $is_admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Bot[] $bots
 * @property-read int|null $bots_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Cluster[] $clusters
 * @property-read int|null $clusters_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\File[] $files
 * @property-read int|null $files_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Host[] $hosts
 * @property-read int|null $hosts_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUsername($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use UuidKey;
    use Notifiable;
    use HasApiTokens;
    use CreatesMyCluster;
    use FirstUserIsPromotedToAdmin;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password',
    ];

    protected $attributes = [
        'is_admin' => false,
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dispatchesEvents = [
        'created' => UserCreated::class,
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function promoteToAdmin()
    {
        $this->is_admin = true;
        $this->save();
    }

    /**
     * @param HostRequest $request
     * @param $name
     * @throws HostAlreadyClaimed
     */
    public function claim(HostRequest $request, $name)
    {
        try {
            HostRequest::query()
                ->whereKey($request->getKey())
                ->where('status', HostRequestStatusEnum::REQUESTED)
                ->whereNull('claimer_id')
                ->update([
                    'claimer_id' => $this->id,
                    'status' => HostRequestStatusEnum::CLAIMED,
                    'hostname' => $name,
                ]);

            $request->refresh();

            if ($request->claimer_id != $this->id) {
                throw new HostAlreadyClaimed('This host request has already been claimed by someone else');
            }
        } catch (\Exception | \Throwable $e) {
            throw new HostAlreadyClaimed('Unexpected exception while trying to grab host request');
        }
    }

    public function bots()
    {
        return $this->hasMany(Bot::class, 'creator_id');
    }

    public function clusters()
    {
        return $this->hasMany(Cluster::class, 'creator_id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'uploader_id');
    }

    public function hosts()
    {
        return $this->hasMany(Host::class, 'owner_id');
    }
}
