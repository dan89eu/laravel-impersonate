<?php
	/**
	 * Created by PhpStorm.
	 * User: danpetrescu
	 * Date: 26/10/2017
	 * Time: 13:14
	 */


	namespace Lab404\Impersonate;

	use Illuminate\Routing\Router;
	use Illuminate\Support\Facades\Blade;
	use Lab404\Impersonate\ImpersonateServiceProvider;
	use Lab404\Impersonate\Middleware\ProtectFromImpersonation;
	use Lab404\Impersonate\Services\ImpersonateManager;
	use Lab404\Impersonate\Services\SentinelImpersonateManager;

	/**
	 * Class ServiceProvider
	 *
	 * @package Lab404\Impersonate
	 */

	class SentinelImpersonateServiceProvider extends ImpersonateServiceProvider
	{

		/**
		 * Register the service provider.
		 *
		 * @return void
		 */
		public function register()
		{
			$configPath = __DIR__ . '/../config/' . $this->configName . '.php';
			$this->mergeConfigFrom($configPath, $this->configName);
			$this->app->bind(ImpersonateManager::class, SentinelImpersonateManager::class);
			$this->app->singleton(ImpersonateManager::class, function ($app)
			{
				return new SentinelImpersonateManager($app);
			});
			$this->app->alias(SentinelImpersonateManager::class, 'impersonate');
			$router = $this->app['router'];
			$router->macro('impersonate', function () use ($router) {
				$router->get('/impersonate/take/{id}', '\Lab404\Impersonate\Controllers\ImpersonateController@take')->name('impersonate');
				$router->get('/impersonate/leave', '\Lab404\Impersonate\Controllers\ImpersonateController@leave')->name('impersonate.leave');
			});
			$this->registerBladeDirectives();
			$this->app['router']->aliasMiddleware('impersonate.protect', ProtectFromImpersonation::class);
		}

		/**
		 * Register plugin blade directives.
		 *
		 * @param   void
		 * @return  void
		 */
		protected function registerBladeDirectives()
		{
			Blade::directive('impersonating', function () {
				return '<?php if (Sentinel::check() && Sentinel::getUser()->isImpersonated()): ?>';
			});

			Blade::directive('endImpersonating', function () {
				return '<?php endif; ?>';
			});

			Blade::directive('canImpersonate', function () {
				return '<?php if (app()["auth"]->check() && app()["auth"]->user()->canImpersonate()): ?>';
			});

			Blade::directive('endCanImpersonate', function () {
				return '<?php endif; ?>';
			});

			Blade::directive('canBeImpersonated', function($expression) {
				$user = trim($expression);
				return "<?php if (app()['auth']->check() && app()['auth']->user()->id != {$user}->id && {$user}->canBeImpersonated()): ?>";
			});

			Blade::directive('endCanBeImpersonated', function() {
				return '<?php endif; ?>';
			});
		}

	}