<?php declare(strict_types=1);

namespace Qliro\QliroOne\Model\Management;

use Qliro\QliroOne\Api\Data\LinkInterfaceFactory;
use Magento\Quote\Api\CartManagementInterface;
use Qliro\QliroOne\Api\LinkRepositoryInterface;
use Qliro\QliroOne\Model\MerchantPayment\Builder\CreateRequestBuilder;
use Qliro\QliroOne\Model\Logger\Manager;
use Qliro\QliroOne\Model\Api\Client\OrderManagement;
use Qliro\QliroOne\Service\General\LinkService;

class CreateMerchantPayment extends AbstractManagement
{
    const DEFAULT_QLIRO_STATUS = 'MerchantPaymentCreated';

    /**
     * @var \Qliro\QliroOne\Service\General\LinkService
     */
    private LinkService $linkService;

    /**
     * @var \Qliro\QliroOne\Model\MerchantPayment\Builder\CreateRequestBuilder
     */
    private CreateRequestBuilder $createRequestBuilder;

    /**
     * @var \Qliro\QliroOne\Api\Data\LinkInterfaceFactory
     */
    private LinkInterfaceFactory $linkFactory;

    /**
     * @var \Qliro\QliroOne\Api\LinkRepositoryInterface
     */
    private LinkRepositoryInterface $linkRepository;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private CartManagementInterface $quoteManagement;

    /**
     * @var \Qliro\QliroOne\Model\Api\Client\OrderManagement
     */
    private OrderManagement $qliroOrderManagement;

    /**
     * @var \Qliro\QliroOne\Model\Logger\Manager
     */
    private Manager $logManager;

    public function __construct(
        LinkService $linkService,
        CreateRequestBuilder $createRequestBuilder,
        LinkInterfaceFactory $linkFactory,
        LinkRepositoryInterface $linkRepository,
        CartManagementInterface $quoteManagement,
        OrderManagement $qliroOrderManagement,
        Manager $logManager
    ) {
        $this->linkService = $linkService;
        $this->createRequestBuilder = $createRequestBuilder;
        $this->linkFactory = $linkFactory;
        $this->linkRepository = $linkRepository;
        $this->logManager = $logManager;
        $this->quoteManagement = $quoteManagement;
        $this->qliroOrderManagement = $qliroOrderManagement;
    }

    /**
     * Creates Magento Order and Merchant Payment associated with each other
     *
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute(): void
    {
        $quote = $this->getQuote();
        $quoteId = $quote->getEntityId();

        $orderReference = $this->linkService->generateOrderReference($quote);
        $this->logManager->setMerchantReference($orderReference);
        $this->logManager->setMark('CREATE MERCHANT PAYMENT');

        $request = $this->createRequestBuilder->setQuote($quote)->create();
        $request->setMerchantReference($orderReference);

        $merchantPaymentResponse = null;
        try {
            // First try creating the Merchant Payment, then the Magento order
            $merchantPaymentResponse = $this->qliroOrderManagement->createMerchantPayment(
                $request,
                (int)$quote->getStoreId()
            );
            $newOrderId = $this->quoteManagement->placeOrder($quote->getId());

            $link = $this->linkFactory->create();

            // A real Quote Snapshot is not needed here but the value is required
            $link->setQuoteSnapshot('merchantPayment');
            
            $link->setQuoteId($quoteId);
            $link->setReference($orderReference);
            $link->setQliroOrderId($merchantPaymentResponse->getOrderId());
            $link->setOrderId($newOrderId);
            $link->setQliroOrderStatus(self::DEFAULT_QLIRO_STATUS);
            $link->setIsActive(1); // The convention is setting the link as Active if the order is placed without errors
            $this->linkRepository->save($link);
        } catch (\Exception $exception) {
            $this->logManager->critical($exception->getMessage());
            return;
        }
    }
}
