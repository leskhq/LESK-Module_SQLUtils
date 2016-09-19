<?php
namespace App\Modules\SQLUtils\Providers;

use App;
//use Config;
use Lang;
//use View;
use Illuminate\Support\ServiceProvider;

class SQLUtilsServiceProvider extends ServiceProvider
{
	/**
	 * Register the SQLUtils module service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerNamespaces();
	}

	/**
	 * Register the SQLUtils module resource namespaces.
	 *
	 * @return void
	 */
	protected function registerNamespaces()
	{
		Lang::addNamespace('sql_utils', realpath(__DIR__.'/../Resources/Lang'));

//		View::addNamespace('sql_utils', base_path('resources/views/vendor/sql_utils'));
//		View::addNamespace('sql_utils', realpath(__DIR__.'/../Resources/Views'));
	}

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
		// $this->addMiddleware('');

        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('sql_utils.php'),
        ], 'config');

        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', 'sql_utils'
        );
    }

	/**
     * Register the Middleware
     *
     * @param  string $middleware
     */
	protected function addMiddleware($middleware)
	{
		$kernel = $this->app['Illuminate\Contracts\Http\Kernel'];
        $kernel->pushMiddleware($middleware);
	}
}
