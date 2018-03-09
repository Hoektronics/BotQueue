<?php


namespace App\ModelTraits;


use App\Cluster;
use App\User;

trait CreatesMyCluster
{
    public static function bootCreatesMyCluster()
    {
        static::created(function (User $user) {
            Cluster::create([
                'name' => 'My Cluster',
                'creator_id' => $user->id,
            ]);
        });
    }
}