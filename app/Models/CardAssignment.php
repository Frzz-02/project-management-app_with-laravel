<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CardAssignment Model
 * 
 * Model ini merepresentasikan tabel 'card_assignments' dan mengelola:
 * - Assignment user ke card tertentu
 * - Role dan status progress assignment
 * - Waktu mulai dan selesai tugas
 */
class CardAssignment extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini
     */
    protected $table = 'card_assignments';

    /**
     * Model ini tidak menggunakan timestamps default
     */
    public $timestamps = false;

    /**
     * Field yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'card_id',      // ID card (foreign key)
        'user_id',      // ID user yang assigned (foreign key)
        'assignment_status',         // Role: assigned, in progress, completed
        'started_at',   // Waktu mulai mengerjakan
        'completed_at', // Waktu selesai mengerjakan
    ];

    /**
     * Field yang akan otomatis di-cast ke tipe data tertentu
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * ====================================
     * RELATIONSHIPS (RELASI ANTAR MODEL)
     * ====================================
     */

    /**
     * Relasi ke Card (Many to One)
     * 
     * Setiap assignment belong to satu card
     * Field: card_id -> cards.id
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id', 'id');
    }

    /**
     * Relasi ke User yang assigned (Many to One)
     * 
     * Field: user_id -> users.id
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
