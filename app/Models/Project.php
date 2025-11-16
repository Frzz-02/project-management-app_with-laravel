<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * Project Model
 * 
 * Model ini merepresentasikan tabel 'projects' dan mengelola:
 * - Data project (nama, deskripsi, deadline, dll)
 * - Relasi dengan User (creator)
 * - Relasi dengan ProjectMember (anggota tim)
 * - Relasi dengan Board (papan kanban)
 * - Helper methods untuk deadline status
 */
class Project extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini
     */
    protected $table = 'projects';

    public $timestamps = false;
    
    
    /**
     * Field yang boleh diisi secara mass assignment
     * 
     * Field ini adalah yang bisa diisi melalui create() atau update()
     */
    protected $fillable = [
        'project_name',           // Nama project
        'slug',           // Slug untuk URL (auto-generated dari project_name)
        'description',    // Deskripsi project (nullable)
        'deadline',       // Tanggal deadline (date)
        'created_by',     // ID user yang membuat project (foreign key)
    ];

    /**
     * Field yang akan otomatis di-cast ke tipe data tertentu
     * 
     * Ini membantu Laravel mengkonversi data dari database
     */
    protected $casts = [
        'deadline' => 'date',      // Cast ke Carbon date object
        'created_at' => 'datetime', // Cast ke Carbon datetime
        'updated_at' => 'datetime', // Cast ke Carbon datetime
    ];

    /**
     * Field yang tidak boleh diisi secara mass assignment
     * 
     * Untuk keamanan, field ini hanya bisa diisi manual
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * ====================================
     * RELATIONSHIPS (RELASI ANTAR MODEL)
     * ====================================
     */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'project_id', 'id');
    }

    /**
     * Relasi ke Team Leader (via project_members)
     * 
     * Get user yang menjadi team lead di project ini
     * Field: project_id -> project_members.project_id WHERE role = 'team lead'
     */
    public function teamLeader()
    {
        return $this->hasOneThrough(
            User::class,
            ProjectMember::class,
            'project_id',    // Foreign key on project_members table
            'id',            // Foreign key on users table
            'id',            // Local key on projects table
            'user_id'        // Local key on project_members table
        )->where('project_members.role', 'team lead');
    }

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class, 'project_id', 'id');
    }

    /**
     * ====================================
     * ACCESSOR & MUTATOR
     * ====================================
     */

    /**
     * Accessor untuk mendapatkan status deadline
     * 
     * Digunakan dengan: $project->deadline_status
     */
    public function getDeadlineStatusAttribute(): string
    {
        $deadline = Carbon::parse($this->deadline);
        $now = Carbon::now();
        
        if ($deadline->isPast()) {
            return 'overdue';      // Sudah lewat deadline
        } elseif ($deadline->diffInDays($now) <= 7) {
            return 'due_soon';     // Deadline dalam 7 hari
        } else {
            return 'safe';         // Masih aman
        }
    }

    /**
     * Accessor untuk mendapatkan warna CSS berdasarkan status deadline
     * 
     * Digunakan dengan: $project->deadline_color
     */
    public function getDeadlineColorAttribute(): string
    {
        return match($this->deadline_status) {
            'overdue' => 'text-red-600 bg-red-50',
            'due_soon' => 'text-yellow-600 bg-yellow-50',
            'safe' => 'text-green-600 bg-green-50',
            default => 'text-gray-600 bg-gray-50'
        };
    }

    /**
     * Accessor untuk mendapatkan sisa hari sampai deadline
     * 
     * Digunakan dengan: $project->days_remaining
     */
    public function getDaysRemainingAttribute(): int
    {
        return Carbon::parse($this->deadline)->diffInDays(Carbon::now(), false);
    }

    /**
     * ====================================
     * SCOPES (QUERY BUILDER HELPERS)
     * ====================================
     */

    /**
     * Scope untuk project yang overdue
     * 
     * Usage: Project::overdue()->get()
     */
    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', Carbon::now()->toDateString());
    }

    /**
     * Scope untuk project yang deadline-nya dalam X hari
     * 
     * Usage: Project::dueSoon(7)->get() // deadline dalam 7 hari
     */
    public function scopeDueSoon($query, $days = 7)
    {
        return $query->whereBetween('deadline', [
            Carbon::now()->toDateString(),
            Carbon::now()->addDays($days)->toDateString()
        ]);
    }

    /**
     * Scope untuk project yang dibuat oleh user tertentu
     * 
     * Usage: Project::createdBy($userId)->get()
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope untuk project dimana user adalah anggota
     * 
     * Usage: Project::whereUserIsMember($userId)->get()
     */
    public function scopeWhereUserIsMember($query, $userId)
    {
        return $query->whereHas('members', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * ====================================
     * HELPER METHODS
     * ====================================
     */

    /**
     * Cek apakah user adalah creator project
     */
    public function isCreator($userId): bool
    {
        return $this->created_by == $userId;
    }

    /**
     * Cek apakah user adalah anggota project
     */
    public function hasMember($userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }

    /**
     * Mendapatkan role user di project
     */
    public function getUserRole($userId): ?string
    {
        $member = $this->members()->where('user_id', $userId)->first();
        return $member ? $member->role : null;
    }

    /**
     * Cek apakah user bisa mengedit project
     * (Creator atau Team Lead)
     */
    public function canUserEdit($userId): bool
    {
        if ($this->isCreator($userId)) {
            return true;
        }
        
        return $this->getUserRole($userId) === 'team lead';
    }

    /**
     * Mendapatkan total cards di semua board project
     */
    public function getTotalCardsAttribute(): int
    {
        return $this->boards()->withCount('cards')->get()->sum('cards_count');
    }
}
