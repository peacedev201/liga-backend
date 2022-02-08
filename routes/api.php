<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// User Api Routes
Route::namespace('API')->group(function () {
    Route::prefix('user')->namespace('User')->group(function () {
        // User Guest Routes
        Route::prefix('data')->group(function () {
            Route::prefix('related')->group(function () {
                Route::get('player/signup', 'DataController@playerSignup');
            });
            Route::get('latestfourgames', 'DataController@latestfourgames');
            Route::get('bestfiveplayers', 'DataController@bestfiveplayers');
            Route::get('bestplayers', 'DataController@bestplayers');

            Route::get('clubs', 'DataController@clubs');
            Route::get('clubs/all/{key}', 'DataController@clubsAll');
            Route::get('clubs/{id}/{key}', 'DataController@clubsPage');
            Route::get('clubs/{id}', 'DataController@clubsPage');
            Route::get('club/{id}', 'DataController@club');

            Route::get('players', 'DataController@players');
            Route::get('players/all/{key}', 'DataController@playersAll');
            Route::get('players/{id}/{key}', 'DataController@playersPage');
            Route::get('players/{id}', 'DataController@playersPage');
            Route::get('player/{id}', 'DataController@player');

            Route::get('tournaments', 'DataController@tournaments');
            Route::get('tournament/{id}', 'DataController@tournament');
            Route::get('news', 'DataController@news');
            Route::get('news/{slug}', 'DataController@newsSingle');      
            
        });

        Route::middleware('guest')->group(function () {
            Route::post('register', 'AuthController@register');
            Route::post('login', 'AuthController@login');
            Route::post('forgot', 'AuthController@forgot');
            Route::post('reset', 'AuthController@reset');

        });

        // User Auth Routes
        Route::middleware('auth')->group(function () {
            Route::post('addmessage', 'FriendController@addMessage');
            Route::get('approvemessage/{id}', 'FriendController@approveMessage');
            Route::get('getthreads/{id}', 'FriendController@getThreads');
            
            Route::get('openchat/{id}', 'FriendController@openChat');
            Route::get('createthread/{from}/{to}', 'FriendController@createThread');
            Route::get('supportrequests/{from}', 'FriendController@supportRequest');
            Route::get('friendlist/{id}', 'FriendController@friendList');
            Route::get('requestlist/{id}', 'FriendController@requestList');
            Route::get('friendrequests/{from}/{to}', 'FriendController@friendRequest');
            Route::get('approverequests/{from}/{to}', 'FriendController@approveRequest');
            Route::get('cancelrequests/{from}/{to}', 'FriendController@cancelRequest');

            Route::get('getnotification/{id}', 'FriendController@getNotification');


            Route::post('email/verify', 'AuthController@verify');
            Route::post('email/resend', 'AuthController@resend');
            Route::post('logout', 'AuthController@logout');
            Route::middleware('usertype:player,club')->group(function () {
                Route::get('authenticate', 'AuthController@authenticate');

                Route::apiResource('tournament', 'TournamentController');

                Route::middleware('usertype:player')->namespace('Player')->prefix('player')->group(function () {
                    Route::apiResource('profile', 'ProfileController');
                });

                Route::middleware('usertype:club')->namespace('Club')->prefix('club')->group(function () {
                    Route::apiResource('profile', 'ProfileController');
                });
            });
        });
    });

    // Admin Api Routes
    Route::prefix('admin')->namespace('Admin')->group(function () {
        // Admin Guest Routes
        Route::middleware('guest:admin')->group(function () {
            
            Route::post('login', 'AuthController@login');
            Route::post('forgot', 'AuthController@forgot');
            Route::post('reset', 'AuthController@reset');
        });

        // Admin Auth Routes
        Route::middleware('auth:admin')->group(function () {
            Route::post('addmessage', 'SupportController@addMessage');
            Route::get('approvemessage/{id}', 'SupportController@approveMessage');
            Route::get('ticketlist', 'SupportController@ticketList');
            Route::get('openchat/{id}', 'SupportController@openChat');
            Route::get('closeticket/{id}', 'SupportController@closeTicket');

            Route::get('authenticate', 'AuthController@authenticate');
            Route::get('getnotification/{id}', 'SupportController@getNotification');
            Route::post('logout', 'AuthController@logout');

            Route::apiResource('admin', 'AdminController');
            Route::post('tournament/round/{id}', 'TournamentController@round');
            Route::post('tournament/plan/{id}', 'TournamentController@plan');
            Route::post('tournament/schedule/{id}', 'TournamentController@schedule');
            Route::post('tournament/complete/{id}', 'TournamentController@complete');
            Route::post('tournament/start/{id}', 'TournamentController@start');
            Route::apiResource('tournament', 'TournamentController');
            Route::apiResource('news', 'NewsController');
            Route::get('player/related', 'PlayerController@related');
            Route::apiResource('player', 'PlayerController')->except('store');
            Route::apiResource('club', 'ClubController')->except('store', 'update');
        });
    });
});
