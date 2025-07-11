<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function show(Request $request)
    {
        // Get active clients
        $clients = Client::where('exists_in_airtable', true)
            ->where('active', true)
            ->withCount('campaigns')
            ->orderBy('name')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'clients' => $clients
            ]);
        }

        return view('dashboard', compact('clients'));
    }
}
