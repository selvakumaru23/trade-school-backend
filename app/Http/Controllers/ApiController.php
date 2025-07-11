<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Campaign;
use App\Models\Unit;
use Illuminate\Http\Request;

class ApiController extends Controller
{

    /*
     * /api/clients
     * list of clients
     */
    public function clients_index(Request $request)
    {
        // Check API Key
        if (!$this->validateBearerToken($request)) return response()->json(['error' => 'No or wrong API Key'], 401);

        // Get data
        $clients = Client::where('exists_in_airtable', true)
            ->where('active', true)
            ->get();

        // Some nice mapping
        $clients = $clients->map(function ($client) {
            return [
                'id' => $client->id,
                'name' => $client->name,
                //'isActive' => $client->active,
                //'airtableId' => $client->airtable_id
            ];
        });

        return $clients->toJson(JSON_PRETTY_PRINT);
    }
    /*
     * /api/clients/1/campaigns
     * list of campaigns for a specific client
     */
    public function campaigns_index(Request $request, Client $client)
    {
        // Check API Key
        if (!$this->validateBearerToken($request)) return response()->json(['error' => 'No or wrong API Key'], 401);

        // Load the campaigns
        $client->load(['campaigns' => function ($query) {
            $query->where('exists_in_airtable', true);
        }]);

        // Map campaigns data
        // (important to do this before we transform the $client into an array)
        $campaigns = $client->campaigns->map(function ($campaign) {
            return [
                'id' => $campaign->id,
                'name' => $campaign->name,
            ];
        });

        // Map client data
        $client = [
            'id' => $client->id,
            'name' => $client->name,
        ];

        return response()->json([
            'client' => $client,
            'campaigns' => $campaigns
        ], 200, [], JSON_PRETTY_PRINT);
    }

    /*
     * Function for replacing emopty values with 'empty', so that we overwrite whatever is in the Figma template with this value
     */
    public function replaceEmpty($value, $replacement = 'empty') {
        return ($value ?? '') === '' ? $replacement : $value;
    }


