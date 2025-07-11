<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Unit;
use App\Models\Image;
use App\Models\Outputimage;
use Symfony\Component\Console\Output\Output;

class UnitStatusImage extends Component
{
    public $unit_id; // we are passing in the id of the unit

    public function render()
    {
        // get unit
        //$unitstatus = Unit::find($this->unit_id);
        $unitstatus = Unit::with('placement')->find($this->unit_id);
        // get images
        // If it's a caroussel, get 3 images, else get 1 image
        if (($unitstatus->placement->figma_id === 'dynamic_meta_static_carousel9x16') OR ($unitstatus->placement->figma_id === 'dynamic_meta_static_carousel1x1'))
        {
            $images = Image::where('unit_id', $this->unit_id)
                ->latest()
                ->take(3)
                ->get();
        } else
        {
            $images = Image::where('unit_id', $this->unit_id)
                ->latest()
                ->take(1)
                ->get();
        }


        // output image
        $outputimage = Outputimage::where('unit_id', $this->unit_id)
            ->latest()
            ->first();

        return view('livewire.unit-status-image', compact('unitstatus', 'outputimage', 'images'));
    }
}
