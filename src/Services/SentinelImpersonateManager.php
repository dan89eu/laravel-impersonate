<?php
	/**
	 * Created by PhpStorm.
	 * User: danpetrescu
	 * Date: 26/10/2017
	 * Time: 13:16
	 */

	namespace Lab404\Impersonate\Services;

	use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
	use Cartalyst\Sentinel\Laravel\Facades\Activation;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Foundation\Application;
	use Illuminate\Support\Facades\Auth;
	use Lab404\Impersonate\Events\LeaveImpersonation;
	use Lab404\Impersonate\Events\TakeImpersonation;

	class SentinelImpersonateManager extends ImpersonateManager
	{

		/**
		 * @var Application
		 */
		private $app;

		public function __construct(Application $app)
		{
			$this->app = $app;
		}

		public function getUserId()
		{
			$user_id = Sentinel::getUser()->id;
			return $user_id;
		}
		/**
		 * @param   int $id
		 * @return  Model
		 */
		public function findUserById($id)
		{
			$user = Sentinel::getUserRepository()->findById($id);
			return $user;
		}

		/**
		 * @param Model $from
		 * @param Model $to
		 * @return bool
		 */
		public function take($from, $to)
		{
			try
			{
				$impersonator = Sentinel::getUser();
				$impersonated = $to;

				session()->put(config('laravel-impersonate.session_key'), $from->getKey());

				Sentinel::logout();
				Sentinel::login($to);
			} catch (\Exception $e)
			{
				unset($e);

				return false;
			}
			$this->app['events']->fire(new TakeImpersonation($impersonator, $impersonated));

			return true;
		}
		/**
		 * @return  bool
		 */
		public function leave()
		{
			try
			{
				$impersonated = Sentinel::getUser();

				Sentinel::logout();
				Sentinel::login( $this->findUserById( $this->getImpersonatorId() ) );
				$impersonator = Sentinel::getUser();

				$this->clear();

			} catch (\Exception $e)
			{
				unset($e);
				return false;
			}
			$this->app['events']->fire(new LeaveImpersonation($impersonator, $impersonated));
			return true;
		}

		/**
		 * @return bool
		 */
		public function isImpersonating()
		{
			return session()->has($this->getSessionKey());
		}

		/**
		 * @param   void
		 * @return  int|null
		 */
		public function getImpersonatorId()
		{
			return session($this->getSessionKey(), null);
		}

		/**
		 * @return void
		 */
		public function clear()
		{
			session()->forget($this->getSessionKey());
		}

		/**
		 * @return string
		 */
		public function getSessionKey()
		{
			return config('laravel-impersonate.session_key');
		}

		/**
		 * @return  string
		 */
		public function getTakeRedirectTo()
		{
			try {
				$uri = route(config('laravel-impersonate.take_redirect_to'));
			} catch (\InvalidArgumentException $e) {
				$uri = config('laravel-impersonate.take_redirect_to');
			}

			return $uri;
		}

		/**
		 * @return  string
		 */
		public function getLeaveRedirectTo()
		{
			try {
				$uri = route(config('laravel-impersonate.leave_redirect_to'));
			} catch (\InvalidArgumentException $e) {
				$uri = config('laravel-impersonate.leave_redirect_to');
			}

			return $uri;
		}

	}