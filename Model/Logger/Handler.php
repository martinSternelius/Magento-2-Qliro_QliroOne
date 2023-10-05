<?php
/**
 * Copyright © Qliro AB. All rights reserved.
 * See LICENSE.txt for license details.
 */

// @codingStandardsIgnoreFile
// phpcs:ignoreFile

namespace Qliro\QliroOne\Model\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Qliro\QliroOne\Model\Config;
use Monolog\Formatter\FormatterInterface;
use Qliro\QliroOne\Model\ResourceModel\LogRecord;
use Qliro\QliroOne\Api\Data\LogRecordInterface;

/**
 * Logger DB handler class
 */
class Handler extends AbstractProcessingHandler
{
    /**
     * @var \Magento\Payment\Model\Method\Adapter
     */
    private $config;

    /**
     * @var \Qliro\QliroOne\Model\Logger\ConnectionProvider
     */
    private $connectionProvider;

    /**
     * Handler constructor.
     *
     * @param FormatterInterface $formatter
     * @param Config $config
     * @param \Qliro\QliroOne\Model\Logger\ConnectionProvider $connectionProvider
     */
    public function __construct(
        FormatterInterface $formatter,
        Config $config,
        ConnectionProvider $connectionProvider
    ) {
        $this->formatter = $formatter;
        $this->config = $config;

        parent::__construct();
        $this->connectionProvider = $connectionProvider;
    }

    /**
     * Make the level be dynamically aware of the configured log level
     *
     * @param array $record
     * @return bool
     */
    public function isHandling(array $record)
    {
        return $record['level'] >= $this->getLevel();

    }

    /**
     * Make the level be dynamically aware of the configured log level
     *
     * @return int
     */
    public function getLevel()
    {
        $this->level = Logger::toMonologLevel($this->config->getLoggingLevel());

        return $this->level;
    }

    /**
     * @param array $record
     * @throws \DomainException
     */
    protected function write(array $record)
    {
        $context = $record['context'];
        $record = $record['formatted'];

        $mark = $context['mark'] ?? null;
        $message = ($mark ? sprintf('%s: ', strtoupper($mark)) : null) . $record['message'];

        $connection = $this->connectionProvider->getConnection();
        $connection->insert(
            $connection->getTableName(LogRecord::TABLE_LOG),
            [
                LogRecordInterface::FIELD_DATE => $record['datetime'],
                LogRecordInterface::FIELD_LEVEL => $record['level_name'],
                LogRecordInterface::FIELD_MESSAGE => $message,
                LogRecordInterface::FIELD_REFERENCE => $context['reference'] ?? '',
                LogRecordInterface::FIELD_TAGS => $context['tags'] ?? '',
                LogRecordInterface::FIELD_PROCESS_ID => $context['process_id'] ?? '',
                LogRecordInterface::FIELD_EXTRA => $this->encodeExtra($context['extra'] ?? ''),
            ]
        );
    }

    /**
     * @param array|string $data
     * @return string
     */
    private function encodeExtra($data)
    {
        try {
            $serializedData = is_array($data) ? $this->serialize($data) : $data;
        } catch (\Exception $exception) {
            $serializedData = null;
        }

        return $serializedData;
    }

    /**
     * Serialize JSON using pretty print and some other options
     *
     * @param array $data
     * @return false|string
     */
    private function serialize($data)
    {
        return \json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE);
    }
}
