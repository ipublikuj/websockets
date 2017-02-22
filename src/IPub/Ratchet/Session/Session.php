<?php
/**
 * Session.php
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

use Ratchet\Session as RSession;
use Ratchet\ConnectionInterface;

use IPub;
use IPub\Ratchet\Exceptions;

/**
 * WebSocket connection session
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Session extends Http\Session
{
	/**
	 * @var array|Http\SessionSection[]
	 */
	private $sections = [];

	/**
	 * Has been session started?
	 *
	 * @var bool
	 */
	private $started = FALSE;

	/**
	 * @var ConnectionInterface
	 */
	private $connection;

	/**
	 * @var array
	 */
	private $sessionData = [];

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var \SessionHandlerInterface
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
	 * @param ConnectionInterface $connection
	 * @param string $name
	 * @param \SessionHandlerInterface|NULL $handler
	 */
	public function __construct(ConnectionInterface $connection, string $name, \SessionHandlerInterface $handler = NULL)
	{
		$this->connection = $connection;

		$this->name = $name;
		$this->handler = $handler;
		$this->nullHandler = new NullHandler;

		$serializer = NULL;

		if ($serializer === NULL) {
			$serialClass = '\\Ratchet\\Session\\Serialize\\' . $this->toClassCase(ini_get('session.serialize_handler')) . 'Handler'; // awesome/terrible hack, eh?

			if (!class_exists($serialClass)) {
				throw new Exceptions\RuntimeException('Unable to parse session serialize handler');
			}

			$serializer = new $serialClass;
		}

		$this->serializer = $serializer;
	}

	/**
	 * @return void
	 */
	public function start()
	{
		if ($this->started) {
			return;
		}

		$this->started = TRUE;

		if (!isset($this->connection->WebSocket) || ($this->id = $this->connection->WebSocket->request->getCookie($this->name)) === NULL) {
			$handler = $this->nullHandler;
			$this->id = '';

		} else {
			$handler = $this->handler;
		}

		$handler->open(session_save_path(), $this->name);

		$rawData = $handler->read($this->id);

		$sessionData = $this->serializer->unserialize($rawData);

		/* structure:
			__NF: Data, Meta, Time
				DATA: section->variable = data
				META: section->variable = Timestamp
		*/
		$nf = &$sessionData['__NF'];

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

		$this->sessionData = $sessionData;
	}

	/**
	 * @return bool
	 */
	public function isStarted() : bool
	{
		return $this->started;
	}

	/**
	 * @return void
	 */
	public function close()
	{
		$this->started = FALSE;

		$this->handler->close();
	}

	/**
	 * @return void
	 *
	 * @throws Exceptions\InvalidStateException
	 */
	public function destroy()
	{
		if (!$this->started) {
			throw new Exceptions\InvalidStateException('Session is not started.');
		}

		$this->started = FALSE;

		$this->sessionData = [];
	}

	/**
	 * @return bool
	 */
	public function exists() : bool
	{
		return $this->started;
	}

	/**
	 * @return void
	 */
	public function regenerateId()
	{

	}

	/**
	 * @return string
	 */
	public function getId() : string
	{
		return $this->id;
	}

	/**
	 * @param string $section
	 *
	 * @return mixed
	 */
	public function getData(string $section)
	{
		return isset($this->sessionData['__NF']['DATA'][$section]) ? $this->sessionData['__NF']['DATA'][$section] : [];
	}

	/**
	 * @param string $section
	 *
	 * @return mixed
	 */
	public function getMeta(string $section)
	{
		return isset($this->sessionData['__NF']['META'][$section]) ? $this->sessionData['__NF']['META'][$section] : [];
	}

	/**
	 * @param string $section
	 * @param string $class
	 *
	 * @return mixed|Http\SessionSection
	 */
	public function getSection($section, $class = 'Nette\Http\SessionSection')
	{
		if (isset($this->sections[$section])) {
			return $this->sections[$section];
		}

		return $this->sections[$section] = parent::getSection($section, $class !== 'Nette\Http\SessionSection' ? $class : 'IPub\Ratchet\Session\SessionSection');
	}

	/**
	 * @param string $section
	 *
	 * @return bool
	 */
	public function hasSection($section) : bool
	{
		return isset($this->sections[$section]);
	}

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator(array_keys($this->sections));
	}


	public function clean()
	{
		if (!$this->started || empty($this->sessionData)) {
			return;
		}

		$nf = &$this->sessionData['__NF'];
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

	/**
	 * @param string $name
	 *
	 * @return void
	 *
	 * @throws Exceptions\NotImplementedException
	 */
	public function setName($name)
	{
		throw new Exceptions\NotImplementedException;
	}

	/**
	 * @return string
	 */
	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * @param array $options
	 *
	 * @return void
	 *
	 * @throws Exceptions\NotImplementedException
	 */
	public function setOptions(array $options = [])
	{
		throw new Exceptions\NotImplementedException;
	}

	/**
	 * @return array
	 */
	public function getOptions() : array
	{
		return [];
	}

	/**
	 * @param string $langDef Input to convert
	 *
	 * @return string
	 */
	private function toClassCase(string $langDef) : string
	{
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $langDef)));
	}
}
