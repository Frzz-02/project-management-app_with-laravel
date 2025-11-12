<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Card Model
 * 
 * Model ini merepresentasikan tabel 'cards' dan mengelola:
 * - Kartu tugas dalam board kanban
 * - Status dan prioritas kartu
 * - Estimasi dan actual hours
 * - Relasi dengan Board, User, Subtask, dll
 */
class Card extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini
     */
    protected $table = 'cards';

    /**
     * Model ini menggunakan custom timestamps
     */
    const UPDATED_AT = null;

    /**
     * Field yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'board_id',         // ID board (foreign key)
        'card_title',       // Judul kartu
        'description',      // Deskripsi kartu (nullable)
        'position',         // Posisi dalam board
        'created_by',       // User yang membuat (nullable)
        'due_date',         // Tanggal deadline (nullable)
        'status',           // Status: todo, in progress, review, done
        'priority',         // Prioritas: low, medium, high
        'estimated_hours',  // Estimasi jam (nullable)
        'actual_hours',     // Jam aktual yang dikerjakan
    ];

    /**
     * Field yang akan otomatis di-cast ke tipe data tertentu
     */
    protected $casts = [
        'created_at' => 'datetime',
        'due_date' => 'date',
        'position' => 'integer',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
    ];

    /**
     * ====================================
     * RELATIONSHIPS (RELASI ANTAR MODEL)
     * ====================================
     */

    /**
     * Relasi ke Board (Many to One)
     * 
     * Setiap card belong to satu board
     * Field: board_id -> boards.id
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id', 'id');
    }

    /**
     * Relasi ke User yang membuat card (Many to One)
     * 
     * Field: created_by -> users.id
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Relasi ke Subtask (One to Many)
     * 
     * Satu card bisa memiliki banyak subtask
     * Field: id -> subtasks.card_id
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class, 'card_id', 'id');
    }




    public function comments() : HasMany
    {
        return $this->hasMany(Comment::class, 'card_id', 'id');
    }
    
    /**
     * Relasi ke CardAssignment (One to Many)
     * 
     * Satu card bisa memiliki banyak assignment ke users
     * Field: id -> card_assignments.card_id
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(CardAssignment::class, 'card_id', 'id');
    }

    /**
     * Relasi ke TimeLog (One to Many)
     * 
     * Satu card bisa memiliki banyak time logs
     * Field: id -> time_logs.card_id
     * 
     * Note: TimeLog dengan subtask_id NULL adalah card tracking
     *       TimeLog dengan subtask_id NOT NULL adalah subtask tracking
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class, 'card_id', 'id');
    }

    /**
     * Relasi ke CardReview (One to Many)
     * 
     * Satu card bisa memiliki banyak review history
     * Field: id -> card_reviews.card_id
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(CardReview::class, 'card_id', 'id');
    }
    
    /**
     * ====================================
     * SCOPES (QUERY BUILDER HELPERS)
     * ====================================
     */

    /**
     * Scope untuk filter berdasarkan status
     * 
     * Usage: Card::status('todo')->get()
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter berdasarkan prioritas
     * 
     * Usage: Card::priority('high')->get()
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope untuk card yang overdue
     * 
     * Usage: Card::overdue()->get()
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
                    ->where('due_date', '<', now())
                    ->where('status', '!=', 'done');
    }

    /**
     * ====================================
     * HELPER METHODS
     * ====================================
     */

    /**
     * Mendapatkan warna badge untuk status
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'todo' => 'bg-gray-100 text-gray-800',
            'in progress' => 'bg-blue-100 text-blue-800',
            'review' => 'bg-yellow-100 text-yellow-800',
            'done' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Mendapatkan warna badge untuk prioritas
     */
    public function getPriorityBadgeColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'bg-green-100 text-green-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'high' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }



    /**
     * ====================================
     * HELPER METHODS UNTUK TIME TRACKING
     * ====================================
     */

    /**
     * Cek apakah card ini sudah pernah di-start tracking (punya completed time log)
     * 
     * @return bool
     */
    public function hasBeenTracked(): bool
    {
        return $this->timeLogs()->whereNotNull('end_time')->exists();
    }

    /**
     * Cek apakah card ini sedang di-track (punya ongoing time log)
     * 
     * @return bool
     */
    public function isBeingTracked(): bool
    {
        return $this->timeLogs()->whereNull('end_time')->exists();
    }

    /**
     * Get ongoing time log untuk card ini
     * 
     * @return \App\Models\TimeLog|null
     */
    public function getOngoingTimeLog()
    {
        return $this->timeLogs()->whereNull('end_time')->first();
    }
}
