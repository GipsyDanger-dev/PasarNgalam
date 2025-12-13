<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $orderId;
    public int $merchantId;
    public ?int $driverId;
    public string $status;

    public function __construct(int $orderId, int $merchantId, ?int $driverId, string $status)
    {
        $this->orderId = $orderId;
        $this->merchantId = $merchantId;
        $this->driverId = $driverId;
        $this->status = $status;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('order.' . $this->orderId),
            new Channel('merchant.' . $this->merchantId),
        ];

        if ($this->driverId) {
            $channels[] = new Channel('driver.' . $this->driverId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'order.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'status' => $this->status,
        ];
    }
}
