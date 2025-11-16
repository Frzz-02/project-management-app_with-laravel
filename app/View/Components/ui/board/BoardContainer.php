<?php

namespace App\View\Components\ui\board;

use Closure;
use Illuminate\View\Component;

class BoardContainer extends Component
{
    public $isCardStatus;
    public $userRole;

    /**
     * Create a new component instance.
     */
    public function __construct($userRole, $isCardStatus = false)
    {
        $this->userRole = $userRole;
        $this->isCardStatus = $isCardStatus;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.ui.board.board-container');
    }
}
