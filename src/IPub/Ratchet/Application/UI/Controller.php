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

/**
 * Ratchet application interface
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
abstract class Controller implements IController
{
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

	public function __construct()
	{
		list(, $name) = func_get_args() + [NULL, NULL];

		if (is_string($name)) {
			$this->name = $name;
		}
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

			$this->initGlobalParameters();

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
	 * @return string
	 */
	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * Returns current action name
	 *
	 * @param bool $fullyQualified
	 *
	 * @return string
	 */
	public function getAction(bool $fullyQualified = FALSE) : string
	{
		return $fullyQualified ? ':' . $this->getName() . ':' . $this->action : $this->action;
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
	public function changeAction($action)
	{
		if (is_string($action) && Nette\Utils\Strings::match($action, '#^[a-zA-Z0-9][a-zA-Z0-9_\x7f-\xff]*\z#')) {
			$this->action = $action;

		} else {
			throw new Exceptions\BadRequestException('Action name is not alphanumeric string.', Nette\Http\IResponse::S404_NOT_FOUND);
		}
	}

	/**
	 * Formats action method name
	 *
	 * @param string $action
	 *
	 * @return string
	 */
	public static function formatActionMethod($action) : string
	{
		return 'action' . $action;
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
		$this->sendResponse(new Responses\MessageResponse('Test message'));
		//$this->sendResponse(new Responses\MessageResponse($this->payload));
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

		$rm = $rc->getMethod($method);

		if ($rm->isPublic() && !$rm->isAbstract() && !$rm->isStatic()) {
			$rm->invokeArgs($this, $rc->combineArgs($rm, $params));

			return TRUE;
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
		$this->changeAction(isset($selfParams[self::ACTION_KEY]) ? $selfParams[self::ACTION_KEY] : self::DEFAULT_ACTION);
	}
}
