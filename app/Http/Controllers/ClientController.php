<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
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
    public function show(Request $request, $id)
    {
        $client = Client::findOrFail($id); // Find the client, throw 404 if not found
        if (!$client->exists_in_airtable) abort(404);

        // Get all campaigns
        $campaigns = Campaign::where('client_id', $id)
            ->where('exists_in_airtable', true)
            ->latest()
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'client' => $client,
                'campaigns' => $campaigns
            ]);
        }

        return view('clients.show', compact('client', 'campaigns'));
    }
}
