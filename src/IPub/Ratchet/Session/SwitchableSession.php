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

use Ratchet\ConnectionInterface;

use IPub;
use IPub\Ratchet\Exceptions;
use Tracy\Debugger;

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
	 * @var bool
	 */
	private $attached = FALSE;

	/**
	 * @param Http\Session $session
	 */
	public function __construct(Http\Session $session)
	{
		$this->systemSession = $session;
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

		if (!isset($connection->session)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Connection "%s" is without session.', $connection->resourceId));
		}

		$this->attached = TRUE;

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

		if ($this->attached && $connection->resourceId === $this->connection->resourceId) {
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

		} else {
			$this->getConnectionSession()->start();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function isStarted()
	{
		if (!$this->attached) {
			return $this->systemSession->isStarted();
		}

		return $this->getConnectionSession()->isStarted();
	}

	/**
	 * {@inheritdoc}
	 */
	public function close()
	{
		if (!$this->attached) {
			$this->systemSession->close();

		} else {
			$this->getConnectionSession()->close();
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
			$this->getConnectionSession()->destroy();
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

		return $this->getConnectionSession()->exists();
	}

	/**
	 * {@inheritdoc}
	 */
	public function regenerateId()
	{
		if (!$this->attached) {
			$this->systemSession->regenerateId();

		} else {
			$this->getConnectionSession()->regenerateId();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId()
	{
		if (!$this->attached) {
			return $this->systemSession->getId();
		}

		return $this->getConnectionSession()->getId();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSection($section, $class = 'Nette\Http\SessionSection')
	{
		if (!$this->attached) {
			return $this->systemSession->getSection($section, $class);
		}

		if (Utils\Strings::startsWith($section, 'Nette.Http.UserStorage')) {
			return new UserStorageSessionSection($this, $section);

		} else {
			return $this->getConnectionSession()->getSection($section, $class);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasSection($section)
	{
		if (!$this->attached) {
			return $this->systemSession->hasSection($section);
		}

		return $this->getConnectionSession()->hasSection($section);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		if (!$this->attached) {
			return $this->systemSession->getIterator();
		}

		return $this->getConnectionSession()->getIterator();
	}

	/**
	 * {@inheritdoc}
	 */
	public function clean()
	{
		if (!$this->attached) {
			$this->systemSession->clean();

		} else {
			$this->getConnectionSession()->clean();
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
	 * @param string $sectionName
	 *
	 * @return array
	 */
	public function getData(string $sectionName) : array
	{
		if (!$this->attached) {
			return $_SESSION['DATA'][$sectionName];
		}

		return $this->getConnectionSession()->getData($sectionName);
	}

	/**
	 * @return Session
	 */
	private function getConnectionSession() : Session
	{
		return $this->connection->session;
	}
}
