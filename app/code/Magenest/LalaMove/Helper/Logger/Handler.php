<?php

namespace Magenest\LalaMove\Helper\Logger;
use Monolog\Logger;

use Magento\Framework\Logger\Handler\Base;

/**
 * Class Handler
 * @package Magenest\Lalamove\Helper\Logger
 */
class Handler extends Base
{
	/**
	 * @var string
	 */
	protected $fileName = '/var/log/shipment/lalamove.log';

	/**
	 * @var int
	 */
	protected $loggerType = Logger::INFO;
}
