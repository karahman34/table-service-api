<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewOrderEvent extends Event implements ShouldBroadcast
{
    public function broadcastOn()
    {
    	return new PrivateChannel('orders');
    }

    public function broadcastAs()
    {
    	return 'new';
    }
}
