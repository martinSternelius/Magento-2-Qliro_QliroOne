<?php
/**
 * Copyright © Qliro AB. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Qliro\QliroOne\Model\Management;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Qliro\QliroOne\Api\Client\MerchantInterface;
use Qliro\QliroOne\Api\Data\LinkInterface;
use Qliro\QliroOne\Api\Data\LinkInterfaceFactory;
use Qliro\QliroOne\Api\Data\QliroOrderCustomerInterface;
use Qliro\QliroOne\Api\Data\CheckoutStatusInterface;
use Qliro\QliroOne\Api\HashResolverInterface;
use Qliro\QliroOne\Api\LinkRepositoryInterface;
use Qliro\QliroOne\Model\Config;
use Qliro\QliroOne\Model\ContainerMapper;
use Qliro\QliroOne\Model\Fee;
use Qliro\QliroOne\Model\Logger\Manager as LogManager;
use Qliro\QliroOne\Model\Method\QliroOne;
use Qliro\QliroOne\Model\QliroOrder\Builder\CreateRequestBuilder;
use Qliro\QliroOne\Model\QliroOrder\Builder\UpdateRequestBuilder;
use Qliro\QliroOne\Model\QliroOrder\Converter\CustomerConverter;
use Magento\Framework\Serialize\Serializer\Json;
use Qliro\QliroOne\Helper\Data as Helper;

/**
 * QliroOne management class
 */
class Quote extends AbstractManagement
{
    /**
     * @var \Qliro\QliroOne\Model\Config
     */
    private $qliroConfig;

    /**
     * @var \Qliro\QliroOne\Api\Client\MerchantInterface
     */
    private $merchantApi;

    /**
     * @var \Qliro\QliroOne\Model\QliroOrder\Builder\CreateRequestBuilder
     */
    private $createRequestBuilder;

    /**
     * @var \Qliro\QliroOne\Api\Data\LinkInterfaceFactory
     */
    private $linkFactory;

    /**
     * @var \Qliro\QliroOne\Api\LinkRepositoryInterface
     */
    private $linkRepository;

    /**
     * @var \Qliro\QliroOne\Api\HashResolverInterface
     */
    private $hashResolver;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Qliro\QliroOne\Model\ContainerMapper
     */
    private $containerMapper;

    /**
     * @var \Qliro\QliroOne\Model\Logger\Manager
     */
    private $logManager;

    /**
     * @var \Qliro\QliroOne\Model\QliroOrder\Builder\UpdateRequestBuilder
     */
    private $updateRequestBuilder;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    /**
     * @var \Qliro\QliroOne\Model\QliroOrder\Converter\CustomerConverter
     */
    private $customerConverter;

    /**
     * @var \Qliro\QliroOne\Model\Fee
     */
    private $fee;

    /**
     * @var \Qliro\QliroOne\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * Inject dependencies
     * @param Config $qliroConfig
     * @param MerchantInterface $merchantApi
     * @param CreateRequestBuilder $createRequestBuilder
     * @param UpdateRequestBuilder $updateRequestBuilder
     * @param CustomerConverter $customerConverter
     * @param LinkInterfaceFactory $linkFactory
     * @param LinkRepositoryInterface $linkRepository
     * @param HashResolverInterface $hashResolver
     * @param CartRepositoryInterface $quoteRepository
     * @param ContainerMapper $containerMapper
     * @param LogManager $logManager
     * @param Json $json
     * @param Fee $fee
     * @param Helper $helper
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Config $qliroConfig,
        MerchantInterface $merchantApi,
        CreateRequestBuilder $createRequestBuilder,
        UpdateRequestBuilder $updateRequestBuilder,
        CustomerConverter $customerConverter,
        LinkInterfaceFactory $linkFactory,
        LinkRepositoryInterface $linkRepository,
        HashResolverInterface $hashResolver,
        CartRepositoryInterface $quoteRepository,
        ContainerMapper $containerMapper,
        LogManager $logManager,
        Json $json,
        Fee $fee,
        Helper $helper,
        ManagerInterface $eventManager
    ) {
        $this->qliroConfig = $qliroConfig;
        $this->merchantApi = $merchantApi;
        $this->createRequestBuilder = $createRequestBuilder;
        $this->linkFactory = $linkFactory;
        $this->linkRepository = $linkRepository;
        $this->hashResolver = $hashResolver;
        $this->quoteRepository = $quoteRepository;
        $this->containerMapper = $containerMapper;
        $this->logManager = $logManager;
        $this->updateRequestBuilder = $updateRequestBuilder;
        $this->json = $json;
        $this->customerConverter = $customerConverter;
        $this->fee = $fee;
        $this->helper = $helper;
        $this->eventManager = $eventManager;
    }

    /**
     * Recalculate the quote, its totals, it's addresses and shipping rates, then saving quote
     *
     * @throws \Exception
     */
    public function recalculateAndSaveQuote()
    {
        $data['method'] = QliroOne::PAYMENT_METHOD_CHECKOUT_CODE;

        $quote = $this->getQuote();
        $customer = $quote->getCustomer();
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();

        if ($quote->isVirtual()) {
            $billingAddress->setPaymentMethod($data['method']);
        } else {
            $shippingAddress->setPaymentMethod($data['method']);
        }

        $billingAddress->save();

        if (!$quote->isVirtual()) {
            $shippingAddress->save();
        }

        $quote->assignCustomerWithAddressChange($customer, $billingAddress, $shippingAddress);
        $quote->setTotalsCollectedFlag(false);

        if (!$quote->isVirtual()) {
            if ($this->qliroConfig->isUnifaunEnabled($quote->getStoreId())) {
                $shippingAddress->setShippingMethod(
                    \Qliro\QliroOne\Model\Carrier\Unifaun::QLIRO_UNIFAUN_SHIPPING_CODE
                );
            }
            $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->save();
        }

        $extensionAttributes = $quote->getExtensionAttributes();

        if (!empty($extensionAttributes)) {
            $shippingAssignments = $extensionAttributes->getShippingAssignments();

            if ($shippingAssignments) {
                foreach ($shippingAssignments as $assignment) {
                    $assignment->getShipping()->setMethod($shippingAddress->getShippingMethod());
                }
            }
        }
        $quote->collectTotals();

        $payment = $quote->getPayment();
        $payment->importData($data);

        $shippingAddress->save();
        $this->quoteRepository->save($quote);
    }

