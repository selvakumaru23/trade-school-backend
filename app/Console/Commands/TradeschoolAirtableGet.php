<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Airtable;
use TANIOS\Airtable\AirtableException;
use App\Models\Client;
use App\Models\Campaign;
use App\Models\Provider;
use App\Models\Image;
use App\Models\Style;
use App\Models\Unit;
use App\Models\Placement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class TradeschoolAirtableGet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tradeschool:getfromairtable {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get data from airtable and update our database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        set_time_limit(280); // Sets the time limit to execute this longer than the default 30 secs (in seconds)

        $table = $this->argument('table'); // Get the argument value
        Log::info('Starting airtable sync for table ' . $table);
        // Check if table name given is valid. Table names are defined in config/airtable
        $allowedTables = ['clients', 'campaigns', 'styles', 'units', 'placements', 'providers'];
        if (!in_array($table, $allowedTables)) {
            $this->error('Error. Table name should be one of these: clients, campaigns, styles, units, placements or providers.');
            return 1;
        }

        // Get the data

        $data = Airtable::table($table)
            ->addParam('returnFieldsByFieldId', true)
            ->all();




        /*
         * Reference: field IDs for table names:
         * From: https://airtable.com/app9T7cVbGfFQPz0A/api/docs#curl/table:provider
         * We use IDs so that, if a user changes the name of a field, it doesn't break things here
         *
         * CLIENTS:
         * fldWQLNclVYkjU6o4 = Name
         * fldlNRKq2As8sPud7 = Active
         * fldxUJ4DvprH6FYbA = campaigns
         *
         * STYLES:
         * fldqskRMkgzuLGheb = Name
         * fldpZlPoDPl6DoB1i = Description
         * fldF8ylouElO70ESn = Campaigns (array)
         * fldpwiYNRY2IieRaz = Figma page name
         *
         * AD UNITS:
         * fldO3Shuz5h0LK7Vs = id
         * fldOSXRlftZhYBQSV = Campaign (array)
         * fldslcRbTiiMwz0AQ = file name
         * fldy23rfTCNP1sqzf = placement (array)
         * fld1U73shtwBA1uBg = images (array)
         * fldd0wMq2Vy4WGBwS = CTA
         * fldzCz1jDymsib6bR = linking destination
         * fldF1Nv1n8sx2eXO9 = ad unit copy direction
         * fldjfNl9Z62o6fIEC = ad unit visual direction
         * fldWEwFslLq5bePkM = tradeschool strategy
         *
         * PLACEMENTS:
         * fldBKiwo3fQGfVYrK = name
         * fldyzdsp0dH6tfoh0 = category
         * fld0hNnckpnsVadNL = provider (array)
         * fldwN35giv2gjFPPJ = media type
         * fldnGuewS0e2cWEmZ = platform type
         * fldnSvNuRUSftwKeO = ad specs
         * fldBixnmhDBf6r7z7 = tradeschool strategy
         * fldDwzjVvpM7G5tsk = products (array with IDs of ad units)
         * fld3QB9FOSA9VJXyM = KPI
         * fldYHwM0gsRq3MiZP = campaigns (array)
         *
         * CAMPAIGNS:
         * fldekRCD90hqJNFfH = client (array)
         * fldH9Sd14f5qOMOLO = style (array)
         * fldjx6GJzhrRydmjx = name
         * fldQDxMqjsBSfNL1q = target audience
         * fldkuQowZpfKULyPK = campaign goal
         * fld06ZxZscw0RgDIs = campaign copy direction
         * fldKvhhdpKK1L0ThA = campaign visual direction
         * fldp0VN1lY5svWwPK = tradeschool strategy
         * fld52CoVkdagmkYy7 = linking destinations
         * fldi6AWXXAodnktJp = funnel placement
         * fldkMm6AeRL9HfCNR = featured products
         * fldKPztoUqUDo3ESA = attached images (array)
         * We do not use the following fields...
         * fldpevO6F1gmfgZHg = placement (array)
         *
         *
         * PROVIDER:
         * fldPtPWXTyt2eSeDo = Name
         *
         *
         */
        $data = json_decode($data, true);
        $count = 0;
        $airtable_ids = [];
        foreach ($data as $record) {
            $airtable_ids[] = $record['id']; // Keep a list of the airtable ids so that we can afterwards loop through and delete ones that were deleted in airtable
            if ($table == 'clients')
            {
                // Business Logic for all objects: object without a "name" (the first column in Airtable) is skipped
                if (!isset($record['fields']['fldWQLNclVYkjU6o4'])) {
                    $message = 'Skipping because we have no name (the first column).';
                    $this->info($message);
                    Log::info($message);
                    continue; // skip to the next
                }

                $fieldmap = [
                    'airtable_id' => $record['id'],
                    'name' => $record['fields']['fldWQLNclVYkjU6o4'],
                    'active' => $record['fields']['fldlNRKq2As8sPud7'] ?? false,
                ]; // Map the fields in the data to be compatible with our table
                $object = Client::firstOrNew(['airtable_id' => $record['id']]);
                $object->fill($fieldmap);
                $object->save();

            } elseif ($table == 'campaigns') {
                // Business Logic for all objects: object without a "name" (the first column in Airtable) is skipped
                if (!isset($record['fields']['fldjx6GJzhrRydmjx'])) {
                    $message = 'Skipping because we have no name (the first column).';
                    $this->info($message);
                    Log::info($message);
                    continue; // skip to the next
                }
                // Business Logic: a campaign MUST have a style and a client, or we skip it.
                if (!isset($record['fields']['fldekRCD90hqJNFfH'])) {
                    $message = 'Skipping campaign because it has no Client ID.';
                    $this->info($message);
                    Log::info($message);
                    continue; // skip to the next
                }
                if (!isset($record['fields']['fldH9Sd14f5qOMOLO'])) {
                    $message = 'Skipping campaign because it has no Style ID.';
                    $this->info($message);
                    Log::info($message);
                    continue; // skip to the next
                }
                // Get the relationship IDs from our database
                $client = Client::where('airtable_id',  $record['fields']['fldekRCD90hqJNFfH'][0])->first();
                $style = Style::where('airtable_id',  $record['fields']['fldH9Sd14f5qOMOLO'][0])->first();

                // Map the fields in the data to be compatible with our table
                $fieldmap = [
                    'airtable_id' => $record['id'],
                    'name' => $record['fields']['fldjx6GJzhrRydmjx'],
                    'client_id' => $client->id,
                    'style_id' => $style->id,
                    'targetaudience' => $record['fields']['fldQDxMqjsBSfNL1q'] ?? '',
                    'goal' => $record['fields']['fldkuQowZpfKULyPK'] ?? '',
                    'copydirection' => $record['fields']['fld06ZxZscw0RgDIs'] ?? '',
                    'visualdirection' => $record['fields']['fldKvhhdpKK1L0ThA'] ?? '',
                    'tradeschoolstrategy' => $record['fields']['fldp0VN1lY5svWwPK'] ?? '',

                ];
                $object = Campaign::firstOrNew(['airtable_id' => $record['id']]);
                $object->fill($fieldmap);
                $object->save();

            }  elseif ($table == 'styles') {
                // Business Logic for all objects: object without a "name" (the first column in Airtable) is skipped
                if (!isset($record['fields']['fldqskRMkgzuLGheb'])) {
                    $message = 'Skipping because we have no name (the first column).';
                    $this->info($message);
                    Log::info($message);
                    continue; // skip to the next
                }
                $fieldmap = [
                    'airtable_id' => $record['id'],
                    'name' => $record['fields']['fldqskRMkgzuLGheb'],
                    'figma_page_name' => $record['fields']['fldpwiYNRY2IieRaz'] ?? 'empty',
                ]; // Map the fields in the data to be compatible with our table
                $object = Style::firstOrNew(['airtable_id' => $record['id']]);
                $object->fill($fieldmap);
                $object->save();

            }  elseif ($table == 'units') {
                // Business Logic for all objects: object without a "name" (the first column in Airtable) is skipped
                if (!isset($record['fields']['fldx9uaZUD2ARvogI'])) {
                    $message = 'Skipping unit because we have no name (the first column).';
                    $this->info($message);
                    Log::info($message);
                    continue; // skip to the next
                }
                // Business Logic: a unit MUST have a campaign and a placement, or we skip it.
                if (!isset($record['fields']['fldOSXRlftZhYBQSV'])) {
                    $message = 'Skipping unit because it has no Campaign ID.';
                    $this->info($message);
                    Log::info($message);
                    continue; // skip to the next
                }
                if (!isset($record['fields']['fldy23rfTCNP1sqzf'])) {
                    $message = 'Skipping unit because it has no Placement ID.';
                    $this->info($message);
                    Log::info($message);
                    continue; // skip to the next
                }
                // Get the relationship IDs from our database (they might not exist in there if they're incomplete)
                if (!$campaign = Campaign::where('airtable_id',  $record['fields']['fldOSXRlftZhYBQSV'][0])->first()) {
                    $message = 'Unit ' . $unit->id . '. Skipping unit because its campaign is incomplete (does it have a client and style?) and has not been imported into our db.';
                    $this->info($message);
                    Log::info($message);
                    continue; // skip to the next
                }
                if (!$placement = Placement::where('airtable_id',  $record['fields']['fldy23rfTCNP1sqzf'][0])->first()) {
                    $message = 'Unit ' . $unit->id . '. Skipping unit because its placement is incomplete (does it have a provider?) and has not been imported into our db.';
                    $this->info($message);
                    Log::info($message);
                    continue; // skip to the next
                }

                // Map the fields in the data to be compatible with our table
                $fieldmap = [
                    'airtable_id' => $record['id'],
                    'name' => $record['fields']['fldx9uaZUD2ARvogI'],
                    'campaign_id' => $campaign->id ?? 0,
                    'placement_id' => $placement->id ?? 0,
                    'uniqueid' => $record['fields']['fldO3Shuz5h0LK7Vs'] ?? '',
                    'filename' => $record['fields']['fldslcRbTiiMwz0AQ'] ?? '',
                    'cta' => $record['fields']['fldd0wMq2Vy4WGBwS'] ?? '',
                    'linkingdestination' => $record['fields']['fldzCz1jDymsib6bR'] ?? '',
                    'copydirection' => $record['fields']['fldF1Nv1n8sx2eXO9'] ?? '',
                    'visualdirection' => $record['fields']['fldjfNl9Z62o6fIEC'] ?? '',
                    'tradeschoolstrategy' => $record['fields']['fldWEwFslLq5bePkM'] ?? '',
                ];
                $object = Unit::firstOrNew(['airtable_id' => $record['id']]);

                // If it's a new object, initialize generated_copy (or we get a db error)
                if (!$object->exists) {
                    $object->generated_copy = [];
                }

                $object->fill($fieldmap);
                $object->save();
                $unit = $object; // for readability below

                /*
                 * Handle images.
                 *
                 */
                if (isset($record['fields']['fld1U73shtwBA1uBg'])) // if we have an images key
                {
                    $images = $record['fields']['fld1U73shtwBA1uBg']; // for readability

                    // Get all existing images for this unit, so we can soft delete the ones no longer there later on..
                    $existingImages = Image::where('unit_id', $unit->id)->get();
                    $updatedImageIds = [];

                    $this->info('Start loop over images for this Unit ID ' . $unit->id);
                    Log::info('Unit ' . $unit->id . '. Start loop over images.');
                    foreach($images as $image) {
                        /*
                         * Reference:
                        $id = $image['id']; // airtable_id
                        $url = $image['url'];
                        $width = $image['width'];
                        $height = $image['height'];
                        $filename = $image['filename'];
                        $size = $image['size'];
                        $type = $image['type'];
                        if (isset($image['thumbnails'])) {
                            $smallThumbnailUrl = $image['thumbnails']['small']['url'];
                            $largeThumbnailUrl = $image['thumbnails']['large']['url'];
                            $fullThumbnailUrl = $image['thumbnails']['full']['url'];
                        }
                        */

                        $airtable_id = $image['id'];
                        $updatedImageIds[] = $airtable_id; // make a list for soft-deleting later

                        /*
                         * Some business/engineering logic:
                         * In Airtable, you can copy a row, and it copies the images, and you can have the same image with the same airtable ID used in multiple rows.
                         * So here we check to see if we already have this image with airtable_id FOR THIS UNIT.
                         * This is strangely a little tricky, logically. We don't want a many to one relationship here, because we'll be using genAI on those images, but Airtable does have a many to one relationship.
                         * So we duplicate the image and create a 1 to 1 relationship.
                         */
                        if (Image::where('airtable_id', $airtable_id)->where('unit_id', $unit->id)->exists()) {
                            // If an image with this airtableID exists **for this unit**, then we can skip
                            $message = 'Unit ' . $unit->id . '. Skipping image because already have it (in db for same unit id and with airtableid ' .  $airtable_id . ')';
                            $this->info($message);
                            Log::info($message);
                            continue; // skip to the next
                        } elseif (($image['type'] != 'image/jpeg') AND ($image['type'] != 'image/webp')) {
                            // business logic: only jpg or webp images allowed
                            $message = 'Unit ' . $unit->id . '. Skipping file because type is not jpg or webp, but is -'. $image['type'] . '-';
                            $this->info($message);
                            Log::info($message);
                            continue; // skip to the next
                        } else {

                            /*
                             * IF we have not yet copied the files into S3, in other words, we do not have ANY image with this airtable id
                             * So we only copy each image once to S3, but we create separate DB rows for every image to every unit.
                             * So multiple DB rows images can point to the same file in S3.
                            */
                            if (Image::where('airtable_id', $airtable_id)->doesntExist()) {
                                // Copy and store image files in S3
                                $startTime = Date::now();
                                if ($image['type'] == 'image/jpeg') $extension = '.jpg';
                                if ($image['type'] == 'image/webp') $extension = '.webp';
                                Storage::disk('s3')->put('originals/' . $image['id'] . $extension, file_get_contents($image['url']), 'public');
                                $endTime = Date::now();
                                $elapsedTime = $startTime->diffInMilliseconds($endTime);
                                $message = 'Saving to S3, img '. $image['type'] . ' for Unit ID ' . $unit->id . ' took ' . $elapsedTime . ' ms';
                                $this->info($message);
                                Log::info($message);
                                if (isset($image['thumbnails'])) {
                                    Storage::disk('s3')->put('originals/thumbs/small/' . $image['id'] . '.jpg', file_get_contents($image['thumbnails']['small']['url']), 'public');
                                    Storage::disk('s3')->put('originals/thumbs/large/' . $image['id'] . '.jpg', file_get_contents($image['thumbnails']['large']['url']), 'public');
                                    Storage::disk('s3')->put('originals/thumbs/full/' . $image['id'] . '.jpg', file_get_contents($image['thumbnails']['full']['url']), 'public');
                                } else {
                                    Log::error('Unit ' . $unit->id . '. The image did not have thumbnails! And we do not handle that well.');
                                }
                            }


                            // Create in DB
                            $url_o = Storage::disk('s3')->url('originals/' . $image['id'] . '.jpg');
                            $url_ts = Storage::disk('s3')->url('originals/thumbs/small/' . $image['id'] . '.jpg');
                            $url_tl = Storage::disk('s3')->url('originals/thumbs/large/' . $image['id'] . '.jpg');
                            $url_tf = Storage::disk('s3')->url('originals/thumbs/full/' . $image['id'] . '.jpg');
                            $img = new Image();
                            $img->airtable_id = $airtable_id;
                            $img->unit_id = $unit->id;
                            $img->url_original = $url_o;
                            $img->url_thumbnail_small = $url_ts;
                            $img->url_thumbnail_large = $url_tl;
                            $img->url_thumbnail_full = $url_tf;
                            $img->save();

                            $message = 'Unit ' . $unit->id . '. Created image ID '. $img->id . ' with airtable ID ' . $airtable_id;
                            $this->info($message);
                            Log::info($message);
                        }
                    }

                    // Now lets see if we have to soft delete some images that had been removed from Airtable
                    foreach ($existingImages as $existingImage) {
                        if (!in_array($existingImage->airtable_id, $updatedImageIds)) {
                            $existingImage->exists_in_airtable = false;
                            $existingImage->save();
                            // log
                            $message = 'Image ' . $existingImage->id . ' with Airtable ID ' . $existingImage->airtable_id . ' no longer exists in Airtable and was soft deleted';
                            $this->info($message);
                            Log::info($message);
                        }
                    }

                } else {
                    // There are no images for this unit
                    $message = 'No image key for unit '. $unit->id;
                    $this->info($message);
                    Log::info($message);
                    // Set all existing images for this unit to not exist in Airtable, in case they were all deleted
                    Image::where('unit_id', $unit->id)->update(['exists_in_airtable' => false]);
                }

            }  elseif ($table == 'placements') {
                // Business Logic for all objects: object without a "name" (the first column in Airtable) is skipped
                if (!isset($record['fields']['fldBKiwo3fQGfVYrK'])) {
                    $message = 'Skipping because we have no name (the first column).';
                    $this->info($message);
                    Log::info($message);
                    continue; // skip to the next
                }
                // Business Logic: a placement MUST have a provider, or we skip it.
                if (!isset($record['fields']['fld0hNnckpnsVadNL'])) {
                    $message = 'Skipping Placement for ID ' . $record['id'] . ' because it has no Provider ID, might be an empty row.';
                    $this->info($message);
                    Log::info($message);
                    continue; // skip to the next
                }
                // Get the relationship IDs from our database
                $provider = Provider::where('airtable_id',  $record['fields']['fld0hNnckpnsVadNL'][0])->first();

                // Map the fields in the data to be compatible with our table
                $fieldmap = [
                    'airtable_id' => $record['id'],
                    'name' => $record['fields']['fldBKiwo3fQGfVYrK'],
                    'provider_id' => $provider->id,
                    'category' => $record['fields']['fldyzdsp0dH6tfoh0'] ?? '',
                    'mediatype' => $record['fields']['fldwN35giv2gjFPPJ'] ?? '',
                    'platformtype' => $record['fields']['fldnGuewS0e2cWEmZ'] ?? '',
                    'tradeschoolstrategy' => $record['fields']['fldBixnmhDBf6r7z7'] ?? '',
                    'figma_id' => $record['fields']['fld4zkdMHxrm9kQMb'] ?? '',

                ];
                $object = Placement::firstOrNew(['airtable_id' => $record['id']]);
                $object->fill($fieldmap);
                $object->save();

            }  elseif ($table == 'providers') {
                // Business Logic for all objects: object without a "name" (the first column in Airtable) is skipped
                if (!isset($record['fields']['fldPtPWXTyt2eSeDo'])) {
                    $message = 'Skipping item because we have no name (the first column).';
                    $this->info($message);
                    Log::info($message);
                    continue; // skip to the next
                }
                $fieldmap = [
                    'airtable_id' => $record['id'],
                    'name' => $record['fields']['fldPtPWXTyt2eSeDo'],
                ]; // Map the fields in the data to be compatible with our table
                $object = Provider::firstOrNew(['airtable_id' => $record['id']]);
                $object->fill($fieldmap);
                $object->save();

            }
            $count++;
        }

        /*
         * Now let's check which items have been deleted in Airtable, and mark those as deleted in our DB
         */
        if ($table == 'clients') {
            Client::whereNotIn('airtable_id', $airtable_ids)->update(['exists_in_airtable' => false]);
        } elseif ($table == 'styles') {
            Style::whereNotIn('airtable_id', $airtable_ids)->update(['exists_in_airtable' => false]);
        } elseif ($table == 'campaigns') {
            Campaign::whereNotIn('airtable_id', $airtable_ids)->update(['exists_in_airtable' => false]);
        } elseif ($table == 'providers') {
            Provider::whereNotIn('airtable_id', $airtable_ids)->update(['exists_in_airtable' => false]);
        } elseif ($table == 'placements') {
            Placement::whereNotIn('airtable_id', $airtable_ids)->update(['exists_in_airtable' => false]);
        } elseif ($table == 'units') {
            Unit::whereNotIn('airtable_id', $airtable_ids)->update(['exists_in_airtable' => false]);
        }
        // Future @todo support deleting images from an ad unit (we don't delete images now)

        $this->info('Done.. ' . $count . ' items added or updated'); // Provide feedback to the user
    }

}
