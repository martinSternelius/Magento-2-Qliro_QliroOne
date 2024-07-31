<?php declare(strict_types=1);

namespace Qliro\QliroOne\Service\RecurringPayments;

use Qliro\QliroOne\Model\Logger\Manager;
use Qliro\QliroOne\Service\RecurringPayments\Data as DataService;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\DataObject\Copy;
use Magento\Sales\Model\OrderRepository;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Checkout\Model\ShippingInformationFactory;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Qliro\QliroOne\Model\Management\CreateMerchantPayment;
use Qliro\QliroOne\Api\Data\RecurringInfoInterface;
use Qliro\QliroOne\Model\Management\Quote as QliroManagement;
use Magento\Checkout\Model\Session;
use \Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Service class for placing recurring orders
 */
class PlaceOrders
{
    private Create $orderCreate;

    private QuoteFactory $quoteFactory;

    private QuoteManagement $quoteManagement;

    private QuoteRepository $quoteRepo;

    private ShippingInformationFactory $shipInfoFactory;

    private ShippingInformationManagement $shipInfoManagement;

    private OrderRepository $orderRepo;

    private Copy $objectCopyService;

    private DataService $dataService;

    private Manager $logger;

    private CreateMerchantPayment $createMerchantPaymentManagement;

    private QliroManagement $qliroManagement;

    private Session $checkoutSession;

    private OrderRepositoryInterface $orderRepository;

    private SearchCriteriaBuilder $searchCriteriaBuilder;

    private array $results = [];

    private string $personalNumber;

    public function __construct(
        Create $orderCreate,
        QuoteFactory $quoteFactory,
        QuoteManagement $quoteManagement,
        QuoteRepository $quoteRepo,
        ShippingInformationFactory $shipInfoFactory,
        ShippingInformationManagement $shipInfoManagement,
        OrderRepository $orderRepo,
        Copy $objectCopyService,
        DataService $dataService,
        QliroManagement $qliroManagement,
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Manager $logger,
        CreateMerchantPayment $createMerchantPaymentManagement
    ) {
        $this->orderCreate = $orderCreate;
        $this->quoteFactory = $quoteFactory;
        $this->quoteManagement = $quoteManagement;
        $this->quoteRepo = $quoteRepo;
        $this->shipInfoFactory = $shipInfoFactory;
        $this->shipInfoManagement = $shipInfoManagement;
        $this->orderRepo = $orderRepo;
        $this->objectCopyService = $objectCopyService;
        $this->dataService = $dataService;
        $this->qliroManagement = $qliroManagement;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->createMerchantPaymentManagement = $createMerchantPaymentManagement;
    }

    /**
     * Place recurring orders
     *
     * @param RecurringInfoInterface[] $recurringInfos
     */
    public function placeRecurringOrders(array $recurringInfos): void
    {
        foreach ($recurringInfos as $recurringInfo) {
            $orderId = (int)$recurringInfo->getOriginalOrderId();
            $this->results[$orderId] = [];
            $this->commit($recurringInfo);
        }
    }

    /**
     * @param int $token
     * @return array
     */
    public function fetchResult(string $orderId): array
    {
        return $this->results[$orderId] ?? [];
    }

    /**
     * Commit a single recurring order and store the result
     *
     * @param int $originalOrderId
     * @return void
     */
    private function commit(RecurringInfoInterface $recurringInfo): void
    {
        $originalOrderId = $recurringInfo->getOriginalOrderId();
        try {
            $order = $this->orderRepo->get($originalOrderId);
        } catch (\Exception $e) {
            $this->logError(
                $originalOrderId,
                [sprintf('The original order with entity_id %s could not be loaded.', $originalOrderId)]
            );
            $this->results[$originalOrderId] = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            return;
        }

        $originalOrderId = (int)$order->getEntityId();
        // Populate quote payment with order payment data
        $this->orderCreate->setQuote($this->quoteFactory->create());
        $this->orderCreate->initFromOrder($order);
        $quote = $this->orderCreate->getQuote();
        $quote->setStoreId($order->getStoreId());
        $quote->reserveOrderId();
        $this->objectCopyService->copyFieldsetToTarget(
            'sales_convert_order_payment',
            'to_quote_payment',
            $order->getPayment(),
            $quote->getPayment()
        );

        // Update recurring info for new Quote
        $this->dataService->scheduleNextRecurringOrder($quote);
        $this->quoteRepo->save($quote);
        $quote->setShippingDescription($order->getShippingDescription());
        $this->checkoutSession->replaceQuote($quote);
        $this->qliroManagement->setQuote($quote)->getLinkFromQuote();
        if($order->getFee() > 0){
            $this->qliroManagement->updateFee($order->getFee() + $order->getFeeTax());
        }
        $this->qliroManagement->updateShippingPrice($order->getShippingInclTax());
        $this->saveQuoteShippingInfo($quote, $order);
        $this->createMerchantPaymentManagement->setQuote($quote);
        if($recurringInfo->getPersonalNumber()){
            $quote->setCustomerPersonalNumber($recurringInfo->getPersonalNumber());
        }
        $this->createMerchantPaymentManagement->execute();

        $newOrder = $this->getOrderByQuoteId($quote->getId());
        if($newOrder){
            $newOrder->setRecurringParentId($order->getIncrementId());
            $this->orderRepository->save($newOrder);
        }

        $recurringInfo->setNextOrderDate($this->dataService->quoteGetter($quote)->getNextOrderDate());
        $this->results[$originalOrderId] = [
            'success' => 'true'
        ];
    }

    /**
     * Log error messages
     *
     * @param mixed $orderId – Entity ID
     * @param array $messages
     * @return void
     */
    public function logError(int $orderId, array $messages): void
    {
        $this->logger->error(sprintf('[RecurringPayment Error Start. Original order: %s]', $orderId));
        foreach ($messages as $message) {
            $this->logger->error($message);
        }
        $this->logger->error(sprintf('[RecurringPayment Error End. Original order: %s]', $orderId));
    }

    /**
     * Transfer shipping info from order to new Quote
     *
     * @param Quote $quote
     * @param string $methodCode
     * @param string $carrier
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    private function saveQuoteShippingInfo(Quote $quote, Order $order): void
    {
        if ($order->getIsVirtual()) {
            return;
        }

        $shippingMethod = $order->getShippingMethod(true);
        $carrier = $shippingMethod->getCarrierCode();
        $method = $shippingMethod->getMethod();

        $shipInfo = $this->shipInfoFactory->create();
        $shipInfo->setBillingAddress($quote->getBillingAddress());
        $shipInfo->setShippingAddress($quote->getShippingAddress());
        $shipInfo->setShippingCarrierCode($carrier);
        $shipInfo->setShippingMethodCode(strtolower($method));
        $this->shipInfoManagement->saveAddressInformation($quote->getId(), $shipInfo);
    }

    public function getOrderByQuoteId($quoteId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('quote_id', $quoteId, 'eq')
            ->setPageSize(1)
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        if (!empty($orders)) {
            return reset($orders);
        }

        return false;
    }
}
