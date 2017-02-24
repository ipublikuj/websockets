<?php
/**
 * SwitchableSession.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 * @since          1.0.0
 *
 * @date           21.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Session;

use Nette;
use Nette\Http;
use Nette\Utils;

use Ratchet\Session as RSession;
use Ratchet\ConnectionInterface;

use IPub;
use IPub\Ratchet\Exceptions;

/**
 * WebSocket session switcher
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class SwitchableSession extends Http\Session
{
	/**
	 * @var Http\Session
	 */
	private $systemSession;

	/**
	 * @var ConnectionInterface|NULL
	 */
	private $connection;

	/**
	 * Has been session started?
	 *
	 * @var bool
	 */
	private $started = FALSE;

	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * @var array
	 */
	private $sections = [];

	/**
	 * @var bool
	 */
	private $attached = FALSE;

	/**
	 * @var \SessionHandlerInterface|NULL
	 */
	private $handler;

	/**
	 * @var NullHandler
	 */
	private $nullHandler;

	/**
	 * @var RSession\Serialize\HandlerInterface
	 */
	private $serializer;

	/**
	 * @param Http\Session $session
	 * @param RSession\Serialize\HandlerInterface $serializer
	 * @param \SessionHandlerInterface|NULL $handler
	 */
	public function __construct(
		Http\Session $session,
		RSession\Serialize\HandlerInterface $serializer,
		\SessionHandlerInterface $handler = NULL
	) {
		$this->systemSession = $session;
		$this->handler = $handler;
		$this->nullHandler = new NullHandler;
		$this->serializer = $serializer;
	}

	/**
	 * @param ConnectionInterface $connection
	 *
	 * @return void
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\LogicException
	 */
	public function attach(ConnectionInterface $connection)
	{
		if ($this->systemSession->isStarted()) {
			throw new Exceptions\LogicException('Session is already started, please close it first and then you can disabled it.');
		}

		$this->attached = TRUE;
		$this->started = FALSE;

		$this->connection = $connection;
	}

	/**
	 * @param ConnectionInterface $connection
	 *
	 * @return void
	 */
	public function detach(ConnectionInterface $connection)
	{
		if (isset($connection->session)) {
			$connection->session->close();
		}

		if ($this->attached) {
			$this->close();

			$this->attached = FALSE;

			$this->connection = NULL;
		}
	}

	/**
	 * @return bool
	 */
	public function isAttached() : bool
	{
		return $this->attached;
	}

	/**
	 * {@inheritdoc}
	 */
	public function start()
	{
		if (!$this->attached) {
			$this->systemSession->start();
			return;
		}

		if ($this->started) {
			return;
		}

		$this->started = TRUE;

		if (!isset($this->connection->WebSocket) || ($id = $this->connection->WebSocket->request->getCookie($this->systemSession->getName())) === NULL) {
			$handler = $this->nullHandler;
			$id = '';

		} else {
			$handler = $this->handler;
		}

		$handler->open(session_save_path(), $this->systemSession->getName());

		$rawData = $handler->read($id);

		$data = $this->serializer->unserialize($rawData);

		/* structure:
			__NF: Data, Meta, Time
				DATA: section->variable = data
				META: section->variable = Timestamp
		*/
		$nf = &$data['__NF'];

		if (!is_array($nf)) {
			$nf = [];
		}

		// regenerate empty session
		if (empty($nf['Time'])) {
			$nf['Time'] = time();
		}

		// process meta metadata
		if (isset($nf['META'])) {
			$now = time();
			// expire section variables
			foreach ($nf['META'] as $section => $metadata) {
				if (is_array($metadata)) {
					foreach ($metadata as $variable => $value) {
						if (!empty($value['T']) && $now > $value['T']) {
							if ($variable === '') { // expire whole section
								unset($nf['META'][$section], $nf['DATA'][$section]);
								continue 2;
							}
							unset($nf['META'][$section][$variable], $nf['DATA'][$section][$variable]);
						}
					}
				}
			}
		}

		$this->data[$this->getConnectionId()] = $data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isStarted()
	{
		if (!$this->attached) {
			return $this->systemSession->isStarted();
		}

		return $this->started;
	}

	/**
	 * {@inheritdoc}
	 */
	public function close()
	{
		if (!$this->attached) {
			$this->systemSession->close();

		} else {
			$this->started = FALSE;

			$this->handler->close();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function destroy()
	{
		if (!$this->attached) {
			$this->systemSession->destroy();

		} else {
			if (!$this->started) {
				throw new Exceptions\InvalidStateException('Session is not started.');
			}

			$this->started = FALSE;

			$this->data = [];
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists()
	{
		if (!$this->attached) {
			return $this->systemSession->exists();
		}

		return $this->started;
	}

	/**
	 * {@inheritdoc}
	 */
	public function regenerateId()
	{
		if (!$this->attached) {
			$this->systemSession->regenerateId();
		}

		// For WS session nothing to do
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId()
	{
		if (!$this->attached) {
			return $this->systemSession->getId();
		}

		return $this->connection->WebSocket->request->getCookie($this->systemSession->getName());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSection($section, $class = 'Nette\Http\SessionSection')
	{
		if (!$this->attached) {
			return $this->systemSession->getSection($section, $class);
		}

		return new SessionSection($this, $section);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasSection($section)
	{
		if (!$this->attached) {
			return $this->systemSession->hasSection($section);
		}

		return isset($this->sections[$this->getConnectionId()][$section]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator() : \ArrayIterator
	{
		if (!$this->attached) {
			return $this->systemSession->getIterator();
		}

		return new \ArrayIterator(array_keys($this->sections[$this->getConnectionId()]));
	}

	/**
	 * {@inheritdoc}
	 */
	public function clean()
	{
		if (!$this->attached) {
			$this->systemSession->clean();

		} else {
			if (!$this->started || empty($this->data[$this->getConnectionId()])) {
				return;
			}

			$nf = &$this->data[$this->getConnectionId()]['__NF'];
			if (isset($nf['META']) && is_array($nf['META'])) {
				foreach ($nf['META'] as $name => $foo) {
					if (empty($nf['META'][$name])) {
						unset($nf['META'][$name]);
					}
				}
			}

			if (empty($nf['META'])) {
				unset($nf['META']);
			}

			if (empty($nf['DATA'])) {
				unset($nf['DATA']);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function setName($name)
	{
		return $this->systemSession->setName($name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->systemSession->getName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setOptions(array $options)
	{
		return $this->systemSession->setOptions($options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOptions()
	{
		return $this->systemSession->getOptions();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setExpiration($time)
	{
		return $this->systemSession->setExpiration($time);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCookieParameters($path, $domain = NULL, $secure = NULL)
	{
		return $this->systemSession->setCookieParameters($path, $domain, $secure);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCookieParameters()
	{
		return $this->systemSession->getCookieParameters();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSavePath($path)
	{
		return $this->systemSession->setSavePath($path);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setHandler(\SessionHandlerInterface $handler)
	{
		return $this->systemSession->setHandler($handler);
	}

	/**
	 * @param string $section
	 *
	 * @return array
	 */
	public function getData(string $section) : array
	{
		if (!$this->attached) {
			return $_SESSION['DATA'][$section];
		}

		return isset($this->data[$this->getConnectionId()]['__NF']['DATA'][$section]) ? $this->data[$this->getConnectionId()]['__NF']['DATA'][$section] : [];
	}

	/**
	 * @return int
	 */
	private function getConnectionId() : int
	{
		return $this->connection->resourceId;
	}
}
