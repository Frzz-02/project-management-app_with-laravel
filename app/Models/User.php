<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 * 
 * Model ini merepresentasikan tabel 'users' dan mengelola:
 * - Data user (name, email, password)
 * - Relasi dengan Project (sebagai creator)
 * - Relasi dengan ProjectMember (sebagai anggota tim)
 * - Relasi dengan Card, Comment, TimeLog yang dibuat user
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public $timestamps = false;
    protected $table = 'users';
    protected $fillable = [
        'username',
        'full_name',
        'current_task_status',
        'email',
        'role',
        'password',
    ];
    protected $guarded = ['id', 'created_at'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * ====================================
     * PROJECT MANAGEMENT RELATIONSHIPS
     * ====================================
     */

    /**
     * Relasi ke Project yang dibuat user (One to Many)
     * 
     * User bisa membuat banyak project
     * Field: id -> projects.created_by
     */
    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by', 'id');
    }

    /**
     * Relasi ke ProjectMember (One to Many)
     * 
     * User bisa menjadi anggota di banyak project
     * Field: id -> project_members.user_id
     */
    public function projectMemberships(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'user_id', 'id');
    }

    /**
     * Relasi ke Card yang dibuat user (One to Many)
     * 
     * User bisa membuat banyak card
     * Field: id -> cards.created_by
     */
    public function createdCards(): HasMany
    {
        return $this->hasMany(Card::class, 'created_by', 'id');
    }

    /**
     * Relasi ke Comment yang dibuat user (One to Many)
     * 
     * User bisa membuat banyak comment
     * Field: id -> comments.user_id
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'user_id', 'id');
    }

    /**
     * Relasi ke TimeLog yang dibuat user (One to Many)
     * 
     * User bisa memiliki banyak time log
     * Field: id -> time_logs.user_id
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class, 'user_id', 'id');
    }

    /**
     * ====================================
     * HELPER METHODS UNTUK PROJECT
     * ====================================
     */

    /**
     * Cek apakah user adalah creator dari project tertentu
     */
    public function isProjectCreator($projectId): bool
    {
        return $this->createdProjects()->where('id', $projectId)->exists();
    }

    /**
     * Cek apakah user adalah anggota dari project tertentu
     */
    public function isProjectMember($projectId): bool
    {
        return $this->projectMemberships()->where('project_id', $projectId)->exists();
    }

    /**
     * Mendapatkan role user di project tertentu
     */
    public function getProjectRole($projectId): ?string
    {
        $membership = $this->projectMemberships()
            ->where('project_id', $projectId)
            ->first();
        
        return $membership ? $membership->role : null;
    }

    /**
     * Mendapatkan semua project yang terkait dengan user
     * (baik sebagai creator maupun anggota)
     */
    public function getAllRelatedProjects()
    {
        $createdProjects = $this->createdProjects()->get();
        $memberProjects = Project::whereHas('members', function($query) {
            $query->where('user_id', $this->id);
        })->get();

        return $createdProjects->merge($memberProjects)->unique('id');
    }
}
