<?php
/**
 * Repository.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Users
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Users;

use Nette;

use Ratchet\ConnectionInterface;

use IPub;
use IPub\Ratchet\Clients;
use IPub\Ratchet\Exceptions;

/**
 * Connected users repository
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Users
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Repository implements IRepository
{
	/**
	 * @var Clients\IStorage
	 */
	private $storage;

	/**
	 * @param Clients\IStorage $storage
	 */
	public function __construct(Clients\IStorage $storage)
	{
		$this->storage = $storage;
	}

	/**
	 * @param ConnectionInterface $connection
	 *
	 * @return Nette\Security\User|NULL
	 */
	public function getUser(ConnectionInterface $connection)
	{
		$storageId = $this->storage->getStorageId($connection);

		try {
			return $this->storage->getClient($storageId)->getUser();

		} catch (Exceptions\ClientNotFoundException $ex) {
			return NULL;
		}
	}

	/**
	 * @param string $username
	 *
	 * @return array|bool
	 */
	public function findByUsername(string $username)
	{
		/** @var Clients\Client $client */
		foreach ($this->storage as $client) {
			$user = $client->getUser();

			if (!$user || !$user->isLoggedIn()) {
				continue;
			}

			if (method_exists($user, 'getUsername') && $user->getUsername() === $username) {
				return [
					'user'   => $client,
					'client' => $client
				];
			}
		}

		return FALSE;
	}

	/**
	 * @param mixed $userId
	 *
	 * @return array|bool
	 */
	public function findById($userId)
	{
		/** @var Clients\Client $client */
		foreach ($this->storage as $client) {
			$user = $client->getUser();

			if (!$user || !$user->isLoggedIn()) {
				continue;
			}

			if ($user->getId() === $userId) {
				return [
					'user'   => $client,
					'client' => $client
				];
			}
		}

		return FALSE;
	}

	/**
	 * @param bool $anonymous
	 *
	 * @return array|bool
	 */
	public function findAll(bool $anonymous = FALSE)
	{
		$results = [];

		/** @var Clients\Client $client */
		foreach ($this->storage as $client) {
			$user = $client->getUser();

			if ($anonymous !== TRUE && (!$user->isLoggedIn() || $user === NULL)) {
				continue;
			}

			$results[] = [
				'user'   => $user,
				'client' => $client,
			];
		}

		return empty($results) ? FALSE : $results;
	}

	/**
	 * @param array $roles
	 *
	 * @return array|bool
	 */
	public function findByRoles(array $roles)
	{
		$results = [];

		/** @var Clients\Client $client */
		foreach ($this->storage as $client) {
			$user = $client->getUser();

			foreach ($user->getRoles() as $role) {
				if (in_array($role, $roles)) {
					$results[] = [
						'user'   => $user,
						'client' => $client,
					];

					continue 1;
				}
			}
		}

		return empty($results) ? FALSE : $results;
	}
}
