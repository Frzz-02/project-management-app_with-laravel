<?php

namespace App\View\Components\ui;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BoardCard extends Component
{
    public $name;
    public $description;
    public $totalCards;
    public $cardColorStyle;
    public $badgeColorStyle;

    
    /**
     * Create a new component instance.
     */
    public function __construct( $name, $description, $totalCards, $cardColorStyle = 'from-gray-50 to-blue-50', $badgeColorStyle = 'bg-blue-100 text-blue-800')
    {
        $this->name = $name;
        $this->description = $description;
        $this->totalCards = $totalCards;
        $this->cardColorStyle = $cardColorStyle;
        $this->badgeColorStyle = $badgeColorStyle;
    }


    public function changeColorStyle()
    {
        return match ($this->name) {
            'Todo' => ['from-gray-50 to-blue-50' , 'bg-blue-100 text-blue-800'],
            'In Progress' => ['from-yellow-50 to-orange-50' , 'bg-yellow-100 text-yellow-800'],
            'Review' => ['from-purple-50 to-pink-50' , 'bg-purple-100 text-purple-800'],
            'Done' => ['from-green-50 to-emerald-50' , 'bg-green-100 text-green-800'],
            default => [$this->cardColorStyle, $this->badgeColorStyle],
        };
    }
    

    

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $this->badgeColorStyle = $this->changeColorStyle()[1];
        $this->cardColorStyle = $this->changeColorStyle()[0];
        return view('components.ui.board-card');
    }
}