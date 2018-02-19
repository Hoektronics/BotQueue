<?php


namespace App\Managers;


use App\Bot;
use App\Cluster;
use App\Enums\JobStatusEnum;
use App\Job;
use Illuminate\Database\Query\JoinClause;

class JobDistributionManager
{
    /**
     * @param $bot Bot
     * @return Job|mixed
     */
    public function nextAvailableJob($bot)
    {
        return $this
            ->first($this->directJobs($bot))
            ->then($this->jobsThroughCluster($bot))
            ->get();
    }

    /**
     * @param $bot Bot
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function directJobs($bot)
    {
        return $bot
            ->morphMany(Job::class, 'worker')
            ->where('status', JobStatusEnum::QUEUED)
            ->toBase();
    }

    /**
     * @param $bot
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function jobsThroughCluster($bot)
    {
        return Job::query()
            ->join('clusters', function ($join) {
                /** @var JoinClause $join */

                $clusterMorphClass = (new Cluster())->getMorphClass();

                $join->on('jobs.worker_type', $clusterMorphClass)
                    ->on('jobs.worker_id', 'clusters.id');
            })
            ->join('bot_cluster', 'bot_cluster.cluster_id', '=', 'clusters.id')
            ->join('bots', 'bots.id', '=', 'bot_cluster.bot_id')
            ->where('bots.id', $bot->id)
            ->where('jobs.status', JobStatusEnum::QUEUED);
    }

    /**
     * @param $jobQuery
     * @return QueryChain
     */
    protected function first($jobQuery)
    {
        return new QueryChain($jobQuery);
    }
}

class QueryChain {
    protected $chain;

    public function __construct($start) {
        $this->chain = collect([$start]);
    }

    public function then($next)
    {
        $this->chain->push($next);

        return $this;
    }

    public function get()
    {
        $result = null;

        $this->chain->each(function ($builder) use (&$result) {
            if($result !== null)
                return;

            $result = $builder->first();
        });

        return $result;
    }
}