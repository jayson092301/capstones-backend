<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\PcDevices;

class PcStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $id;
    public string $status;
    public string $device_name;
    public $connection = 'sync';

    public function __construct(int $id, string $status, string $device_name)
    {
        $this->id = $id;
        $this->status = $status;
        $this->device_name = $device_name;
    }

    public function broadcastOn(): array
    {
        return [new Channel('pc-devices')];
    }

    public function broadcastAs(): string
    {
        return 'PcStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'assignedNumber' => $this->device_name,
        ];
    }

}
