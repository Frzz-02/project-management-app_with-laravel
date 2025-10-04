<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Comment Model
 * 
 * Model ini merepresentasikan tabel 'comments' dan mengelola:
 * - Komentar pada card atau subtask
 * - Tipe komentar (card/subtask)
 * - Relasi dengan Card, Subtask, dan User
 */
class Comment extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini
     */
    protected $table = 'comments';

    /**
     * Model ini tidak menggunakan updated_at (hanya created_at)
     */
    public $timestamps = false;

    /**
     * Field yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'card_id',       // ID card (nullable - untuk comment di card)
        'subtask_id',    // ID subtask (untuk comment di subtask)
        'user_id',       // ID user yang membuat comment
        'comment_text',  // Isi komentar
        'comment_type',  // Tipe: card atau subtask
    ];

    /**
     * Field yang akan otomatis di-cast ke tipe data tertentu
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * ====================================
     * RELATIONSHIPS (RELASI ANTAR MODEL)
     * ====================================
     */

    /**
     * Relasi ke Card (Many to One) - nullable
     * 
     * Comment bisa belong to card (jika comment_type = 'card')
     * Field: card_id -> cards.id
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id', 'id');
    }

    /**
     * Relasi ke Subtask (Many to One)
     * 
     * Comment belong to subtask (jika comment_type = 'subtask')
     * Field: subtask_id -> subtasks.id
     */
    public function subtask(): BelongsTo
    {
        return $this->belongsTo(Subtask::class, 'subtask_id', 'id');
    }

    /**
     * Relasi ke User yang membuat comment (Many to One)
     * 
     * Field: user_id -> users.id
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * ====================================
     * SCOPES (QUERY BUILDER HELPERS)
     * ====================================
     */

    /**
     * Scope untuk comment pada card
     * 
     * Usage: Comment::forCard()->get()
     */
    public function scopeForCard($query)
    {
        return $query->where('comment_type', 'card');
    }

    /**
     * Scope untuk comment pada subtask
     * 
     * Usage: Comment::forSubtask()->get()
     */
    public function scopeForSubtask($query)
    {
        return $query->where('comment_type', 'subtask');
    }

    /**
     * ====================================
     * HELPER METHODS
     * ====================================
     */

    /**
     * Cek apakah comment untuk card
     */
    public function isForCard(): bool
    {
        return $this->comment_type === 'card';
    }

    /**
     * Cek apakah comment untuk subtask
     */
    public function isForSubtask(): bool
    {
        return $this->comment_type === 'subtask';
    }
}
