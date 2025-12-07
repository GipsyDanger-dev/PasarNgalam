<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $driverId;
    public $latitude;
    public $longitude;

    public function __construct($driverId, $latitude, $longitude)
    {
        $this->driverId = $driverId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function broadcastOn()
    {
        return new Channel('driver.' . $this->driverId);
    }

    public function broadcastAs()
    {
        return 'driver.location.updated';
    }

    public function broadcastWith()
    {
        return [
            'driver_id' => $this->driverId,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
