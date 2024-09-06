<?php declare(strict_types=1);

namespace Qliro\QliroOne\Model\Logger\Handler;

use Monolog\Logger;
use Magento\Framework\Filesystem\Driver\File;
use Qliro\QliroOne\Model\Config;
use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Handler\AbstractHandler;

/**
 * Handler for logging errors to specific file
 */
class ErrorFile extends BaseHandler
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var int
     */
    protected $loggerType = Logger::ERROR;

    public function __construct(
        Config $config,
        File $filesystemDriver,
        ?string $filePath = null,
        ?string $fileName = 'var/log/qliroone_error.log'
    ) {
        $this->config = $config;
        parent::__construct($filesystemDriver, $filePath, $fileName);
    }

    /**
     * @inheritDoc
     */
    public function setLevel($level): AbstractHandler
    {
        $configLevel = Logger::toMonologLevel($this->config->getLoggingLevel());
        // If the config'd level is higher than the handler default, we use it
        $this->level = ($configLevel >= $level) ? $configLevel : $level;
        return $this;
    }
}
