<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * TimeLog Model
 * 
 * Model ini merepresentasikan tabel 'time_logs' dan mengelola:
 * - Tracking waktu kerja pada card/subtask
 * - Durasi kerja dalam menit
 * - Relasi dengan Card, Subtask, dan User
 * - Helper methods untuk kalkulasi waktu
 */
class TimeLog extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini
     */
    protected $table = 'time_logs';

    /**
     * Field yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'card_id',          // ID card (foreign key)
        'subtask_id',       // ID subtask (foreign key) 
        'user_id',          // ID user yang melakukan tracking
        'start_time',       // Waktu mulai kerja
        'end_time',         // Waktu selesai kerja (nullable)
        'duration_minutes', // Durasi dalam menit
        'description',      // Deskripsi pekerjaan (nullable)
    ];

    /**
     * Field yang akan otomatis di-cast ke tipe data tertentu
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    /**
     * ====================================
     * RELATIONSHIPS (RELASI ANTAR MODEL)
     * ====================================
     */

    /**
     * Relasi ke Card (Many to One)
     * 
     * Setiap time log belong to satu card
     * Field: card_id -> cards.id
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id', 'id');
    }

    /**
     * Relasi ke Subtask (Many to One)
     * 
     * Setiap time log belong to satu subtask
     * Field: subtask_id -> subtasks.id
     */
    public function subtask(): BelongsTo
    {
        return $this->belongsTo(Subtask::class, 'subtask_id', 'id');
    }

    /**
     * Relasi ke User (Many to One)
     * 
     * Setiap time log belong to satu user
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
     * Scope untuk time log yang masih berjalan (belum selesai)
     * 
     * Usage: TimeLog::ongoing()->get()
     */
    public function scopeOngoing($query)
    {
        return $query->whereNull('end_time');
    }

    /**
     * Scope untuk time log yang sudah selesai
     * 
     * Usage: TimeLog::completed()->get()
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('end_time');
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     * 
     * Usage: TimeLog::onDate('2024-01-01')->get()
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('start_time', $date);
    }

    /**
     * Scope untuk filter berdasarkan user
     * 
     * Usage: TimeLog::byUser($userId)->get()
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * ====================================
     * HELPER METHODS
     * ====================================
     */

    /**
     * Cek apakah time log masih berjalan
     */
    public function isOngoing(): bool
    {
        return is_null($this->end_time);
    }

    /**
     * Menghentikan time log dan menghitung durasi
     */
    public function stop(): bool
    {
        if ($this->isOngoing()) {
            $endTime = Carbon::now();
            $duration = $this->start_time->diffInMinutes($endTime);
            
            return $this->update([
                'end_time' => $endTime,
                'duration_minutes' => $duration
            ]);
        }
        
        return false;
    }

    /**
     * Mendapatkan durasi dalam format jam:menit (contoh: "2:30")
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = intval($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        return sprintf('%d:%02d', $hours, $minutes);
    }

    /**
     * Mendapatkan durasi dalam jam (contoh: 2.5)
     */
    public function getDurationInHoursAttribute(): float
    {
        return round($this->duration_minutes / 60, 2);
    }

    /**
     * Auto calculate duration saat model disimpan
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($timeLog) {
            // Auto calculate duration jika belum diset dan end_time ada
            if (is_null($timeLog->duration_minutes) && !is_null($timeLog->end_time)) {
                $timeLog->duration_minutes = Carbon::parse($timeLog->start_time)
                    ->diffInMinutes(Carbon::parse($timeLog->end_time));
            }
        });
    }
}
