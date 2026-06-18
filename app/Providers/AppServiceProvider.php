<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

require_once dirname(__DIR__).'/Helpers/fecha.php';

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void {}
}
