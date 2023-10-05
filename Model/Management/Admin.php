<?php
/**
 * Copyright © Qliro AB. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Qliro\QliroOne\Model\Management;

use Qliro\QliroOne\Api\Client\OrderManagementInterface;
use Qliro\QliroOne\Api\LinkRepositoryInterface;
use Qliro\QliroOne\Model\Logger\Manager as LogManager;

/**
 * QliroOne management class
 */
class Admin extends AbstractManagement
{
    /**
     * @var \Qliro\QliroOne\Api\Client\OrderManagementInterface
     */
    private $orderManagementApi;

    /**
     * @var \Qliro\QliroOne\Api\LinkRepositoryInterface
     */
    private $linkRepository;

    /**
     * @var \Qliro\QliroOne\Model\Logger\Manager
     */
    private $logManager;

    /**
     * Inject dependencies
     *
     * @param OrderManagementInterface $orderManagementApi
     * @param LinkRepositoryInterface $linkRepository
     * @param LogManager $logManager
     */
    public function __construct(
        OrderManagementInterface $orderManagementApi,
        LinkRepositoryInterface $linkRepository,
        LogManager $logManager
    ) {
        $this->orderManagementApi = $orderManagementApi;
        $this->linkRepository = $linkRepository;
        $this->logManager = $logManager;
    }

    /**
     * Get Admin Qliro order after it was already placed
     *
     * @param int $qliroOrderId
     * @return \Qliro\QliroOne\Api\Data\AdminOrderInterface
     */
    public function getQliroOrder($qliroOrderId)
    {
        $qliroOrder = null; // Placeholder, QliroOne order will never be returned as null

        try {
            $link = $this->linkRepository->getByQliroOrderId($qliroOrderId);
            $this->logManager->setMerchantReference($link->getReference());
            $qliroOrder = $this->orderManagementApi->getOrder($qliroOrderId);
        } catch (\Exception $exception) {
            $this->logManager->critical(
                $exception,
                [
                    'extra' => [
                        'qliro_order_id' => isset($link) ? $link->getOrderId() : $qliroOrderId,
                    ],
                ]
            );
        }

        return $qliroOrder;
    }
}