    /**
     * Get a link for the current quote
     *
     * @return \Qliro\QliroOne\Api\Data\LinkInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function getLinkFromQuote()
    {
        $quote = $this->getQuote();
        $quoteId = $quote->getEntityId();

        try {
            $link = $this->linkRepository->getByQuoteId($quoteId);
        } catch (NoSuchEntityException $exception) {
            /** @var \Qliro\QliroOne\Api\Data\LinkInterface $link */
            $link = $this->linkFactory->create();
            $link->setRemoteIp($this->helper->getRemoteIp());
            $link->setIsActive(true);
            $link->setQuoteId($quoteId);
        }

        if ($link->getQliroOrderId()) {
            $this->update($link->getQliroOrderId());
        } else {
            $this->logManager->debug('create new qliro order'); // @todo: remove
            $orderReference = $this->generateOrderReference();
            $this->logManager->setMerchantReference($orderReference);

            $request = $this->createRequestBuilder->setQuote($quote)->create();
            $request->setMerchantReference($orderReference);

            try {
                $orderId = $this->merchantApi->createOrder($request);
            } catch (\Exception $exception) {
                $orderId = null;
            }

            $hash = $this->generateUpdateHash($quote);
            $link->setQuoteSnapshot($hash);

            $link->setIsActive(true);
            $link->setReference($orderReference);
            $link->setQliroOrderId($orderId);
            $this->linkRepository->save($link);
        }

        return $link;
    }

    /**
     * Update qliro order with information in quote
     *
     * @param int|null $orderId
     * @param bool $force
     */
    public function update($orderId, $force = false)
    {
        $this->logManager->setMark('UPDATE ORDER');

        try {
            $link = $this->linkRepository->getByQliroOrderId($orderId);
            $this->logManager->setMerchantReference($link->getReference());

            $isQliroOrderStatusEmpty = empty($link->getQliroOrderStatus());
            $isQliroOrderStatusInProcess = $link->getQliroOrderStatus() == CheckoutStatusInterface::STATUS_IN_PROCESS;

            if ($isQliroOrderStatusEmpty || $isQliroOrderStatusInProcess) {
                $this->logManager->debug('update qliro order');     // @todo: remove
                $quoteId = $link->getQuoteId();

                try {
                    /** @var \Magento\Quote\Model\Quote $quote */
                    $quote = $this->quoteRepository->get($quoteId);

                    $hash = $this->generateUpdateHash($quote);

                    $this->logManager->debug(
                        sprintf(
                            'order hash is %s',
                            $link->getQuoteSnapshot() === $hash ? 'same' : 'different'
                        )
                    );     // @todo: remove

                    if ($force || $this->canUpdateOrder($hash, $link)) {
                        $request = $this->updateRequestBuilder->setQuote($quote)->create();
                        $this->merchantApi->updateOrder($orderId, $request);
                        $link->setQuoteSnapshot($hash);
                        $this->linkRepository->save($link);
                        $this->logManager->debug(sprintf('updated order %s', $orderId));     // @todo: remove
                    }
                } catch (\Exception $exception) {
                    if ($link && $link->getId()) {
                        $link->setIsActive(false);
                        $link->setMessage($exception->getMessage());
                        $this->linkRepository->save($link);
                    }
                    $this->logManager->critical(
                        $exception,
                        [
                            'extra' => [
                                'qliro_order_id' => $orderId,
                                'quote_id' => $quoteId,
                                'link_id' => $link->getId()
                            ],
                        ]
                    );
                }
            } else {
                $this->logManager->debug('Can\'t update QliroOne order');
            }
        } catch (\Exception $exception) {
            $this->logManager->critical(
                $exception,
                [
                    'extra' => [
                        'qliro_order_id' => $orderId,
                    ],
                ]
            );
        } finally {
            $this->logManager->setMark(null);
        }
    }

    /**
     * Check if QliroOne order can be updated
     *
     * @param string $hash
     * @param \Qliro\QliroOne\Api\Data\LinkInterface $link
     * @return bool
     */
    private function canUpdateOrder($hash, LinkInterface $link)
    {
        return empty($this->getQuote()->getShippingAddress()->getShippingMethod()) || $link->getQuoteSnapshot() !== $hash;
    }

    /**
     * Generate a hash for quote content comparison
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return string
     */
    private function generateUpdateHash($quote)
    {
        $request = $this->updateRequestBuilder->setQuote($quote)->create();
        $data = $this->containerMapper->toArray($request);
        unset($data['AvailableShippingMethods']);
        sort($data);

        try {
            $serializedData = $this->json->serialize($data);
        } catch (\InvalidArgumentException $exception) {
            $serializedData = null;
        }

        $hash = $serializedData ? md5($serializedData) : null;

        $this->logManager->debug(
            sprintf('generateUpdateHash: %s', $hash),
            ['extra' => var_export($data, true)]
        );     // @todo: remove

        return $hash;
    }

    /**
     * Generate a QliroOne unique order reference
     *
     * @return string
     */
    public function generateOrderReference()
    {
        $quote = $this->getQuote();
        $hash = $this->hashResolver->resolveHash($quote);
        $this->validateHash($hash);
        $hashLength = self::REFERENCE_MIN_LENGTH;

        do {
            $isUnique = false;
            $shortenedHash = substr($hash, 0, $hashLength);

            try {
                $this->linkRepository->getByReference($shortenedHash);

                if ((++$hashLength) > HashResolverInterface::HASH_MAX_LENGTH) {
                    $hash = $this->hashResolver->resolveHash($quote);
                    $this->validateHash($hash);
                    $hashLength = self::REFERENCE_MIN_LENGTH;
                }
            } catch (NoSuchEntityException $exception) {
                $isUnique = true;
            }
        } while (!$isUnique);

        return $shortenedHash;
    }

    /**
     * Update customer with data from QliroOne frontend callback
     *
     * @param array $customerData
     * @throws \Exception
     */
    public function updateCustomer($customerData)
    {
        /** @var \Qliro\QliroOne\Api\Data\QliroOrderCustomerInterface $qliroCustomer */
        $qliroCustomer = $this->containerMapper->fromArray($customerData, QliroOrderCustomerInterface::class);

        $this->customerConverter->convert($qliroCustomer, $this->getQuote());
        $this->recalculateAndSaveQuote();
    }

    /**
     * Update shipping price in quote
     * Return true in case shipping price was set, or false if the quote is virtual or update didn't happen
     *
     * @param float|null $price
     * @return bool
     * @throws \Exception
     */
    public function updateShippingPrice($price)
    {
        $quote = $this->getQuote();

        if ($price && !$quote->isVirtual()) {
            // @codingStandardsIgnoreStart
            // phpcs:disable
            $container = new DataObject(
                [
                    'shipping_price' => $price,
                    'can_save_quote' => false,
                ]
            );
            // @codingStandardsIgnoreEnd
            // phpcs:enable

            $this->eventManager->dispatch(
                'qliroone_shipping_price_update_before',
                [
                    'quote' => $quote,
                    'container' => $container,
                ]
            );
            $this->updateReceivedAmount($container);

            if ($container->getCanSaveQuote()) {
                $this->recalculateAndSaveQuote();

                return true;
            }
        }

        return false;
    }

    /**
     * If freight amount comes from Qliro, it's Unifaun and that amount has to be stored for Carrier to pick up
     *
     * @param $container
     */
    public function updateReceivedAmount($container)
    {
        try {
            $quote = $this->getQuote();
            if ($this->qliroConfig->isUnifaunEnabled($quote->getStoreId())) {
                $link = $this->linkRepository->getByQuoteId($quote->getId());
                if ($link->getUnifaunShippingAmount() != $container->getData('shipping_price')) {
                    $link->setUnifaunShippingAmount($container->getData('shipping_price'));
                    $this->linkRepository->save($link);
                    $container->setData('can_save_quote', true);
                }
            }
        } catch (\Exception $exception) {
        }
    }

    /**
     * Update selected shipping method in quote
     * Return true in case shipping method was set, or false if the quote is virtual or method was not changed
     *
     * @param float $fee
     * @return bool
     * @throws \Exception
     */
    public function updateFee($fee)
    {
        try {
            $this->fee->setQlirooneFeeInclTax($this->getQuote(), $fee);
            $this->recalculateAndSaveQuote();
        } catch (\Exception $exception) {
            $link = $this->getLinkFromQuote();
            $this->logManager->critical(
                $exception,
                [
                    'extra' => [
                        'qliro_order_id' => $link->getOrderId(),
                    ],
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * Validate hash against QliroOne order merchant reference requirements
     *
     * @param string $hash
     */
    private function validateHash($hash)
    {
        if (!preg_match(HashResolverInterface::VALIDATE_MERCHANT_REFERENCE, $hash)) {
            throw new \DomainException(sprintf('Merchant reference \'%s\' will not be accepted by Qliro', $hash));
        }
    }
}
