<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Unit;

/*
 * Used on the unit detail page.
 * Renders all copy.
 */
class UnitCopy extends Component
{
    public $unit_id; // we are passing in the id of the unit

    public function render()
    {
        $unit_copy = Unit::with('placement')->find($this->unit_id);
        return view('livewire.unit-copy', compact('unit_copy'));
    }
}
