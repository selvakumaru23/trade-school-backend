<?php

namespace App\Http\Controllers;

use App\Models\Outputimage;
use App\Models\Image;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /*
     * Overview page
     */
    public function index()
    {
        return redirect('/dashboard');
    }
    /*
     * Unit detail page
     */
    public function show(Request $request, $id)
    {
        // Find the campaign, throw 404 if not found
        // Get the unit, with images if they exist_in_airtable
        $unit = Unit::with(['campaign', 'placement', 'images' => function ($query) {
            $query->where('exists_in_airtable', true);
        }])->findOrFail($id);
        if (!$unit->exists_in_airtable) abort(404);
        //$outputimages = Outputimage::where('unit_id', $unit->id)->get();
        $outputimage = Outputimage::where('unit_id', $unit->id)->latest()->first();
        $oneimage = Image::where('unit_id', $unit->id)->latest()->first();

        $nextUnit = Unit::where('campaign_id', $unit->campaign_id)
            ->where('id', '>', $unit->id) // Get units with ID greater than the current one
            ->where('exists_in_airtable', true)
            ->orderBy('id', 'asc') // Order by ID ascending
            ->first(); // Get the first matching unit
        $previousUnit = Unit::where('campaign_id', $unit->campaign_id)
            ->where('id', '<', $unit->id) // Get units with ID greater than the current one
            ->where('exists_in_airtable', true)
            ->orderBy('id', 'desc') // Order by ID ascending
            ->first(); // Get the first matching unit

        if ($request->wantsJson()) {
            return response()->json([
                'unit' => $unit,
                'nextUnit' => $nextUnit,
                'previousUnit' => $previousUnit,
                'outputimage' => $outputimage,
                'oneimage' => $oneimage
            ]);
        }

        return view('units.show', compact('unit', 'nextUnit', 'previousUnit', 'outputimage', 'oneimage'));
    }

    public function fetchUnit($id)
    {
        $unit = Unit::findOrFail($id);
        if (!$unit->exists_in_airtable) abort(404);

        // Get the latest output image for this unit
        $outputimage = Outputimage::where('unit_id', $unit->id)->latest()->first();

        return response()->json([
            'unit' => $unit,
            'outputimage' => $outputimage
        ]);
    }

    /*
     * Show the prompt used for the unit
     */
    public function prompt(Request $request, $id)
    {
        $unit = Unit::findOrFail($id); // Find the campaign, throw 404 if not found
        if (!$unit->exists_in_airtable) abort(404);


        if ($request->wantsJson()) {
            return response()->json([
                'unit' => $unit
            ]);
        }

        return view('units.prompt', compact('unit'));
    }
}
