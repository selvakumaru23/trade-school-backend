# Local setup
- clone code
- npm install (your IDE will tell you to)
- composer install (your IDE will tell you to)
- php artisan key:generate (will update key in .env)
- set up .env, including all the keys and a mysql db
- locally create mysql db
- php artisan migrate
- npm run dev (to start vite)
- Go to routes/auth.php and uncomment the /register routes so that you can create a user
- sign in locally http://tradeschool.test/register and create a user
- in auth.php, comment out the /register routes again so live nobody can create a user

OK now you are in, let's test this:
- Go to http://tradeschool.test/admin and do the first sync. This will pull data from airtable.
  - To test, go to http://tradeschool.test/dashboard and it should show a list of campaigns
- Back to http://tradeschool.test/admin and do the second sync. 
  - This can take a long time, because it is downloading all the images.
  - This code is not optimized for scale so may break.
  - You might get a 504 gateway timeout. 
  - Run the second sync again, multiple times. It will skip the units already finished and continue with the following ones.
  - A lot of units will already have imported. Check your /storage/logs and your db table units.
- Now all your campaigns should be in your local DB. You can access them in the UI and they will start generating text and images.
  - Run php artisan queue:listen to start a queue worker for copy and image generation.

# Welcome
## Airtable
- All objects must have their Name (the first column in Airtable) filled in, or they are not imported.
- All objects must have their relationships filled in.
  - if, for example, we have a Unit that references a Campaign, but that campaign doesn't have a client, we skip that unit.
- Be careful deleting things in Airtable, this has limited support to propogate throughout the system for the demo.
  - For example, if you delete a campaign, but not its Units, the Units will still exist in the system.
- Note: we only start the generation process once you visit the Campaign page with all the ad units, so that you can actually see the generation happening and it looks good.

## Images
- We only support original images in Airtable that are .jpg or .webp. We do not support .png.
- Do not upload non-image files as images (limited error checking there)
- Image exporting
  - We don't create an optimized 360x50 image and send that to Figma. 
    - Instead, we export to Figma a higher res image, and, with a different aspect ratio.
    - Inside of Figma, we can use Figma masks and export options to optimize images.
      - This creates flexibility for the designers to tweak things in Figma if needed.

# Technical Notes
- Image relationships
  - Relationships with images are a little tricky.
  - In Airtable, the same image (with the same airtable_id) can belong to multiple units. 
  - In our system, ever image only belongs to one unit, to keep things clean.
  - We have two types of images: Outputimage is an image that is created by our system. Image is an image pulled from Airtable.
  - Outputimage
    - An Outputimage belongs to 1 unit and to 1 image.
    - It is created by our system.
    - When we re-generate an image for a specific unit and a specific image in that unit, we keep previous versions.
  - Image
    - A Unit can have multiple Images.
  - (Also note, we don't currently delete images from S3 storage, even if they are removed from the DB)

## Testing locally
- To generate copy, we use background workers. To test this locally, you have to do php artisan queue:work, to start some local workers.
  - You can do php artisan queue:work on multiple command lines, to create multiple workers, so Jobs get done faster (in parralel)
  - This worker stays in memory while it's running. If you change anything in the Job code, close it down and restart it, so it uses the latest code.
## Airtable restrictions
For things to be imported:
- every object needs a Name (the first field) required. 
- Campaigns require Client and Style 
- Placement requires Provider 
- Units require a valid Campaign and a valid Placement 
- Image needs to be jpg or webp

## Business Logic in the code
- In TradeschoolAirtableGet: 
  - All objects must have their Name (the first column in Airtable) filled in, or they are not imported.
    - Note that empty rows in Airtable are also given an Airtable ID, so this rule stops those from being imported.
    - Edge case note: if you add a name, import it, and then delete the name, it still exists.
  - All objects must have their relationships filled in. 
    - if, for example, we have a Unit that references a Campaign, but that campaign doesn't have a client, we skip that unit.
      - (This 2nd-order logic is implemented for units but not for other objects)
    - Ex: A Campaign must have a client id and a style id, or we just skip it.
  - We mark objects as exist_in_airtable=false when they are deleted in airtable
    - So we keep them in the DB as to not break other things (references to it)
    - We do that when the command to update runs.
  - Tips:
    - Do not upload non-image files as images (limited error checking there)
- Images
    - We do not support deleting images from an ad unit for the prototype
    - We do support large image files, tested up to 50 MB. However, it's better to use smaller images (2-5MB each).
- DELETING things
    - You can delete ad units, or styles, any object.
        - We soft-delete things in order not to break stuff.
    - But be careful deleting things in Airtable. This needs some testing.
        - For example, we don't delete relationships.
            - If you delete a campaign, in our system the ad units for that campaign still exist.
    - When we move from prototype to full production, deleting things will need some work.
- DEMO considerations
    - We only start the generation process once you visit the Campaign page with all the ad units, so that you can actually see the generation happening and it looks good.
        - Technically, we could easily be running these Jobs in the background from the moment we import a campaign.


## Local Setup
- Very recommended: use Laravel Herd to set up local Laravel environment
- We use Laravel 11, php 8.3, Node, MySQL.
- Create a new directory under /herd/ called tradeschool-dynamic and check out the code from Github
- You need a local mysql DB running
- Adjust your .env file (copy from the example)
- Command line:
  - php artisan migrate to run the migrations.
  - npm run dev to start Vite for local development (frontend)
- Go to http://tradeschool-dynamic.test in your browser, that should now work.
  - Create an account at /register
- IMPORTANT 
  - Local Development is using the live production Airtable.
- S3 bucket set up with these instructions https://laravel-news.com/using-aws-s3-for-laravel-storage
- Copying images to S3 is slow, can take 30 seconds per unit if it has a few large images.

## Architecture
- We interact with genAI in Jobs.
- We have a set of command lines commands to interact with Airtable
  - They live in app/Console/Commands
  - tradeschool:getfromairtable all -> updates all the tables from Airtabe
  - We pull data from Airtable using https://github.com/TappNetwork/laravel-airtable
    - Airtable Config settings are here: config/airtable
      - We use Airtable IDs for table names and field names, so that if they change them it doesn't break
- We have db tables and models for Ad Units etc.
  - Under /app/models/
- Images
  - We store images in S3
- We use Livewire polling (which is simpler than setting up WebSockets) to dynamically update the "generating" bits
  - Where that code lives
- We expose JSON for the Figma plugin to read and use a simple hardcoded Bearer Token API key
  - The route is /api/...
  - The controller is /Controllers/ApiController
- Use a Queue and Workers to make the API calls to generate text and images
- Deploy with Forge to a simple server, for 1-click deploys
  - Forge setup notes: 
    - In Forge, in the site in 'Queue', create 1 or more queue workers with a 'database' driver, not a redis driver. More Workers = it moves faster.
    - Set up multiple Workers using Forge (Forge provides the Supervisor setup that keeps Queues and Workers running) https://forge.laravel.com/features/queues

