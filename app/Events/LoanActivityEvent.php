<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanActivityEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $kind,
        public int $loanId,
        public string $itemCode,
        public string $borrower,
        public string $kelas,
        public ?string $by = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('latekaje-lab')];
    }

    public function broadcastAs(): string
    {
        return 'loan.activity';
    }
}
