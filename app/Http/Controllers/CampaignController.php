<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Unit;
use App\Models\Image;
use App\Jobs\GenerateCopy;
use App\Jobs\GenerateImages;

use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /*
     * Overview page
     */
    public function index()
    {
        return redirect('/dashboard');
    }
    /*
     * Detail page
     */
    public function show($id)
    {
        $campaign = Campaign::findOrFail($id); // Find the campaign, throw 404 if not found
        if (!$campaign->exists_in_airtable) abort(404);

        // get ad units, with images IF they exist_in_airtable
        $units = Unit::where('campaign_id', $campaign->id)
            ->where('exists_in_airtable', true)
            ->with(['placement', 'images' => function ($query) {
                $query->where('exists_in_airtable', true);
            }])
            ->get();

        /*
         * Kick of copy+image generation jobs for each unit where it's not done yet
         *
         * Note: we could be running these Jobs in the background from the moment we import a campaign,
         * but then it wouldn't look cool and active on this page. So instead we only start the workers
         * once someone gets to this page.
         */
        foreach ($units as $unit) {
            if (!$unit->generation_copy_complete) { // if not done yet, kick off a job
                GenerateCopy::dispatch($unit->load('campaign')); // This queues up the job, and makes sure we can access the related campaign easily as well
            }
            // Also images
            if (!$unit->generation_images_complete)
            { // if not done yet, kick off a job
                GenerateImages::dispatch($unit->load('campaign')); // This queues up the job, and makes sure we can access the related campaign easily as well
            }
            // hello
        }

        return view('campaigns.show', compact('campaign', 'units'));
    }

    public function fetchUnits($id)
    {
        $campaign = Campaign::findOrFail($id);
        if (!$campaign->exists_in_airtable) abort(404);

        $units = Unit::where('campaign_id', $campaign->id)
            ->where('exists_in_airtable', true)
            ->with(['placement', 'latestOutputimage', 'images' => function ($query) {
                $query->where('exists_in_airtable', true);
            }])
            ->get();

        return response()->json([
            'campaign' => $campaign,
            'units' => $units
        ]);
    }
}
