<?php

namespace Abs\TaxPkg;

use Illuminate\Support\ServiceProvider;

class TaxPkgServiceProvider extends ServiceProvider {
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register() {
		$this->loadRoutesFrom(__DIR__ . '/routes/web.php');
		$this->loadRoutesFrom(__DIR__ . '/routes/api.php');
		$this->loadMigrationsFrom(__DIR__ . '/migrations');
		$this->loadViewsFrom(__DIR__ . '/views', 'tax-pkg');
		$this->publishes([
			__DIR__ . '/public' => base_path('public'),
			__DIR__ . '/database/seeds/client' => 'database/seeds',
			__DIR__ . '/config/config.php' => config_path('tax-pkg.php'),
		]);
	}

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot() {
	}
}
