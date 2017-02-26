<?php
/**
 * IDriver.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     WAMP
 * @since          1.0.0
 *
 * @date           26.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\WAMP\V1\Topics\Drivers;

/**
 * Classic memory topic storage driver
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     WAMP
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class InMemory implements IDriver
{
	/**
	 * @var array
	 */
	private $elements;

	public function __construct()
	{
		$this->elements = [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch(string $id)
	{
		if (!$this->contains($id)) {
			return FALSE;
		}

		return $this->elements[$id];
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetchAll() : array
	{
		return array_values($this->elements);
	}

	/**
	 * {@inheritdoc}
	 */
	public function contains(string $id) : bool
	{
		return isset($this->elements[$id]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(string $id, $data, int $lifeTime = 0) : bool
	{
		$this->elements[$id] = $data; // Lifetime is not supported

		return TRUE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(string $id) : bool
	{
		unset($this->elements[$id]);

		return TRUE;
	}
}
