<?php


namespace Tests;

use App\Cluster;

trait HasCluster
{
    /** @var Cluster $cluster */
    protected $cluster;

    public function createTestCluster()
    {
        $this->cluster = factory(Cluster::class)->create([
            'creator_id' => $this->user->id,
        ]);
    }
}
