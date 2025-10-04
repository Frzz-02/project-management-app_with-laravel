<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Subtask Model
 * 
 * Model ini merepresentasikan tabel 'subtasks' dan mengelola:
 * - Sub-tugas dalam sebuah card
 * - Status subtask (to do, in progress, done)
 * - Estimasi dan actual hours untuk subtask
 * - Relasi dengan Card dan Comment
 */
class Subtask extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini
     */
    protected $table = 'subtasks';

    /**
     * Model ini tidak menggunakan updated_at (hanya created_at)
     */
    public $timestamps = false;

    /**
     * Field yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'card_id',          // ID card (foreign key)
        'subtask_name',     // Nama subtask
        'description',      // Deskripsi subtask (nullable)
        'status',           // Status: to do, in progress, done
        'estimated_hours',  // Estimasi jam (nullable)
        'actual_hours',     // Jam aktual (nullable)
        'position',         // Posisi dalam card
    ];

    /**
     * Field yang akan otomatis di-cast ke tipe data tertentu
     */
    protected $casts = [
        'created_at' => 'datetime',
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
     * Relasi ke Card (Many to One)
     * 
     * Setiap subtask belong to satu card
     * Field: card_id -> cards.id
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id', 'id');
    }

    /**
     * Relasi ke Comment (One to Many)
     * 
     * Satu subtask bisa memiliki banyak comment
     * Field: id -> comments.subtask_id
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'subtask_id', 'id');
    }

    /**
     * ====================================
     * SCOPES (QUERY BUILDER HELPERS)
     * ====================================
     */

    /**
     * Scope untuk filter berdasarkan status
     * 
     * Usage: Subtask::status('done')->get()
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk subtask yang sudah selesai
     * 
     * Usage: Subtask::completed()->get()
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'done');
    }

    /**
     * Scope untuk subtask yang belum selesai
     * 
     * Usage: Subtask::pending()->get()
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['to do', 'in progress']);
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
            'to do' => 'bg-gray-100 text-gray-800',
            'in progress' => 'bg-blue-100 text-blue-800',
            'done' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Cek apakah subtask sudah selesai
     */
    public function isCompleted(): bool
    {
        return $this->status === 'done';
    }
}
