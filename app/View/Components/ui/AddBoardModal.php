<?php

namespace App\View\Components\ui;

use Closure;
use Illuminate\View\Component;

/**
 * AddBoardModal Component
 * 
 * Modal component untuk menambah board baru dalam project.
 * Component ini menggunakan Alpine.js untuk interaktivitas dan Tailwind CSS untuk styling.
 * 
 * Usage:
 * <x-ui.add-board-modal :project-id="$project->id" :next-position="$nextPosition" />
 * 
 * Required Props:
 * - projectId: ID project tempat board akan ditambahkan
 * - nextPosition: Posisi selanjutnya untuk board baru (untuk urutan)
 * 
 * Features:
 * - Form validation dengan Alpine.js
 * - Responsive design
 * - Smooth animations
 * - Auto-focus pada input pertama
 * - Reset form saat modal ditutup
 */
class AddBoardModal extends Component
{
    /**
     * ID project tempat board akan ditambahkan
     */
    public $projectId;
    
    /**
     * Posisi selanjutnya untuk board baru
     */
    public $nextPosition;

    /**
     * Create a new component instance.
     *
     * @param int $projectId ID project
     * @param int $nextPosition Posisi urutan board selanjutnya
     */
    public function __construct($projectId, $nextPosition = 1)
    {
        $this->projectId = $projectId;
        $this->nextPosition = $nextPosition;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.ui.add-board-modal');
    }
}