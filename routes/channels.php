<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('table.{number}', function ($user, $number) {
    return true;
});

Broadcast::channel('orders', function ($user) {
    return true;
});
