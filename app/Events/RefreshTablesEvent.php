<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RefreshTablesEvent extends Event implements ShouldBroadcast
{
    public function broadcastOn()
    {
        return new Channel('tables');
    }

    public function broadcastAs()
    {
        return 'refresh';
    }
}