    /*
     * /api/campaigns/1/
     * Campaign detail with list of all units
     */
    public function campaigns_show(Request $request, Campaign $campaign)
    {
        // Check API Key
        if (!$this->validateBearerToken($request)) return response()->json(['error' => 'No or wrong API Key'], 401);

        // Load necessary relationships
        $campaign->load(['client', 'style', 'units' => function ($query) {
            $query->where('exists_in_airtable', true)->with('placement');
        }]);

        /*
         * Example JSON
         *
         * {
    "campaign": {
        "client": "",
        "dynamic_campaign_targetaudience": "lorem ipsum dolor target audience",
        "dynamic_campaign_goal": "lorem ipsum dolor target audience",
        "dynamic_campaign_copydirection": "lorem ipsum dolor target audience",
        "dynamic_campaign_visualdirection": "lorem ipsum dolor target audience"
    },
    "units": [

        {
            "dynamic_placement": "dynamic_meta_static_carousel1x1",
            "dynamic_headline": "Exclusive",
            "dynamic_cta": "Click Here",
            "dynamic_disclaimer": "Terms and conditions apply.",
            "dynamic_logo": "https://picsum.photos/1000",
            "dynamic_image": "https://picsum.photos/1000",
            "dynamic_url": "https://example.com",
            "dynamic_primary": "Exclusive",
            "dynamic_description": "Your go-to source for information.",
            "dynamic_subject": "Exclusive",
            "dynamic_body": "Find out all the details.",
            "dynamic_offer": "Special Offer",
            "dynamic_destination": "https://example.com",
            "dynamic_title": "Amazing Discovery"
        },
    ]
}
         *
         */

        // Map Campaign data

        $mappedCampaign = [
            'id' => $campaign->id,
            'dynamic_campaign_name' => $this->replaceEmpty($campaign->name),
            'dynamic_campaign_targetaudience' => $this->replaceEmpty($campaign->targetaudience),
            'dynamic_campaign_goal' => $this->replaceEmpty($campaign->goal),
            'dynamic_campaign_copydirection' => $this->replaceEmpty($campaign->copydirection),
            'dynamic_campaign_visualdirection' => $this->replaceEmpty($campaign->visualdirection),
            'dynamic_campaign_tradeschoolstrategy' => $this->replaceEmpty($campaign->tradeschoolstrategy),
            //'linkingDestination' => $campaign->linkingdestination,
            //'funnelPlacement' => $campaign->funnelplacement,
            //'featuredProducts' => $campaign->featuredproducts,
            //'airtableId' => $campaign->airtable_id,
        ];

        // Map Client data
        $mappedClient = [
            'id' => $campaign->client->id,
            'dynamic_client_name' => $this->replaceEmpty($campaign->client->name),
            //'active' => $campaign->client->active,
            //'airtableId' => $campaign->client->airtable_id,
        ];

        // Map Style data
        $mappedStyle = [
            'id' => $campaign->style->id,
            'name' => $campaign->style->name,
            'figma_page_name' => $campaign->style->figma_page_name,
            //'airtableId' => $campaign->style->airtable_id,
        ];

        // Create the hardcoded title ad unit. This "ad unit" creates a title ad unit and always shows up if the dynammic figma id exists
        $titleUnit = [
            'id' => 0,
            'placement' => [
                'placement_figma_id' => 'dynamic_titlepage',
            ],
        ];


        // Map Unit data
        $mappedUnits = $campaign->units->map(function ($unit) {

            // Use the generated_copy directly as it's already an array
            $generatedCopy = $unit->generated_copy ?? [];



            // Map the generated_copy fields to custom keys
            $mappedGeneratedCopy = [
                'dynamic_headline' => $this->replaceEmpty($generatedCopy['headline'] ?? null),
                'dynamic_headline1' => $this->replaceEmpty($generatedCopy['headline1'] ?? null),
                'dynamic_headline2' => $this->replaceEmpty($generatedCopy['headline2'] ?? null),
                'dynamic_headline3' => $this->replaceEmpty($generatedCopy['headline3'] ?? null),
                'dynamic_description' => $this->replaceEmpty($generatedCopy['description'] ?? null),
                'dynamic_primary' => $this->replaceEmpty($generatedCopy['meta_primary'] ?? null), // legacy
                'dynamic_meta_primary' => $this->replaceEmpty($generatedCopy['meta_primary'] ?? null),
                'dynamic_nextdoor_body' => $this->replaceEmpty($generatedCopy['nextdoor_body'] ?? null),
                'dynamic_nextdoor_offer' => $this->replaceEmpty($generatedCopy['nextdoor_offer'] ?? null),
                'dynamic_nextdoor_subject' => $this->replaceEmpty($generatedCopy['nextdoor_subject'] ?? null),
                'dynamic_body' => $this->replaceEmpty($generatedCopy['nextdoor_body'] ?? null), // legacy
                'dynamic_offer' => $this->replaceEmpty($generatedCopy['nextdoor_offer'] ?? null), // legacy
                'dynamic_subject' => $this->replaceEmpty($generatedCopy['nextdoor_subject'] ?? null), // legacy
                'dynamic_pinterest_description' => $this->replaceEmpty($generatedCopy['pinterest_description'] ?? null),
            ];

            // Add a copy of each field starting with 'export_'
            $mappedGeneratedCopy = array_merge(
                $mappedGeneratedCopy,
                array_combine(
                    array_map(fn($key) => 'export_' . $key, array_keys($mappedGeneratedCopy)),
                    array_values($mappedGeneratedCopy)
                )
            );

            /*
             * Decide which images to send to Figma
             */
            $imagesdata = [];
            if (in_array($unit->placement->figma_id, [
                'dynamic_ola_320x50_smartphonebanner',
                'dynamic_ola_728x90_leaderboard',
                'dynamic_ola_160x600_skyscraper']))
            {
                // 1. For GenAI placements, get most recent outputimage
                $imagesdata = [
                    'dynamic_image' => $unit->outputimages()->latest()->first()->url_generated ?? null,
                    'export_dynamic_image' => $unit->outputimages()->latest()->first()->url_generated ?? null
                ];
            } elseif (in_array($unit->placement->figma_id, [
                'dynamic_ola_300x250_mediumrectangle',
                'dynamic_nextdoor_static_display',
                'dynamic_pinterest_static_standard']))
            {
                // 2. For simple placements, use the optimized (512px) input image
                $imagesdata = [
                    'dynamic_image' => $unit->images()->latest()->first()->url_thumbnail_large ?? null,
                    'export_dynamic_image' => $unit->images()->latest()->first()->url_thumbnail_large ?? null
                ];
            } elseif (in_array($unit->placement->figma_id, [
                'dynamic_meta_static_carousel9x16',
                'dynamic_meta_static_carousel1x1']))
            {
                // 3. For Carousel placements, use 3 images
                $imagesdata = [
                    'dynamic_image' => $unit->images()->latest()->first()->url_thumbnail_large ?? null,
                    'dynamic_image1' => $unit->images()->latest()->first()->url_thumbnail_large ?? null,
                    'dynamic_image2' => $unit->images()->latest()->skip(1)->first()->url_thumbnail_large ?? null,
                    'dynamic_image3' => $unit->images()->latest()->skip(2)->first()->url_thumbnail_large ?? null,
                    'export_dynamic_image' => $unit->images()->latest()->first()->url_thumbnail_large ?? null,
                    'export_dynamic_image1' => $unit->images()->latest()->first()->url_thumbnail_large ?? null,
                    'export_dynamic_image2' => $unit->images()->latest()->skip(1)->first()->url_thumbnail_large ?? null,
                    'export_dynamic_image3' => $unit->images()->latest()->skip(2)->first()->url_thumbnail_large ?? null,
                ];
            }


            return array_merge([
                'id' => $unit->id,
                'name' => $this->replaceEmpty($unit->name),
                'uniqueId' => $unit->uniqueid,
                'dynamic_filename' => $unit->filename,
                'dynamic_cta' => $unit->cta,
                // For units with 3 CTAs, we repeat the CTA because we can only use each variable once in the Figma slide
                'dynamic_cta1' => $this->replaceEmpty($unit->cta),
                'dynamic_cta2' => $this->replaceEmpty($unit->cta),
                'dynamic_cta3' => $this->replaceEmpty($unit->cta),
                'export_dynamic_cta' => $this->replaceEmpty($unit->cta),
                'export_dynamic_cta1' => $this->replaceEmpty($unit->cta),
                'export_dynamic_cta2' => $this->replaceEmpty($unit->cta),
                'export_dynamic_cta3' => $this->replaceEmpty($unit->cta),
                'dynamic_url' => $this->replaceEmpty($unit->linkingdestination), // legacy
                'dynamic_url1' => $this->replaceEmpty($unit->linkingdestination), // legacy, for carousels
                'dynamic_url2' => $this->replaceEmpty($unit->linkingdestination), // legacy, for carousels
                'dynamic_url3' => $this->replaceEmpty($unit->linkingdestination), // legacy, for carousels
                'dynamic_linkingdestination' => $this->replaceEmpty($unit->linkingdestination),
                'dynamic_linkingdestination1' => $this->replaceEmpty($unit->linkingdestination),
                'dynamic_linkingdestination2' => $this->replaceEmpty($unit->linkingdestination),
                'dynamic_linkingdestination3' => $this->replaceEmpty($unit->linkingdestination),
                'dynamic_copydirection' => $this->replaceEmpty($unit->copydirection), // legacy
                'dynamic_adunit_copydirection' => $this->replaceEmpty($unit->copydirection),
                'dynamic_visualdirection' => $this->replaceEmpty($unit->visualdirection), // legacy
                'dynamic_adunit_visualdirection' => $this->replaceEmpty($unit->visualdirection),
                'dynamic_tradeschoolstrategy' => $this->replaceEmpty($unit->tradeschoolstrategy),
                'placement' => [
                    'placement_name' => $this->replaceEmpty($unit->placement->name),
                    'placement_figma_id' => $unit->placement->figma_id,
                    'category' => $unit->placement->category,
                ],
            ], $mappedGeneratedCopy, $imagesdata
            );
        });

        // Prepend the title unit to the mapped units
        $mappedUnits->prepend($titleUnit);

        // Combine all mapped data
        $data = [
            'campaign' => $mappedCampaign,
            'client' => $mappedClient,
            'style' => $mappedStyle,
            'units' => $mappedUnits,
        ];

        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    }


    /*
     * Check for API Key
     */
    protected function validateBearerToken(Request $request)
    {
        if ($request->hasHeader('Authorization')) {
            $bearerToken = $request->bearerToken();
            if ($bearerToken && $bearerToken === env('API_KEY')) {
                return true;
            }
        }
        return false;
    }
}
