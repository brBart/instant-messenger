<?php namespace Nathanmac\InstantMessenger;

use Illuminate\Support\ServiceProvider;
use Nathanmac\InstantMessenger\Console\MessengerCommand;

class MessengerServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('messenger.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register Aliases
        $this->app->alias('messenger', 'Nathanmac\InstantMessenger\Messenger');
        $this->app->alias('messenger', 'Nathanmac\InstantMessenger\Contracts\Messenger');
        $this->app->alias('messenger', 'Nathanmac\InstantMessenger\Contracts\MessengerQueue');

        // Register the Facade
        $this->app->alias('Messenger', 'Nathanmac\InstantMessenger\Facades\Messenger');

        $this->app->singleton('messenger', function ($app) {
            $this->registerMessengerService();

            // Once we have create the messenger instance, we will set a container instance
            // on the messenger. This allows us to resolve messenger classes via containers
            // for maximum testability on said classes instead of passing Closures.
            $messenger = new Messenger(
                $app['messenger.transport']->driver(), $app['events']
            );

            $this->setMessengerDependencies($messenger, $app);

            // Here we will determine if the messenger should be in "pretend" mode for this
            // environment, which will simply write out messages to the logs instead of
            // sending it over the web, which is useful for local dev environments.
            $pretend = $app['config']->get('messenger.pretend', false);

            $messenger->pretend($pretend);

            return $messenger;
        });

        // Add command for messenger
        $this->app->bindShared('command.messenger.send', function($app)
        {
            return new MessengerCommand();
        });
        $this->commands('command.messenger.send');
    }

    /**
     * Set a few dependencies on the messenger instance.
     *
     * @param  \Nathanmac\InstantMessenger\Messenger $messenger
     * @param  \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    protected function setMessengerDependencies(Messenger $messenger, $app)
    {
        $messenger->setContainer($app);

        if ($app->bound('log'))
        {
            $messenger->setLogger($app['log']->getMonolog());
        }

        if ($app->bound('queue'))
        {
            $messenger->setQueue($app['queue.connection']);
        }
    }

    /**
     * Register the Messenger Service instance.
     *
     * @return void
     */
    protected function registerMessengerService()
    {
        $this->app['messenger.transport'] = $this->app->share(function($app)
        {
            return new ServiceManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['messenger', 'messenger.transport'];
    }
}
