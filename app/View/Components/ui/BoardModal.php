<?php

namespace App\View\Components\ui;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * BoardModal Component
 * 
 * Modal component untuk form tambah board baru dalam project.
 * Component ini menggunakan Alpine.js untuk interaktivitas dan Tailwind CSS untuk styling.
 * 
 * Usage di Blade:
 * <x-ui.board-modal 
 *     :project-id="$project->id" 
 *     action-url="/boards" 
 *     modal-id="addBoardModal"
 * />
 * 
 * Untuk membuka modal, gunakan Alpine.js:
 * <button @click="$dispatch('open-modal', 'addBoardModal')">Add Board</button>
 * 
 * Properties:
 * - projectId: ID project tempat board akan dibuat
 * - actionUrl: URL endpoint untuk submit form
 * - modalId: ID unik modal (default: 'boardModal')
 */
class BoardModal extends Component
{
    /**
     * ID project tempat board akan dibuat
     * 
     * @var int
     */
    public $projectId;

    /**
     * URL endpoint untuk submit form tambah board
     * 
     * @var string
     */
    public $actionUrl;

    /**
     * ID unik untuk modal element
     * 
     * @var string
     */
    public $modalId;

    /**
     * Create a new component instance.
     * 
     * @param int $projectId ID project
     * @param string $actionUrl URL untuk submit form
     * @param string $modalId ID modal (opsional)
     */
    public function __construct($projectId, $actionUrl, $modalId = 'boardModal')
    {
        $this->projectId = $projectId;
        $this->actionUrl = $actionUrl;
        $this->modalId = $modalId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.board-modal');
    }
}