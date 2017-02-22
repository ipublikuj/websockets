<?php
/**
 * Controller.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           17.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Application\UI;

use Nette;
use Nette\Utils;

use IPub;
use IPub\Ratchet\Application;
use IPub\Ratchet\Application\Responses;
use IPub\Ratchet\Exceptions;
use IPub\Ratchet\Router;
use IPub\Ratchet\Session;

/**
 * Ratchet application controller interface
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @property-read \stdClass $payload
 * @property-read Nette\Security\User $user
 */
abstract class Controller implements IController
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * Special parameter keys
	 *
	 * @internal
	 */
	const ACTION_KEY = 'action';
	const DEFAULT_ACTION = 'default';

	/**
	 * @var Application\Request
	 */
	private $request;

	/**
	 * @var Responses\IResponse
	 */
	private $response;

	/**
	 * @var \stdClass
	 */
	private $payload;

	/**
	 * @var bool
	 */
	private $startupCheck = FALSE;

	/**
	 * @var array
	 */
	private $globalParams = [];

	/**
	 * @var array
	 */
	private $params = [];

	/**
	 * @var string
	 */
	private $action;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $defaultAction = self::DEFAULT_ACTION;

	/**
	 * @var Nette\DI\Container
	 */
	private $context;

	/**
	 * @var Application\IControllerFactory
	 */
	private $controllerFactory;

	/**
	 * @var Router\IRouter
	 */
	private $router;

	/**
	 * @var Session\Session
	 */
	private $session;

	/**
	 * @var Nette\Security\User
	 */
	private $user;

	/**
	 * @param Nette\DI\Container|NULL $context
	 * @param Application\IControllerFactory|NULL $controllerFactory
	 * @param Router\IRouter|NULL $router
	 * @param Nette\Security\User|NULL $user
	 */
	public function injectPrimary(
		Nette\DI\Container $context = NULL,
		Application\IControllerFactory $controllerFactory = NULL,
		Router\IRouter $router = NULL,
		Nette\Security\User $user = NULL
	) {
		if ($this->controllerFactory !== NULL) {
			throw new Nette\InvalidStateException(sprintf('Method "%s" is intended for initialization and should not be called more than once.', __METHOD__));
		}

		$this->context = $context;
		$this->controllerFactory = $controllerFactory;
		$this->router = $router;
		$this->user = $user;
	}

	public function __construct()
	{
		$this->payload = new \stdClass;
	}

	/**
	 * @param Application\Request $request
	 *
	 * @return Responses\IResponse
	 * @throws Exceptions\InvalidStateException
	 */
	public function run(Application\Request $request) : Responses\IResponse
	{
		try {
			// STARTUP
			$this->request = $request;
			$this->payload = $this->payload ?: new \stdClass;
			$this->name = $request->getControllerName();

			$this->initGlobalParameters();

			$this->checkRequirements(new Nette\Application\UI\ComponentReflection($this));

			$this->startup();

			if (!$this->startupCheck) {
				$class = (new \ReflectionClass($this))->getMethod('startup')->getDeclaringClass()->getName();

				throw new Exceptions\InvalidStateException(sprintf('Method %s::startup() or its descendant doesn\'t call parent::startup().', $class));
			}

			// calls $this->action<Action>()
			$this->tryCall($this->formatActionMethod($this->action), $this->params);

			$this->sendPayload();

		} catch (Exceptions\AbortException $ex) {
			// SHUTDOWN
			$this->shutdown($this->response);

			return $this->response;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * Checks authorization
	 *
	 * @param $element
	 *
	 * @return void
	 *
	 * @throws Exceptions\ForbiddenRequestException
	 */
	public function checkRequirements($element)
	{
		$user = (array) Nette\Application\UI\ComponentReflection::parseAnnotation($element, 'User');

		if (in_array('loggedIn', $user, TRUE) && !$this->getUser()->isLoggedIn()) {
			throw new Exceptions\ForbiddenRequestException;
		}
	}

	/**
	 * @return \stdClass
	 */
	public function getPayload() : \stdClass
	{
		return $this->payload;
	}

	/**
	 * Sends payload to the output
	 *
	 * @return void
	 *
	 * @throws Nette\Application\AbortException
	 */
	public function sendPayload()
	{
		if (isset($this->payload->callback)) {
			$this->sendResponse(new Responses\CallResponse($this->payload->callback, $this->payload->data));

		} else {
			$this->sendResponse(new Responses\MessageResponse($this->payload->data));
		}
	}

	/**
	 * Sends response and terminates presenter
	 *
	 * @param Responses\IResponse $response
	 *
	 * @return void
	 *
	 * @throws Exceptions\AbortException
	 */
	public function sendResponse(Responses\IResponse $response)
	{
		$this->response = $response;

		$this->terminate();
	}

	/**
	 * Correctly terminates controller
	 *
	 * @return void
	 *
	 * @throws Exceptions\AbortException
	 */
	public function terminate()
	{
		throw new Exceptions\AbortException;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDefaultAction(string $action)
	{
		$this->defaultAction = $action;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSession(Session\Session $session)
	{
		$this->session = $session;
	}

	/**
	 * Changes current action. Only alphanumeric characters are allowed
	 *
	 * @param string $action
	 *
	 * @return void
	 *
	 * @throws Exceptions\BadRequestException
	 */
	private function changeAction($action)
	{
		if (is_string($action) && Nette\Utils\Strings::match($action, '#^[a-zA-Z0-9][a-zA-Z0-9_\x7f-\xff]*\z#')) {
			$this->action = $action;

		} else {
			throw new Exceptions\BadRequestException('Action name is not alphanumeric string.', Nette\Http\IResponse::S404_NOT_FOUND);
		}
	}

	/**
	 * @param string|NULL $namespace
	 *
	 * @return Nette\Http\Session|Nette\Http\SessionSection
	 *
	 * @throws Exceptions\InvalidStateException
	 */
	public function getSession(string $namespace = NULL)
	{
		if (!$this->session) {
			throw new Exceptions\InvalidStateException('Service Session has not been set.');
		}

		return $namespace === NULL ? $this->session : $this->session->getSection($namespace);
	}

	/**
	 * @return Nette\Security\User
	 *
	 * @throws Exceptions\InvalidStateException
	 */
	public function getUser() : Nette\Security\User
	{
		if (!$this->user) {
			throw new Exceptions\InvalidStateException('Service User has not been set.');
		}

		return $this->user;
	}

	/**
	 * Formats action method name
	 *
	 * @param string $action
	 *
	 * @return string
	 */
	private static function formatActionMethod($action) : string
	{
		return 'action' . $action;
	}

	/**
	 * @return void
	 */
	protected function startup()
	{
		$this->startupCheck = TRUE;
	}

	/**
	 * @param Responses\IResponse $response
	 *
	 * @return void
	 */
	protected function shutdown(Responses\IResponse $response)
	{

	}

	/**
	 * Call method of object
	 *
	 * @param string $method
	 * @param array $params
	 *
	 * @return bool
	 */
	protected function tryCall($method, array $params) : bool
	{
		$rc = new Nette\Application\UI\ComponentReflection($this);

		if ($rc->hasMethod($method)) {
			$rm = $rc->getMethod($method);

			if ($rm->isPublic() && !$rm->isAbstract() && !$rm->isStatic()) {
				$this->checkRequirements($rm);
				$rm->invokeArgs($this, $rc->combineArgs($rm, $params));

				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Initializes $this->globalParams, $this->action. Called by run()
	 *
	 * @return void
	 */
	private function initGlobalParameters()
	{
		// init $this->globalParams
		$this->globalParams = [];

		$selfParams = [];

		$params = $this->request->getParameters();

		foreach ($params as $key => $value) {
			if (!preg_match('#^((?:[a-z0-9_]+-)*)((?!\d+\z)[a-z0-9_]+)\z#i', $key, $matches)) {
				continue;

			} elseif (!$matches[1]) {
				$selfParams[$key] = $value;

			} else {
				$this->globalParams[substr($matches[1], 0, -1)][$matches[2]] = $value;
			}
		}

		$this->params = $selfParams;

		// init & validate $this->action & $this->view
		$this->changeAction(isset($selfParams[self::ACTION_KEY]) ? $selfParams[self::ACTION_KEY] : $this->defaultAction);
	}
}
