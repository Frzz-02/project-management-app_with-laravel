<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProjectMember Model
 * 
 * Model ini merepresentasikan tabel 'project_members' dan mengelola:
 * - Relasi anggota tim dengan project
 * - Role anggota dalam project (team lead, developer, designer)
 * - Tanggal bergabung (joined_at)
 */
class ProjectMember extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini
     */
    protected $table = 'project_members';

    /**
     * Model ini tidak menggunakan timestamps Laravel default
     * karena hanya ada joined_at di migration
     */
    public $timestamps = false;

    /**
     * Field yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'project_id',   // ID project (foreign key)
        'user_id',      // ID user (foreign key)
        'role',         // Role: team lead, developer, designer
        'joined_at',    // Tanggal bergabung
    ];

    /**
     * Field yang akan otomatis di-cast ke tipe data tertentu
     */
    protected $casts = [
        'joined_at' => 'datetime',  // Cast ke Carbon datetime
    ];

    /**
     * ====================================
     * RELATIONSHIPS (RELASI ANTAR MODEL)
     * ====================================
     */

    /**
     * Relasi ke Project (Many to One)
     * 
     * Setiap member belong to satu project
     * Field: project_id -> projects.id
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * Relasi ke User (Many to One)
     * 
     * Setiap member adalah satu user
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
     * Scope untuk filter berdasarkan role
     * 
     * Usage: ProjectMember::role('team lead')->get()
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope untuk team lead
     * 
     * Usage: ProjectMember::teamLeads()->get()
     */
    public function scopeTeamLeads($query)
    {
        return $query->where('role', 'team lead');
    }

    /**
     * Scope untuk developer
     * 
     * Usage: ProjectMember::developers()->get()
     */
    public function scopeDevelopers($query)
    {
        return $query->where('role', 'developer');
    }

    /**
     * Scope untuk designer
     * 
     * Usage: ProjectMember::designers()->get()
     */
    public function scopeDesigners($query)
    {
        return $query->where('role', 'designer');
    }

    /**
     * ====================================
     * HELPER METHODS
     * ====================================
     */

    /**
     * Cek apakah member adalah team lead
     */
    public function isTeamLead(): bool
    {
        return $this->role === 'team lead';
    }

    /**
     * Cek apakah member adalah developer
     */
    public function isDeveloper(): bool
    {
        return $this->role === 'developer';
    }

    /**
     * Cek apakah member adalah designer
     */
    public function isDesigner(): bool
    {
        return $this->role === 'designer';
    }

    /**
     * Mendapatkan badge color untuk role
     */
    public function getRoleBadgeColorAttribute(): string
    {
        return match($this->role) {
            'team lead' => 'bg-purple-100 text-purple-800',
            'developer' => 'bg-blue-100 text-blue-800',
            'designer' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}
