<div class="grid grid-cols-12 gap-4"> 
    <div class="col-span-12 sm:col-span-3">
        <div class="grid grid-cols-12 gap-0">
            <div class="col-span-4 text-slate-500 font-medium uppercase">VENUEID</div>
            <div class="col-span-8 font-medium">{{ (isset($venue->idnumber) && !empty($venue->idnumber) ? $venue->idnumber : '---') }}</div>
        </div>
    </div>
    <div class="col-span-12 sm:col-span-3">
        <div class="grid grid-cols-12 gap-0">
            <div class="col-span-4 text-slate-500 font-medium uppercase">OWNVENUEID</div>
            <div class="col-span-8 font-medium">{{ (isset($venue->id) && !empty($venue->id) ? $venue->id : '---') }}</div>
        </div>
    </div>
    <div class="col-span-12 sm:col-span-3">
        <div class="grid grid-cols-12 gap-0">
            <div class="col-span-4 text-slate-500 font-medium uppercase">POSTCODE</div>
            <div class="col-span-8 font-medium">{{ (isset($venue->postcode) && !empty($venue->postcode) ? $venue->postcode : '---') }}</div>
        </div>
    </div>
    <div class="col-span-12 sm:col-span-3">
        <div class="grid grid-cols-12 gap-0">
            <div class="col-span-4 text-slate-500 font-medium uppercase">VENUENAME</div>
            <div class="col-span-8 font-medium">{{ (isset($venue->name) && !empty($venue->name) ? $venue->name : '---') }}</div>
        </div>
    </div>
    <div class="col-span-12 sm:col-span-3">
        <div class="grid grid-cols-12 gap-0">
            <div class="col-span-4 text-slate-500 font-medium uppercase">VENUEUKPRN</div>
            <div class="col-span-8 font-medium">{{ (isset($venue->ukprn) && !empty($venue->ukprn) ? $venue->ukprn : '---') }}</div>
        </div>
    </div>
</div>