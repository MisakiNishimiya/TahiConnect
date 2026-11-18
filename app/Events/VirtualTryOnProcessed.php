<?php

namespace App\Events;

use App\Models\VirtualTryon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VirtualTryOnProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public VirtualTryon $virtualTryon
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->virtualTryon->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'virtual-try-on-processed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->virtualTryon->id,
            'status' => $this->virtualTryon->processing_status,
            'preview_data' => $this->virtualTryon->preview_data,
            'error_message' => $this->virtualTryon->error_message,
        ];
    }
}
