<?php

namespace App\View\Components\ui\board;

use App\Models\Board;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * DeleteBoardModal Component
 * 
 * Modal konfirmasi untuk menghapus board dengan warning yang jelas
 * tentang data yang akan ikut terhapus (cascade delete).
 * 
 * Component ini menggunakan Alpine.js untuk interaktivitas dan 
 * Tailwind CSS untuk styling dengan theme danger (red).
 * 
 * @package App\View\Components\ui\board
 * 
 * Usage:
 * <x-ui.board.delete-board-modal :board="$board" />
 * 
 * Fitur:
 * - Danger-themed modal dengan red gradient
 * - Menampilkan detail data yang akan terhapus
 * - Loading state saat proses delete
 * - Keyboard shortcuts (Escape untuk cancel)
 * - Smooth animations dengan Alpine.js transitions
 * - Authorization check via Policy
 */
class DeleteBoardModal extends Component
{
    /**
     * Board instance yang akan dihapus
     * 
     * @var Board
     */
    public $board;

    /**
     * Create a new component instance.
     * 
     * @param Board $board - Board model instance
     */
    public function __construct(Board $board)
    {
        $this->board = $board;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.board.delete-board-modal');
    }
}
