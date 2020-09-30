<?php

namespace Tests\Helpers\Models;

use App\Models\Cluster;

class ClusterBuilder
{
    use Builder;

    private $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = array_merge(
            [
                'id' => $this->get_id(),
            ],
            $attributes);
    }

    /**
     * @return Cluster
     */
    public function create()
    {
        return Cluster::unguarded(function () {
            return Cluster::create($this->attributes);
        });
    }

    private function newWith($newAttributes)
    {
        return new self(
            array_merge(
                $this->attributes,
                $newAttributes
            )
        );
    }

    public function creator(\App\Models\User $user)
    {
        return $this->newWith(['creator_id' => $user->id]);
    }

    public function name(string $name)
    {
        return $this->newWith(['name' => $name]);
    }
}
