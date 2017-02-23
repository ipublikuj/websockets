<?php
/**
 * UserStorageSessionSection.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 * @since          1.0.0
 *
 * @date           23.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Session;

use Nette;
use Nette\Http;

use IPub;
use IPub\Ratchet\Exceptions;

/**
 * WebSocket connection session section for user storage
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class UserStorageSessionSection extends Http\SessionSection
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var SwitchableSession
	 */
	private $session;

	/**
	 * @param SwitchableSession $session
	 * @param $name
	 */
	public function __construct(SwitchableSession $session, $name)
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
		$data = $this->start();

		return new \ArrayIterator($data);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function __set($name, $value)
	{
		//
	}

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function &__get($name)
	{
		$data = $this->start();

		if ($this->warnOnUndefined && !array_key_exists($name, $data)) {
			trigger_error("The variable '$name' does not exist in session section", E_USER_NOTICE);
		}

		return $data[$name];
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		$data = $this->start();

		return isset($data[$name]);
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
		//
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
		//
	}

	/**
	 * @return array
	 */
	protected function start() : array
	{
		$this->session->start();

		return $this->session->getData($this->name);
	}
}
