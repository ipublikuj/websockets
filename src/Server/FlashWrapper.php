<?php declare(strict_types = 1);

namespace IPub\WebSockets\Server;

use IPub\WebSockets\Entities;
use IPub\WebSockets\Exceptions;
use SimpleXMLElement;
use Throwable;

final class FlashWrapper implements IWrapper
{

	/**
	 * Contains the root policy node
	 *
	 * @var string
	 */
	private $policy = '<?xml version="1.0"?><!DOCTYPE cross-domain-policy SYSTEM "http://www.adobe.com/xml/dtds/cross-domain-policy.dtd"><cross-domain-policy></cross-domain-policy>';

	/**
	 * Stores an array of allowed domains and their ports
	 *
	 * @var array
	 */
	private $access = [];

	/** @var string */
	private $siteControl = '';

	/** @var string */
	private $cache = '';

	/** @var string */
	private $cacheValid = false;

	/**
	 * Add a domain to an allowed access list.
	 *
	 * @param string $domain Specifies a requesting domain to be granted access. Both named domains and IP
	 *                       addresses are acceptable values. Subdomains are considered different domains. A wildcard (*) can
	 *                       be used to match all domains when used alone, or multiple domains (subdomains) when used as a
	 *                       prefix for an explicit, second-level domain name separated with a dot (.)
	 * @param string $ports  A comma-separated list of ports or range of ports that a socket connection
	 *                       is allowed to connect to. A range of ports is specified through a dash (-) between two port numbers.
	 *                       Ranges can be used with individual ports when separated with a comma. A single wildcard (*) can
	 *                       be used to allow all ports.
	 * @param bool $secure
	 *
	 * @return void
	 *
	 * @throws Exceptions\UnexpectedValueException
	 */
	public function addAllowedAccess($domain, $ports = '*', $secure = false): void
	{
		if (!$this->validateDomain($domain)) {
			throw new Exceptions\UnexpectedValueException('Invalid domain');
		}

		if (!$this->validatePorts($ports)) {
			throw new Exceptions\UnexpectedValueException('Invalid Port');
		}

		$this->access[] = [$domain, $ports, $secure];
		$this->cacheValid = false;
	}

	/**
	 * Removes all domains from the allowed access list
	 *
	 * @return void
	 */
	public function clearAllowedAccess(): void
	{
		$this->access = [];
		$this->cacheValid = false;
	}

	/**
	 * site-control defines the meta-policy for the current domain. A meta-policy specifies acceptable
	 * domain policy files other than the master policy file located in the target domain's root and named
	 * crossdomain.xml.
	 *
	 * @param string $permittedCrossDomainPolicies
	 *
	 * @return void
	 *
	 * @throws Exceptions\UnexpectedValueException
	 */
	public function setSiteControl($permittedCrossDomainPolicies = 'all'): void
	{
		if (!$this->validateSiteControl($permittedCrossDomainPolicies)) {
			throw new Exceptions\UnexpectedValueException('Invalid site control set');
		}

		$this->siteControl = $permittedCrossDomainPolicies;
		$this->cacheValid = false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handleOpen(Entities\Clients\IClient $client): void
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function handleMessage(Entities\Clients\IClient $client, string $message): void
	{
		if (!$this->cacheValid) {
			$this->cache = $this->renderPolicy()->asXML();
			$this->cacheValid = true;
		}

		$client->getConnection()->write($this->cache . "\0");
		$client->getConnection()->end();
	}

	/**
	 * {@inheritdoc}
	 */
	public function handleClose(Entities\Clients\IClient $client): void
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function handleError(Entities\Clients\IClient $client, Throwable $ex): void
	{
		$client->getConnection()->end();
	}

	/**
	 * Builds the crossdomain file based on the template policy
	 *
	 * @return SimpleXMLElement
	 *
	 * @throws Exceptions\UnexpectedValueException
	 */
	public function renderPolicy(): SimpleXMLElement
	{
		$policy = new SimpleXMLElement($this->policy);

		$siteControl = $policy->addChild('site-control');

		if ($this->siteControl === '') {
			$this->setSiteControl();
		}

		$siteControl->addAttribute('permitted-cross-domain-policies', $this->siteControl);

		if ($this->access === []) {
			throw new Exceptions\UnexpectedValueException('You must add a domain through addAllowedAccess()');
		}

		foreach ($this->access as $access) {
			$tmp = $policy->addChild('allow-access-from');
			$tmp->addAttribute('domain', (string) $access[0]);
			$tmp->addAttribute('to-ports', (string) $access[1]);
			$tmp->addAttribute('secure', ($access[2] === true) ? 'true' : 'false');
		}

		return $policy;
	}

	/**
	 * Make sure the proper site control was passed
	 *
	 * @param string $permittedCrossDomainPolicies
	 *
	 * @return bool
	 */
	private function validateSiteControl($permittedCrossDomainPolicies): bool
	{
		//'by-content-type' and 'by-ftp-filename' are not available for sockets
		return in_array($permittedCrossDomainPolicies, ['none', 'master-only', 'all'], true);
	}

	/**
	 * Validate for proper domains (wildcards allowed)
	 *
	 * @param string $domain
	 *
	 * @return bool
	 */
	private function validateDomain(string $domain): bool
	{
		return (bool) preg_match('/^((http(s)?:\/\/)?([a-z0-9-_]+\.|\*\.)*([a-z0-9-_\.]+)|\*)$/i', $domain);
	}

	/**
	 * Make sure valid ports were passed
	 *
	 * @param string $port
	 *
	 * @return bool
	 */
	private function validatePorts(string $port): bool
	{
		return (bool) preg_match('/^(\*|(\d+[,-]?)*\d+)$/', $port);
	}

}
