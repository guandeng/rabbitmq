<?php
namespace Guandeng\Testpkg;

use Illuminate\Support\ServiceProvider;

class Md5HasherProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->app->singleton("md5hash", function () {
            return new Md5Hasher();
        });
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }
}
