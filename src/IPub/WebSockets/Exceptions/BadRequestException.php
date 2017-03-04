<?php
/**
 * BadRequestException.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     Exceptions
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Exceptions;

use Nette;
use Nette\Application;

class BadRequestException extends Application\BadRequestException implements IException
{
}
