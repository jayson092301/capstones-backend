<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Announcement;

class NewAnnouncement implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $id;
    public string $announcement;
    public $connection = 'sync';

    public function __construct(int $id, string $announcement)
    {
        $this->id = $id;
        $this->announcement = $announcement;
    }

    public function broadcastOn(): array
    {
        return [new Channel('student-announcement')];
    }

    public function broadcastAs(): string
    {
        return 'NewAnnouncement';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->id,
            'announcement' => $this->announcement,
        ];
    }

}
