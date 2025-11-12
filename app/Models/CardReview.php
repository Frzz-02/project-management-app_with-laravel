<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CardReview Model
 * 
 * Model ini merepresentasikan tabel 'card_reviews' dan mengelola:
 * - History approve/reject task oleh team lead
 * - Status review (approved/rejected)
 * - Notes/keterangan dari reviewer (opsional)
 * - Audit trail dengan timestamp
 */
class CardReview extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini
     */
    protected $table = 'card_reviews';

    /**
     * Model ini tidak menggunakan timestamps Laravel default (created_at, updated_at)
     * karena hanya menggunakan reviewed_at
     */
    public $timestamps = false;

    /**
     * Field yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'card_id',      // ID card yang direview
        'reviewed_by',  // ID user yang melakukan review (team lead)
        'status',       // Status: approved atau rejected
        'notes',        // Keterangan/catatan (opsional)
        'reviewed_at',  // Timestamp review
    ];

    /**
     * Field yang akan otomatis di-cast ke tipe data tertentu
     */
    protected $casts = [
        'reviewed_at' => 'datetime',  // Cast ke Carbon datetime
    ];

    /**
     * ====================================
     * RELATIONSHIPS (RELASI ANTAR MODEL)
     * ====================================
     */

    /**
     * Relasi ke Card (Many to One)
     * 
     * Setiap review belong to satu card
     * Field: card_id -> cards.id
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id', 'id');
    }

    /**
     * Relasi ke User (reviewer/team lead) (Many to One)
     * 
     * Setiap review dilakukan oleh satu user (team lead)
     * Field: reviewed_by -> users.id
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    /**
     * ====================================
     * SCOPES (QUERY BUILDER HELPERS)
     * ====================================
     */

    /**
     * Scope untuk filter berdasarkan status
     * 
     * Usage: CardReview::status('approved')->get()
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk review yang approved
     * 
     * Usage: CardReview::approved()->get()
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope untuk review yang rejected
     * 
     * Usage: CardReview::rejected()->get()
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope untuk review berdasarkan card
     * 
     * Usage: CardReview::forCard($cardId)->get()
     */
    public function scopeForCard($query, $cardId)
    {
        return $query->where('card_id', $cardId);
    }

    /**
     * Scope untuk review berdasarkan reviewer
     * 
     * Usage: CardReview::byReviewer($userId)->get()
     */
    public function scopeByReviewer($query, $userId)
    {
        return $query->where('reviewed_by', $userId);
    }

    /**
     * Scope untuk review terbaru
     * 
     * Usage: CardReview::latest()->get()
     */
    public function scopeLatestReviews($query)
    {
        return $query->orderBy('reviewed_at', 'desc');
    }

    /**
     * ====================================
     * HELPER METHODS
     * ====================================
     */

    /**
     * Cek apakah review adalah approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Cek apakah review adalah rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Cek apakah review memiliki notes
     */
    public function hasNotes(): bool
    {
        return !empty($this->notes);
    }

    /**
     * Mendapatkan badge color untuk status
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Mendapatkan status text yang lebih readable
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown'
        };
    }

    /**
     * Format reviewed_at untuk display
     */
    public function getReviewedAtFormattedAttribute(): string
    {
        return $this->reviewed_at->format('d M Y, H:i');
    }
}
