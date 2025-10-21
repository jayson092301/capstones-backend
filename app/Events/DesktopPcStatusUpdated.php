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

class DesktopPcStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $id;
    public string $status;
    public string $device_name;
    public string $macAddress;
    public $connection = 'sync';

    public function __construct(int $id, string $status, string $device_name, string $macAddress)
    {
        $this->id = $id;
        $this->status = $status;
        $this->device_name = $device_name;
        $this->macAddress = $macAddress;
    }

    public function broadcastOn(): array
    {
        return [new Channel('DesktopLockApp')];
    }

    public function broadcastAs(): string
    {
        return 'DesktopPcStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'assignedNumber' => $this->device_name,
            'macAddress' => $this->macAddress,
        ];
    }

}
