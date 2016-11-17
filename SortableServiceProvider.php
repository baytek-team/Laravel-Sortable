<?php

namespace Baytek\LaravelSortable;

use Illuminate\Support\ServiceProvider;

class SortableServiceProvider extends ServiceProvider {

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Sort', function ($app) {
            return new Sort($app);
        });
    }

}