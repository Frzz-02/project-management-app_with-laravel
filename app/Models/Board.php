<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Board Model
 * 
 * Model ini merepresentasikan tabel 'boards' dan mengelola:
 * - Papan kanban dalam sebuah project
 * - Relasi dengan Project dan Card
 * - Posisi board dalam project
 */
class Board extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini
     */
    protected $table = 'boards';

    /**
     * Model ini tidak menggunakan updated_at
     */
    public $timestamps = false;

    /**
     * Field yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'project_id',   // ID project (foreign key)
        'board_name',   // Nama board
        'description',  // Deskripsi board (nullable)
        'position',     // Posisi urutan board
    ];

    /**
     * Field yang akan otomatis di-cast ke tipe data tertentu
     */
    protected $casts = [
        'created_at' => 'datetime',
        'position' => 'integer',
    ];

    /**
     * ====================================
     * RELATIONSHIPS (RELASI ANTAR MODEL)
     * ====================================
     */

    /**
     * Relasi ke Project (Many to One)
     * 
     * Setiap board belong to satu project
     * Field: project_id -> projects.id
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * Relasi ke Card (One to Many)
     * 
     * Satu board bisa memiliki banyak card
     * Field: id -> cards.board_id
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'board_id', 'id');
    }

    /**
     * ====================================
     * SCOPES (QUERY BUILDER HELPERS)
     * ====================================
     */

    /**
     * Scope untuk urutkan berdasarkan posisi
     * 
     * Usage: Board::ordered()->get()
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position', 'asc');
    }
}
