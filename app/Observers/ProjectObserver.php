<?php

namespace App\Observers;

use App\Models\Project;
use Illuminate\Support\Str;

class ProjectObserver
{
    /**
     * Handle the Project "creating" event.
     * 
     * Event ini dipanggil SEBELUM data disimpan ke database.
     * Perfect untuk auto-generate slug dari project_name.
     */
    public function creating(Project $project): void
    {
        // Generate slug dari project_name jika slug belum diisi
        if (empty($project->slug)) {
            $project->slug = $this->generateUniqueSlug($project->project_name);
        }
    }

    /**
     * Handle the Project "updating" event.
     * 
     * Event ini dipanggil SEBELUM data diupdate di database.
     * Update slug jika project_name berubah.
     */
    public function updating(Project $project): void
    {
        // Jika project_name berubah, regenerate slug
        if ($project->isDirty('project_name')) {
            $project->slug = $this->generateUniqueSlug($project->project_name, $project->id);
        }
    }

    /**
     * Generate unique slug dari project name
     * 
     * Method ini:
     * 1. Convert project_name ke slug format (lowercase, strip special chars, replace space with dash)
     * 2. Check apakah slug sudah ada di database
     * 3. Jika sudah ada, tambahkan angka suffix (-1, -2, dst)
     * 
     * @param string $projectName
     * @param int|null $excludeId ID project yang di-exclude (untuk update)
     * @return string
     */
    private function generateUniqueSlug(string $projectName, ?int $excludeId = null): string
    {
        // Generate base slug dari project_name
        // Contoh: "My Awesome Project" -> "my-awesome-project"
        $slug = Str::slug($projectName);
        
        // Check apakah slug sudah ada
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $excludeId)) {
            // Jika sudah ada, tambahkan counter
            // my-awesome-project-1, my-awesome-project-2, dst
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Check apakah slug sudah ada di database
     * 
     * @param string $slug
     * @param int|null $excludeId ID project yang di-exclude (untuk update)
     * @return bool
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Project::where('slug', $slug);
        
        // Exclude current project ID (untuk update)
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }
}
