<?php

namespace App\Events;

use App\Models\Bot;
use App\Models\Cluster;
use App\Models\Host;
use App\Models\Job;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Collection;

class Event
{
    /**
     * @var Collection
     */
    private $channels;

    protected function ensureNotEmptyChannels()
    {
        if ($this->channels == null) {
            $this->channels = collect();
        }
    }

    protected function addChannel(Channel $channel)
    {
        $this->ensureNotEmptyChannels();

        $this->channels->push($channel);

        return $this;
    }

    protected function channels()
    {
        $this->ensureNotEmptyChannels();

        return $this->channels->map(function ($channel) {
            /* @var Channel $channel */
            return $channel->name;
        })->unique()->values()->sort()->all();
    }

    /**
     * @param $user User|mixed
     * @return Event
     */
    protected function userChannel($user)
    {
        $user_id = $user;
        if ($user instanceof User) {
            $user_id = $user->id;
        }

        if ($user_id !== null) {
            return $this->addChannel(new PrivateChannel('users.'.$user_id));
        }

        return $this;
    }

    /**
     * @param $bot Bot|mixed
     * @return Event
     */
    protected function botChannel($bot)
    {
        $bot_id = $bot;
        if ($bot instanceof Bot) {
            $bot_id = $bot->id;
        }

        if ($bot_id !== null) {
            return $this->addChannel(new PrivateChannel('bots.'.$bot_id));
        }

        return $this;
    }

    /**
     * @param $job Job|mixed
     * @return Event
     */
    protected function jobChannel($job)
    {
        $job_id = $job;
        if ($job instanceof Job) {
            $job_id = $job->id;
        }

        if ($job_id !== null) {
            return $this->addChannel(new PrivateChannel('jobs.'.$job_id));
        }

        return $this;
    }

    /**
     * @param $host Host|mixed
     * @return Event
     */
    protected function hostChannel($host)
    {
        $host_id = $host;
        if ($host instanceof Host) {
            $host_id = $host->id;
        }

        if ($host_id !== null) {
            return $this->addChannel(new PrivateChannel('hosts.'.$host_id));
        }

        return $this;
    }

    /**
     * @param $cluster Cluster|mixed
     * @return Event
     */
    protected function clusterChannel($cluster)
    {
        $cluster_id = $cluster;
        if ($cluster instanceof Cluster) {
            $cluster_id = $cluster->id;
        }

        if ($cluster_id !== null) {
            return $this->addChannel(new PrivateChannel('clusters.'.$cluster_id));
        }

        return $this;
    }
}
