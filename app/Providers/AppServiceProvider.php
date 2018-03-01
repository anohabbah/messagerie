<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

//        if ($this->app->environment() === 'local') {
//            if (isset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'])) {
//                file_put_contents('php://stdout',
//                    "\e[33m[HTTP::{$_SERVER['REQUEST_METHOD']} \e[0m{$_SERVER['REQUEST_URI']}\n");
//            }
//
//            DB::listen(function (QueryExecuted $event) {
//                file_put_contents('php://stdout', "\e[34m{$event->sql}\t\e[37m" .
//                    json_encode($event->bindings) . "\t\e[32m{$event->time}ms\e[0m\n");
//            });
//        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Passport::ignoreMigrations();
    }
}
