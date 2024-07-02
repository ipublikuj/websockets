<?php declare(strict_types = 1);

namespace IPubModule;

use IPub\WebSockets\Application;
use IPub\WebSockets\Application\Controller;
use IPub\WebSockets\Application\Responses;
use IPub\WebSockets\Exceptions;
use IPub\WebSockets\Router;
use Nette;
use Nette\DI;
use Nette\Utils;
use ReflectionException;
use ReflectionNamedType;

/**
 * WebSockets micro controller
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class WebSocketsController implements Controller\IController
{

	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/** @var DI\Container|null */
	private $context;

	/** @var Router\IRouter */
	private $router;

	/** @var Application\Request */
	private $request;

	/** @var string */
	private $name;

	/**
	 * @param DI\Container|null $context
	 * @param Router\IRouter|null $router
	 */
	public function __construct(?DI\Container $context = null, ?Router\IRouter $router = null)
	{
		$this->context = $context;
		$this->router = $router;
	}

	/**
	 * Gets the context
	 *
	 * @return DI\Container
	 */
	public function getContext(): DI\Container
	{
		return $this->context;
	}

	/**
	 * @param Application\Request $request
	 *
	 * @return Responses\IResponse
	 *
	 * @throws Exceptions\BadRequestException
	 * @throws Exceptions\InvalidStateException
	 * @throws ReflectionException
	 */
	public function run(Application\Request $request): Responses\IResponse
	{
		$this->name = $request->getControllerName();

		$this->request = $request;

		$params = $request->getParameters();

		if (!isset($params['callback'])) {
			throw new Exceptions\BadRequestException('Parameter callback is missing.');
		}

		$callback = $params['callback'];

		$reflection = Utils\Callback::toReflection(Utils\Callback::check($callback));

		if ($this->context) {
			foreach ($reflection->getParameters() as $param) {
				$type = $param->getType();

				if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
					$params[$param->getName()] = $this->context->getByType($type->getName(), false);
				}
			}
		}

		$params['controller'] = $this;
		$params = Application\Reflection::combineArgs($reflection, $params);

		$response = call_user_func_array($callback, $params);

		if (is_array($response)) {
			$response = new Responses\MessageResponse($response);

		} elseif (!$response instanceof Responses\IResponse) {
			throw new Exceptions\InvalidStateException('Returned value from micro controller is no valid.');
		}

		return $response;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return Application\Request
	 */
	public function getRequest(): Application\Request
	{
		return $this->request;
	}

}
