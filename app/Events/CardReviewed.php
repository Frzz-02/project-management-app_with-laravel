<?php

namespace App\Events;

use App\Models\Card;
use App\Models\CardReview;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * CardReviewed Event
 * 
 * Event ini di-broadcast secara realtime ketika card direview (approve/reject)
 * Digunakan untuk notifikasi realtime ke semua anggota project
 */
class CardReviewed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CardReview $cardReview;
    public Card $card;

    /**
     * Create a new event instance.
     */
    public function __construct(CardReview $cardReview, Card $card)
    {
        $this->cardReview = $cardReview;
        $this->card = $card;
    }

    /**
     * Get the channels the event should broadcast on.
     * 
     * Broadcast ke channel project agar semua member bisa dengar
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('projects.' . $this->card->board->project_id),
        ];
    }

    /**
     * Data yang akan di-broadcast
     */
    public function broadcastWith(): array
    {
        return [
            'review' => [
                'id' => $this->cardReview->id,
                'card_id' => $this->cardReview->card_id,
                'reviewed_by' => $this->cardReview->reviewed_by,
                'reviewer_name' => $this->cardReview->reviewer->full_name,
                'status' => $this->cardReview->status,
                'notes' => $this->cardReview->notes,
                'reviewed_at' => $this->cardReview->reviewed_at->toDateTimeString(),
            ],
            'card' => [
                'id' => $this->card->id,
                'card_title' => $this->card->card_title,
                'status' => $this->card->status,
                'board_id' => $this->card->board_id,
                'project_id' => $this->card->board->project_id,
            ],
            'message' => $this->cardReview->status === 'approved' 
                ? 'Card "' . $this->card->card_title . '" telah di-approve!'
                : 'Card "' . $this->card->card_title . '" memerlukan perubahan.',
        ];
    }

    /**
     * Nama event yang akan di-broadcast
     */
    public function broadcastAs(): string
    {
        return 'card.reviewed';
    }
}
