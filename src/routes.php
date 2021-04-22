<?php

if (app()->environment('local')) {
    Route::namespace('TELstatic\\Thresh\\Controllers')
        ->middleware('throttle:60,1')
        ->group(function () {
            Route::get('thresh/swagger', 'SwaggerController@index');
            Route::resource('thresh', 'ThreshController');
        });
}
