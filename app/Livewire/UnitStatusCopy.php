<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Unit;

/*
 * Used on the campaign overview page that lists each unit.
 * Update the status of the copy generation for each unit
 */
class UnitStatusCopy extends Component
{
    public $unit_id; // we are passing in the id of the unit

    public function render()
    {
        $unitstatus = Unit::find($this->unit_id);
        return view('livewire.unit-status-copy', compact('unitstatus'));
    }
}
