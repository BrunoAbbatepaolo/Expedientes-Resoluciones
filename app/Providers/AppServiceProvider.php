<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Livewire\DashboardPanel;

require_once dirname(__DIR__).'/Helpers/fecha.php';

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void {}
}
