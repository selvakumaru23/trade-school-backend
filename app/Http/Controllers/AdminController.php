<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateCopy;
use App\Jobs\GenerateImages;
use App\Models\Campaign;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Genailog;

class AdminController extends Controller
{
    /*
     * Display main admin screen
     */
    public function show()
    {
        return view('admin.show');
    }
    /*
     * Reset all units in a campaign to "not done yet"
     * (which upon visiting /campaign/{id} will then kick off the Jobs to generate them.


    NO LONGER USED


    public function resetcampaign($id)
    {
        // Reset all the content we generated, so that it starts again
        $campaign = Campaign::findOrFail($id);
        $campaign->units()->update([
            'generation_copy_complete' => false,
            'generation_images_complete' => false,
            'generated_copy' => json_encode([]),
        ]);
        return redirect('/campaigns/' . $campaign->id);
    }
    */
    /*
     * Reset copy for all units in a campaign
     */
    public function resetcampaigncopy(Request $request, $id)
    {
        // Reset all the content we generated, so that it starts again
        $campaign = Campaign::findOrFail($id);
        $campaign->units()->update([
            'generation_copy_complete' => false,
            'generated_copy' => json_encode([]),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'campaign_id' => $campaign->id
            ]);
        }

        return redirect('/campaigns/' . $campaign->id);
    }
    /*
     * Reset copy for all units in a campaign
     */
    public function resetcampaignimages(Request $request, $id)
    {
        // Reset all the content we generated, so that it starts again
        $campaign = Campaign::findOrFail($id);
        $campaign->units()->update([
            'generation_images_complete' => false,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'campaign_id' => $campaign->id
            ]);
        }

        return redirect('/campaigns/' . $campaign->id);
    }
    /*
     * Reset the copy and images for 1 unit
     * and put a job in the queue
     */
    public function resetunitcopy(Request $request, $id)
    {
        // Reset the content we generated, so that it starts again
        Log::info('Starting re-generate copy for 1 unit');
        $unit = Unit::findOrFail($id);
        $unit->update([
            'generation_copy_complete' => false,
            'generated_copy' => json_encode([]),
        ]);
        // Dispatch Jobs
        GenerateCopy::dispatch($unit->load('campaign'));

        if ($request->wantsJson()) {
            return response()->json([
                'unit_id' => $unit->id
            ]);
        }

        return redirect('/units/' . $unit->id);
    }
    public function resetunitimages(Request $request, $id)
    {
        // Reset the content we generated, so that it starts again
        Log::info('Starting re-generate images for 1 unit');
        $unit = Unit::findOrFail($id);
        $unit->update([
            'generation_images_complete' => false,
        ]);
        // Dispatch Jobs
        GenerateImages::dispatch($unit->load('campaign'));

        if ($request->wantsJson()) {
            return response()->json([
                'unit_id' => $unit->id
            ]);
        }

        return redirect('/units/' . $unit->id);
    }
    /*
     * Show log of API calls
     */
    public function genaihistory(Request $request)
    {
        $genailogs = Genailog::latest()->paginate(30);

        if ($request->wantsJson()) {
            return response()->json([
                'genailogs' => $genailogs
            ]);
        }

        return view('admin.genaihistory', compact('genailogs'));
    }
}
