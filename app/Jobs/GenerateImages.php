<?php

namespace App\Jobs;

use App\Models\Genailog;
use App\Models\Unit;
use App\Models\Outputimage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $unit;

    public function __construct(Unit $unit)
    {
        $this->unit = $unit; // we are passing a $unit to this image processing Job
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get more Unit data
        $placement = $this->unit->placement;
        $images = $this->unit->images()->where('exists_in_airtable', true)->get();

        /*
         * OLA - 728x90 LeaderBoard
         */
        if ($placement->name === 'OLA - 320x50 Smartphone Banner')
        {
            Log::info('Generating 320x50');
            $image = $images->first(); // Get the first image
            $this->stability_outfill($this->unit, $image, 600, 600, 0, 0); // $unit, $image, left, right, up, down
        } elseif ($placement->name === 'OLA - 728x90 LeaderBoard')
        {
            Log::info('Generating 728x90');
            $image = $images->first(); // Get the first image
            $this->stability_outfill($this->unit, $image, 600, 600, 0, 0); // $unit, $image, left, right, up, down
        } elseif ($placement->name === 'OLA - 160x600 Skyscraper')
        {
            Log::info('Generating 160x600');
            $image = $images->first(); // Get the first image
            $this->stability_outfill($this->unit, $image, 0, 0, 600, 600); // $unit, $image, left, right, up, down
        } else {
            // Nothing to generate, so set it to complete.
            $this->unit->generation_images_complete = true;
            $this->unit->save();
        }
    }

    public function stability_outfill($unit, $image, $left, $right, $up, $down)
    {
        // API Settings
        $baseUrl = 'https://api.stability.ai/v2beta/stable-image/edit/outpaint';
        $apiKey = env('STABILIY_API_KEY', false);

        // Check if image exists
        if (!$image || empty($image->url_thumbnail_large)) {
            Log::error('No valid image URL found for unit ' . $this->unit->id);
            return;
        }
        $imageUrl = $image->url_thumbnail_large;

        // Calculate size
        /*
        // We may NOT need this?
        $imageInfo = getimagesize($imageUrl);
        $image->width = $imageInfo[0];
        $image->height = $imageInfo[1];
        Log::info('Width: ' . $image->width . ', height: ' . $image->height . '.');
        $new_width = round($image->height * (728 / 90));
        $outfill_amount_each_side = round(($new_width - $image->width)/2);
        Log::info('Left and right outfill px: ' . $outfill_amount_each_side);
        */

        /*
         * Outfill criteria.
         *
         * Note: for best quality use outpaint direction values smaller or equal to your source image dimensions.
         * between 0 and 2000 px in each direction.
         */
        $outputFormat = 'jpeg'; // jpeg png webp, default png
        $seed = 0; // 0 is a random seed
        $creativity = 0.5; // 0-1. Default 0.5. Controls the likelihood of creating additional details not heavily conditioned by the init image.
        /*
        * A strong, descriptive prompt that clearly defines elements, colors, and subjects will lead to better results.
        * To control the weight of a given word use the format (word:weight),
        * where word is the word you'd like to control the weight of and weight is a value between 0 and 1.
        * For example: The sky was a crisp (blue:0.3) and (green:0.8) would convey a sky that was blue and green, but more green than blue.
        */
        $prompt = '';


        try {
            // Get the image from S3
            $imageContent = file_get_contents($imageUrl);
            if ($imageContent === false) {
                throw new \Exception("Failed to fetch image from URL: $imageUrl");
            }

            /*
             * Business logic.
             * We are not using the above calculation. Because:
             * If we generate aspect ratio 320x50, from a square starter, it breaks.
             * It just goes crazy with hallucination, because the starting image is too small, there's not enough data to work from.
             * Instead, better to generate more limited outfill and use a mask in Figma.
             * This also gives the designer the ability to move the image around there.
             * This does assume we will always have square-ish starter images for these.
             * Stability warns about this in their docs: 'for best quality use outpaint direction values smaller or equal to your source image dimensions'
             */

            // Call the API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'image/*',
            ])->attach(
                'image', $imageContent, 'image.jpg'
            )->post($baseUrl, [
                'left' => $left,
                'right' => $right,
                'up' => $up,
                'down' => $down,
                'output_format' => $outputFormat,
                'seed' => $seed,
                'creativity' => $creativity,
                'prompt' => $prompt,
            ]);
            if ($response->successful()) {
                // Log for legal reasons
                Genailog::create([
                    'model' => $baseUrl,
                    'prompt' => 'Unit: ' . $unit->id . ' , Prompt: ' . $prompt . ', left: ' . $left. ', right: ' . $right. ', up: ' . $up. ', down: ' . $down,
                ]);

                // construct path
                $fileName = Str::random(60) .'.jpg';
                $s3_path = 'processed/' . $fileName;

                // Store the file in S3
                $stored = Storage::disk('s3')->put($s3_path, $response->body(), 'public');

                if ($stored) {
                    $s3_url = Storage::url($s3_path);
                    Log::info('Image created and stored in S3: ' . $s3_url);

                    // Now create a resized and optimized version of the generated image.
                    // Do we need this????

                    // Job done, update database. (We keep previous versions so that we can show the history if we want to.)
                    $outputImage = new Outputimage([
                        'unit_id' => $unit->id,
                        'image_id' => $image->id,
                        'url_generated' => $s3_url,
                    ]);
                    $outputImage->save();

                    $this->unit->generation_images_complete = true;
                    $this->unit->save();

                } else {
                    Log::error('Failed to store image in S3');
                }
            } else {
                Log::error('Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Error in image generation: ' . $e->getMessage());
        }
    }
}
