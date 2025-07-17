<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">VENUEID</div>
        <div class="col-span-8 font-medium">{{ (isset($venue->idnumber) && !empty($venue->idnumber) ? $venue->idnumber : '---') }}</div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">OWNVENUEID</div>
        <div class="col-span-8 font-medium">{{ (isset($venue->id) && !empty($venue->id) ? $venue->id : '---') }}</div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">POSTCODE</div>
        <div class="col-span-8 font-medium">{{ (isset($venue->postcode) && !empty($venue->postcode) ? $venue->postcode : '---') }}</div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">VENUENAME</div>
        <div class="col-span-8 font-medium">{{ (isset($venue->name) && !empty($venue->name) ? $venue->name : '---') }}</div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">VENUEUKPRN</div>
        <div class="col-span-8 font-medium">{{ (isset($venue->ukprn) && !empty($venue->ukprn) ? $venue->ukprn : '---') }}</div>
    </div>
</div>