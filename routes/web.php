<?php

Auth::routes();

Route::get('/', 'ConversationsController@index')->name('home');
Route::get('/conversations', 'ConversationsController@index')->name('conversations');
Route::get('/conversations/{user}', 'ConversationsController@index');
