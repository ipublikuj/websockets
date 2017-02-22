<?php
/**
 * SessionSection.php
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

use IPub;
use IPub\Ratchet\Exceptions;

/**
 * WebSocket connection session section
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class SessionSection extends Http\SessionSection
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * Session metadata storage
	 *
	 * @var array
	 */
	private $meta = FALSE;

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @param Session $session
	 * @param string $name
	 */
	public function __construct(Session $session, $name)
	{
		parent::__construct($session, $name);

		$this->session = $session;
		$this->name = $name;
	}

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator() : \ArrayIterator
	{
		$this->start();

		return new \ArrayIterator($this->data);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return void
	 *
	 * @throws Exceptions\NotImplementedException
	 */
	public function __set($name, $value)
	{
		throw new Exceptions\NotImplementedException;
	}

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function &__get($name)
	{
		$this->start();

		if ($this->warnOnUndefined && !array_key_exists($name, $this->data)) {
			trigger_error("The variable '$name' does not exist in session section", E_USER_NOTICE);
		}

		return $this->data[$name];
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		$this->start();

		return isset($this->data[$name]);
	}

	/**
	 * @param string $name
	 *
	 * @return void
	 *
	 * @throws Exceptions\NotImplementedException
	 */
	public function __unset($name)
	{
		throw new Exceptions\NotImplementedException;
	}

	/**
	 * @param \DateTimeInterface|int|string $time
	 * @param string|array|NULL $variables
	 *
	 * @return void
	 */
	public function setExpiration($time, $variables = NULL)
	{
		//
	}

	/**
	 * @param string|array|NULL $variables
	 *
	 * @return void
	 */
	public function removeExpiration($variables = NULL)
	{
		//
	}

	/**
	 * @return void
	 */
	public function remove()
	{
		$this->data = [];
	}

	/**
	 * @return void
	 */
	protected function start()
	{
		if ($this->meta === FALSE) {
			$this->session->start();

			$this->data = $this->session->getData($this->name);
			$this->meta = $this->session->getMeta($this->name);
		}
	}
}
