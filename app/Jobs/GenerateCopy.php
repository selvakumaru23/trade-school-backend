<?php

namespace App\Jobs;

use App\Models\Unit;
use App\Models\Genailog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class GenerateCopy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $unit;

    public function __construct(Unit $unit)
    {
        $this->unit = $unit;
    }

    public function handle()
    {
        Log::info('Starting generation for 1 unit.');

        try {
            $campaign = $this->unit->campaign;
            $prompt = $this->generatePrompt($this->unit, $campaign);

            $this->unit->generation_prompt_used = $prompt;
            $this->unit->save();

            $response = $this->callOpenAI($prompt);
            $output = $this->processResponse($response);

            if ($output !== null) {
                $this->saveGeneratedCopy($this->unit, $output);
            } else {
                Log::error("Error: output was NULL");
                throw new \Exception("Output was null");
            }
        } catch (\Exception $e) {
            Log::error("GenerateCopy Job Failed: " . $e->getMessage());
            $this->unit->generation_copy_complete = false;
            $this->unit->save();
            // @todo dispatch a job for this unit again.
            throw $e;
        }
    }

    protected function generatePrompt($unit, $campaign)
    {
        /*
         * Notes on prompt
         * - we don't have to use the campaign copy direction. That's used to create each unit copy direction.
         * - we don't use campaign visual direction or unit visual direction (same reasons)
         *
         * @todo only get fields that are needed for this Placement
         */

        $provider = $unit->placement->provider; // Get the provider
        $campaign = $unit->campaign; // Get the campaign

        /*
         * Business logic:
         * For OLA campaigns, that only need a headline, we only generate that.
         * Because it is much faster to only ask the model to generate 1 thing.
         * For all other things (to keep the logic here simple), we generate all fields.
         */
        $prompt = "
Your task is to write copy using the following brief, for use in online advertising.
Follow the following directions about writing copy closely.
If there are reasons to believe mentioned, be sure to write about at least one of the reasons-to-believe.
If there are tone of voice instructions, follow them closely.

Ad unit copy direction:
[starting ad unit copy direction]" . $unit->copydirection . "[ending ad unit copy direction]

Ad unit strategic direction:
[starting ad unit strategic direction]" . $unit->tradeschoolstrategy . "[ending ad unit strategic direction]

Ad campaign direction: the goal of this campaign:
[campaign goal]" . $unit->campaign->goal . "[ending campaign goal]

Ad campaign target audience: write the copy so that it appeals to this audience.
[campaign target audience]" . $unit->campaign->targetaudience . "[ending campaign target audience]


Taking into account the above direction and instructions,

Write the copy for an advertising 'headline', to be used in an online ad, around 30 to 35 characters.";
        if ($provider->name != "OLA") {
            $prompt .= "
Then, write a second headline version as 'headline2', around 30 to 35 characters.
Then, write a third headline version as 'headline3', around 30 to 35 characters.
Then, write copy for an advertising 'description', around 450 characters.
Then, write copy for the subject of a nextdoor post as 'nextdoor_subject', around 60 characters.
Then, write copy for the body of a nextdoor post as 'nextdoor_body', around 500 characters.
Then, write copy for a nextdoor offer as 'nextdoor_offer', which should encourage people to click the button on nextdoor, around 40 characters.
Then, write copy for the primary text in a facebook ad unit as 'meta_primary', around 80 characters.
Then, write copy for a description for pinterest, using many SEO keywords, as 'pinterest_description', around 450 characters.
";
        }
        $prompt .= "

Do not include a call to action in the text.
Doublecheck the character count for each.

Return valid json.
        ";
        return $prompt;
    }

    protected function callOpenAI($prompt)
    {
        // log this api call for legal
        Genailog::create([
            'model' => 'gpt-4o-mini, temperature 1.15',
            'prompt' => $prompt
        ]);

        return OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'temperature' => 1.15, // between 0 and 2. Higher values like 0.8 will make the output more random, while lower values like 0.2 will make it more focused and deterministic.
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);
    }

    protected function processResponse($response)
    {
        $content = $response->choices[0]->message->content;
        $content = trim($content, '`');
        $content = trim($content, 'json');
        return json_decode($content, true);
    }

    protected function saveGeneratedCopy($unit, $output)
    {
        Log::info('Saving generated copy, example headline: ' . ($output['headline'] ?? 'No headline generated'));
        $content = [
            'headline' => $output['headline'] ?? null,
            'headline1' => $output['headline'] ?? null, // same as headline
            'headline2' => $output['headline2'] ?? null,
            'headline3' => $output['headline3'] ?? null,
            'description' => $output['description'] ?? null,
            'nextdoor_subject' => $output['nextdoor_subject'] ?? null,
            'nextdoor_body' => $output['nextdoor_body'] ?? null,
            'nextdoor_offer' => $output['nextdoor_offer'] ?? null,
            'meta_primary' => $output['meta_primary'] ?? null,
            'pinterest_description' => $output['pinterest_description'] ?? null,
        ];
        $unit->generated_copy = $content;
        $unit->generation_copy_complete = true; // Update unit status in db
        $unit->save();
    }
}
