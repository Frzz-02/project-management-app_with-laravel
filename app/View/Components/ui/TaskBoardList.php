<?php

namespace App\View\Components\ui;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskBoardList extends Component
{
    public $taskName;
    public $description;
    public $taskStatus;
    public $badgeColorStyle;
    /**
     * Create a new component instance.
     */
    public function __construct( $taskName, $description, $taskStatus, $badgeColorStyle = 'from-gray-50 to-blue-50')
    {
        $this->taskName = $taskName;
        $this->description = $description;
        $this->taskStatus = $taskStatus;
        $this->badgeColorStyle = $badgeColorStyle;
    }


    public function changeBadgeColor()
    {
        return match ($this->taskStatus) {
            'todo' => 'bg-blue-100 text-blue-800',
            'in progress' => 'bg-yellow-100 text-yellow-800',
            'review' => 'bg-purple-100 text-purple-800',
            'done' => 'bg-green-100 text-green-800',
            default => $this->badgeColorStyle ?? 'bg-blue-100 text-blue-800',
        };
    }
    
    

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $this->badgeColorStyle = $this->changeBadgeColor();
        return view('components.ui.task-board-list');
    }
}
