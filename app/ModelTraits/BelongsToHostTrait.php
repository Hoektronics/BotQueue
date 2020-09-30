<?php

namespace App\ModelTraits;

use App\Models\Bot;
use App\Events\BotAssignedToHost;
use App\Events\BotRemovedFromHost;
use App\Models\Host;

trait BelongsToHostTrait
{
    public function host()
    {
        return $this->belongsTo(Host::class);
    }

    public function assignTo($host)
    {
        /** @var Bot $bot */
        $bot = $this;

        if ($this->host_id !== null) {
            $oldHost = $this->host;

            event(new BotRemovedFromHost($bot, $oldHost));
        }

        $this->host_id = $host->id;

        $this->save();

        event(new BotAssignedToHost($bot, $host));
    }
}
