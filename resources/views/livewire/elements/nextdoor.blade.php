<div class="my-2">
    <div class="text-gray-500 text-xs py-1">Nextdoor Subject:</div>
    <div class="pl-2 text-base font-bold">
                    {{ $unit_copy->generated_copy['nextdoor_subject'] ?? 'No subject' }}
    </div>
</div>
<div class="my-2">
    <div class="text-gray-500 text-xs py-1">Nextdoor Body:</div>
    <div class="pl-2 text-base font-bold">
                    {{ $unit_copy->generated_copy['nextdoor_body'] ?? 'No body' }}
    </div>
</div>
<div class="my-2">
    <div class="text-gray-500 text-xs py-1">Nextdoor Offer:</div>
    <div class="pl-2 text-base font-bold">
                    {{ $unit_copy->generated_copy['nextdoor_offer'] ?? 'No headline' }}
    </div>
</div>
