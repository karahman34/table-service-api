<?php

namespace App\Events;

use App\Table;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TransactionCompleteEvent extends Event implements ShouldBroadcast
{
    /**
     * Table object.
     *
     * @var Table
     */
    public $table;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("table.{$this->table->number}");
    }

    public function broadcastAs()
    {
        return 'complete';
    }
}
