<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AirtableController extends Controller
{
    //
    /*
     * POST
     * Update all airtable data, except for units (since that is slow to process)
     */
    public function allButUnits(Request $request)
    {
        // Important to do these in the right order, so that references (relationships) work correctly.
        Artisan::call('tradeschool:getfromairtable', ['table' => 'clients']);
        Artisan::call('tradeschool:getfromairtable', ['table' => 'styles']);
        Artisan::call('tradeschool:getfromairtable', ['table' => 'campaigns']);
        Artisan::call('tradeschool:getfromairtable', ['table' => 'providers']);
        Artisan::call('tradeschool:getfromairtable', ['table' => 'placements']);

        if ($request->wantsJson()) {
            return response()->json([], 204);
        }

        return redirect('/admin');
    }
    public function updateUnits(Request $request)
    {
        // This can be slow, especially if there are many images to import
        Artisan::call('tradeschool:getfromairtable', ['table' => 'units']);

        if ($request->wantsJson()) {
            return response()->json([], 204);
        }

        return redirect('/admin');
    }

}
