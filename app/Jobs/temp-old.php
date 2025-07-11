<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Unit;
use App\Models\Campaign;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Exceptions\ErrorException;

class GenerateCopy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Unit $unit;

    /**
     * Create a new job instance.
     */
    public function __construct(Unit $unit)
    {
        // We generate copy for every individual unit as a separate job
        $this->unit = $unit;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /*
         * Let's generate some copy
         *
         * Starting with these fields:
         * headline: 45 characters
         * description: 18 characters
         */
        $campaign = $this->unit->campaign;
        /*
         * For reference:
         * See https://docs.google.com/spreadsheets/d/1kH76hP3Wg-CDfZTk1c7iQ_cZs2NgdRm0woSgLYT904g/edit?gid=933992623#gid=933992623
         * Full List of generated copy fields for various supported placement:
            $dynamic_headline	34
            $dynamic_pinterest_description	450
            $dynamic_nextdoor_subject	60
            $dynamic_nextdoor_body	600
            $dynamic_nextdoor_offer	40
            $dynamic_headline1	34
            $dynamic_headline2	34
            $dynamic_headline3	34
            $dynamic_meta_primary	80
         */
        $prompt = "
You are an expert copywriter working on an advertising campaign.
Your task is to write copy using the following brief, for use in online advertising.

Follow these directions about writing copy closely.

Ad unit copy direction: (this is the most important direction)
[starting ad unit copy direction]" . $this->unit->copydirection . "[ending ad unit copy direction]

The following directions for copy writing also matter but are less important.

Ad unit strategic direction:
[starting ad unit strategic direction]" . $this->unit->tradeschoolstrategy . "[ending ad unit strategic direction]

Overall campaign-level copy direction:
[starting campaign copy direction]" . $campaign->copydirection . "[ending campaign copy direction]

Taking into account the above direction,
first, write copy for an advertising 'headline', around 30 to 35 characters.
Then, write a second headline version as 'headline2', around 30 to 35 characters.
Then, write a third headline version as 'headline3', around 30 to 35 characters.
Then, write copy for an advertising 'description', around 450 characters.
Then, write copy for the subject of a nextdoor post as 'nextdoor_subject', around 60 characters.
Then, write copy for the body of a nextdoor post as 'nextdoor_body', around 500 characters.
Then, write copy for a nextdoor offer as 'nextdoor_offer', which should encourage people to click the button on nextdoor, around 40 characters.
Then, write copy for the primary text in a facebook ad unit as 'meta_primary', around 80 characters.

Do not include a call to action in the text.
Doublecheck the character count for each.

Return valid json.
            ";
        Log::info('Calling OpenAI');
        // Save the prompt
        $this->unit->generation_prompt_used = $prompt;
        $this->unit->save();

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o',
                'temperature' => 1,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);
            // OpenAI returns backticks to indicate json is code, so remove backticks ```
            $content = $response->choices[0]->message->content;
            $content = trim($content, '`'); // Remove backticks and any extra whitespace
            $content = trim($content, 'json');

            $output = json_decode($content, true); // Now decode the clean JSON

        } catch (ErrorException $e) {
            // Handle OpenAI API errors
            $error = $e->toArray();
            Log::error("OpenAI API Error: " . $error['error']['message']);

            // Additional error handling based on $error['error']['type'] or 'code'
            if ($error['error']['type'] === 'invalid_request_error') {
                // Handle invalid request errors
            } elseif ($error['error']['type'] === 'server_error') {
                // Handle server errors (e.g., retry)
                // create the job again in the queue...

            } else {
                // Handle other types of errors
            }

        } catch (\Exception $e) {
            // Handle other exceptions (e.g., network issues, timeouts)
            Log::error("General Error: " . $e->getMessage());
        }


        if ($output !== null) {
            Log::info('Output example from API:' . $output['headline']);
            // Save to DB
            $content = [
                'headline' => $output['headline'],
                'description' => $output['description'],
            ];
            $this->unit->generated_copy = $content;
            $this->unit->generation_copy_complete = true;
            $this->unit->save();
        } else {
            Log::error("Error: output was NULL");
            Log::debug('Raw API response:', (array) $response);
        }



    }
}
