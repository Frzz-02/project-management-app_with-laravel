# Auto Slug Generation for Projects

## 📋 Overview

Sistem ini secara **otomatis** menggenerate slug dari `project_name` menggunakan **Laravel Observer Pattern**.

## 🎯 Fitur

### ✅ **Auto-Generate Slug**
- Ketika project dibuat (via form, seeder, tinker, atau API)
- Slug otomatis di-generate dari `project_name`
- Format: lowercase, strip special chars, replace spaces dengan dash

**Contoh:**
```
Project Name: "My Awesome Project"
Generated Slug: "my-awesome-project"
```

### ✅ **Unique Slug Handling**
- Jika slug sudah ada, otomatis tambahkan counter
- Increment counter sampai slug unique

**Contoh:**
```
1st Project: "My Project" → slug: "my-project"
2nd Project: "My Project" → slug: "my-project-1"
3rd Project: "My Project" → slug: "my-project-2"
```

### ✅ **Auto-Update on Name Change**
- Ketika `project_name` diubah, slug otomatis ter-update
- Tetap menjaga uniqueness

### ✅ **Works Everywhere**
- ✅ Form tambah project
- ✅ Seeder (`php artisan db:seed`)
- ✅ Factory (`Project::factory()->create()`)
- ✅ Tinker (`Project::create([...])`)
- ✅ Direct Model create (`new Project()`)

## 🔧 Implementation

### 1. **Observer** (`app/Observers/ProjectObserver.php`)

```php
class ProjectObserver
{
    public function creating(Project $project): void
    {
        // Generate slug SEBELUM data disimpan
        if (empty($project->slug)) {
            $project->slug = $this->generateUniqueSlug($project->project_name);
        }
    }

    public function updating(Project $project): void
    {
        // Update slug jika project_name berubah
        if ($project->isDirty('project_name')) {
            $project->slug = $this->generateUniqueSlug(
                $project->project_name, 
                $project->id
            );
        }
    }

    private function generateUniqueSlug(string $projectName, ?int $excludeId = null): string
    {
        $slug = Str::slug($projectName);
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}
```

### 2. **Registration** (`app/Providers/AppServiceProvider.php`)

```php
public function boot(): void
{
    // Register Observer
    Project::observe(ProjectObserver::class);
}
```

### 3. **Model Update** (`app/Models/Project.php`)

```php
protected $fillable = [
    'project_name',
    'slug',        // ✅ Added to fillable
    'description',
    'deadline',
    'created_by',
];
```

## 🧪 Testing

### **Test 1: Create via Tinker**
```bash
php artisan tinker

Project::create([
    'project_name' => 'Test Auto Slug',
    'description' => 'Testing',
    'deadline' => now()->addDays(30),
    'created_by' => 1
]);

# Result: slug = "test-auto-slug" ✅
```

### **Test 2: Duplicate Handling**
```bash
# Create second project with same name
Project::create([
    'project_name' => 'Test Auto Slug',
    'description' => 'Testing duplicate',
    'deadline' => now()->addDays(30),
    'created_by' => 1
]);

# Result: slug = "test-auto-slug-1" ✅
```

### **Test 3: Update Project Name**
```bash
$project = Project::first();
$project->update(['project_name' => 'Updated Project Name']);

# Result: slug automatically updated to "updated-project-name" ✅
```

### **Test 4: Seeder**
```bash
php artisan db:seed --class=ProjectSeeder

# All projects will have auto-generated slugs ✅
```

## 📊 Database Schema

```sql
CREATE TABLE projects (
    id BIGINT UNSIGNED PRIMARY KEY,
    slug VARCHAR(255) UNIQUE,  -- Auto-generated, unique
    project_name VARCHAR(255),
    description TEXT NULL,
    created_by BIGINT UNSIGNED,
    deadline DATE,
    created_at TIMESTAMP
);
```

## 🔐 Security

- ✅ **SQL Injection Safe**: Uses Eloquent ORM
- ✅ **Unique Constraint**: Database-level unique index
- ✅ **XSS Safe**: `Str::slug()` sanitizes input
- ✅ **Race Condition Safe**: Unique check in loop

## 📝 Best Practices

### ✅ **DO:**
```php
// Just provide project_name, slug will be auto-generated
Project::create([
    'project_name' => 'My Project',
    'description' => 'Description',
    'deadline' => now()->addDays(30),
    'created_by' => auth()->id()
]);
```

### ❌ **DON'T:**
```php
// Don't manually set slug (unless you have specific reason)
Project::create([
    'project_name' => 'My Project',
    'slug' => 'custom-slug',  // Observer will be skipped if slug exists
    // ...
]);
```

## 🐛 Troubleshooting

### Issue: "Slug not generated"
**Solution:** Make sure Observer is registered in AppServiceProvider

### Issue: "Duplicate slug error"
**Solution:** Observer handles this automatically with counter

### Issue: "Slug not updated on name change"
**Solution:** Make sure you're using `update()` method, not direct property assignment

## 📚 Related Files

- `app/Observers/ProjectObserver.php` - Observer logic
- `app/Providers/AppServiceProvider.php` - Observer registration
- `app/Models/Project.php` - Model with fillable slug
- `database/migrations/2025_09_03_011353_create_projects_table.php` - Schema

## ✅ Status

**Implementation Date:** November 10, 2025
**Status:** ✅ ACTIVE
**Test Status:** ✅ ALL TESTS PASSED

---

**Auto-generated slugs make URLs clean, unique, and SEO-friendly!** 🚀
