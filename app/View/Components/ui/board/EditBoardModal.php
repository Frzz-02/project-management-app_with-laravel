<?php

namespace App\View\Components\ui\board;

use App\Models\Board;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * EditBoardModal Component
 * 
 * Modal untuk mengedit board name dan description.
 * 
 * @package App\View\Components\ui\board
 * 
 * Usage:
 * <x-ui.board.edit-board-modal :board="$board" />
 */
class EditBoardModal extends Component
{
    /**
     * Board instance yang akan diedit
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
        return view('components.ui.board.edit-board-modal');
    }
}
