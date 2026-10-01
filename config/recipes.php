<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recipe Source
    |--------------------------------------------------------------------------
    |
    | Where user-facing recipe reads are served from:
    |
    | 'api' - live TheMealDB API (default, current behaviour)
    | 'db'  - locally synced database (populated by app:mealdb:sync)
    |
    */

    'source' => env('RECIPE_SOURCE', 'api'),

];
