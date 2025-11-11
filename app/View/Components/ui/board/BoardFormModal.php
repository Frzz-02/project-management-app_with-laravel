<?php

namespace App\View\Components\ui\board;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * BoardFormModal Component
 * 
 * Modal component untuk form tambah board baru dalam sebuah project.
 * Component ini menggunakan Alpine.js untuk interaktivitas dan Tailwind CSS untuk styling.
 * 
 * @package App\View\Components\ui
 * 
 * Usage:
 * <x-ui.board-form-modal 
 *     :projectId="$project->id" 
 *     action="{{ route('boards.store') }}"
 *     modalId="addBoardModal" />
 * 
 * Fitur:
 * - Form validation visual
 * - Smooth animations dengan Alpine.js
 * - Responsive design
 * - Auto-focus pada input pertama
 * - Backdrop blur effect
 */
class BoardFormModal extends Component
{
    /**
     * ID project yang akan menjadi parent dari board baru
     * 
     * @var int
     */
    public $projectId;

    /**
     * URL action untuk submit form (route ke controller)
     * 
     * @var string
     */
    public $action;

    /**
     * ID unik untuk modal (untuk Alpine.js state management)
     * 
     * @var string
     */
    public $modalId;

    /**
     * Title modal yang akan ditampilkan di header
     * 
     * @var string
     */
    public $title;

    /**
     * Method HTTP untuk form submission
     * 
     * @var string
     */
    public $method;

    /**
     * Create a new component instance.
     * 
     * @param int $projectId - ID project untuk relasi
     * @param string $action - URL endpoint untuk form submission
     * @param string $modalId - ID unik modal untuk Alpine.js
     * @param string $title - Judul modal
     * @param string $method - HTTP method (default: POST)
     */
    public function __construct(
        $projectId, 
        $action, 
        $modalId = 'boardFormModal', 
        $title = 'Add New Board',
        $method = 'POST'
    ) {
        $this->projectId = $projectId;
        $this->action = $action;
        $this->modalId = $modalId;
        $this->title = $title;
        $this->method = $method;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.board-form-modal');
    }
}