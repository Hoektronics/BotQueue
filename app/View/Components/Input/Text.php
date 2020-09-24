<?php

namespace App\View\Components\Input;

use Illuminate\View\Component;

class Text extends Component
{
    public $name;
    public $label;
    /**
     * @var string
     */
    public $type;

    /**
     * Create a new component instance.
     *
     * @param $name
     * @param $label
     * @param string $type
     */
    public function __construct(
        $name,
        $label,
        $type = 'text'
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->type = $type;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.input.text');
    }
}
