<?php

namespace App\Livewire;

use App\Models\Image;
use App\Models\Outputimage;
use Livewire\Component;
use App\Models\Unit;

class UnitImages extends Component
{
    public $unit_id;

    public function render()
    {
        $unit_images = Unit::with(['placement', 'campaign', 'images'])->find($this->unit_id);
        $outputimage = Outputimage::where('unit_id', $this->unit_id)->latest()->first();
        $oneimage = Image::where('unit_id', $this->unit_id)->latest()->first();

        return view('livewire.unit-images', compact('unit_images', 'outputimage', 'oneimage'));
    }
}
