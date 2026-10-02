<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Punch terminal — restrict to clock-in
    |--------------------------------------------------------------------------
    | With this on, the /punch terminal offers Break, Break Return and Clock-Out
    | only to staff holding the "Allow All Services" privilege. Everyone else
    | can clock in there and ends their day from their own dashboard.
    |
    | A single env var so it can be turned off instantly, with no deploy, if the
    | restriction ever locks out more people than intended.
    */

    'punch_restrict_services' => (bool) env('PUNCH_RESTRICT_SERVICES', true),

];
