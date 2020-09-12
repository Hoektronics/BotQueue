<?php

namespace App\Models;

use App\Enums\HostRequestStatusEnum;
use App\Events\UserCreated;
use App\Exceptions\HostAlreadyClaimed;
use App\ModelTraits\CreatesMyCluster;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * App\User.
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property bool $is_admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Bot[] $bots
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $clients
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Cluster[] $clusters
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\File[] $files
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Host[] $hosts
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[] $tokens
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUsername($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 */
class User extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;
    use CreatesMyCluster;

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
