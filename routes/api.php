<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function () {
    Route::get('/conversations', 'API\ConversationsController@index');
    Route::get('/conversations/{user}', 'API\ConversationsController@show')->middleware('can:talkTo,user');
    Route::post('/conversations/{user}', 'API\ConversationsController@store')->middleware('can:talkTo,user');
});
