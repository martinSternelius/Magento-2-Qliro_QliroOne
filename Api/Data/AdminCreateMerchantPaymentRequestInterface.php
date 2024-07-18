<?php declare(strict_types=1);

namespace Qliro\QliroOne\Api\Data;

use Qliro\QliroOne\Api\Data\ContainerInterface;

/**
 * Interface for Create Merchant Payment request data model
 */
interface AdminCreateMerchantPaymentRequestInterface extends ContainerInterface
{
    /**
     * @return string
     */
    public function getRequestId(): string;

    /**
     * @return string
     */
    public function getMerchantApiKey(): string;

    /**
     * @return string
     */
    public function getMerchantReference(): string;

    /**
     * @return string
     */
    public function getCurrency(): string;

    /**
     * @return string
     */
    public function getCountry(): string;

    /**
     * @return string
     */
    public function getLanguage(): string;

    /**
     * @return string
     */
    public function getMerchantOrderManagementStatusPushUrl(): string;

    /**
     * @return \Qliro\QliroOne\Api\Data\QliroOrderItemInterface[]
     */
    public function getOrderItems();

    /**
     * @return \Qliro\QliroOne\Api\Data\MerchantPaymentCreateRequestInterface|null
     */
    public function getCustomer(): ?MerchantPaymentCustomerInterface;

    /**
     * @return \Qliro\QliroOne\Api\Data\QliroOrderCustomerAddressInterface|null
     */
    public function getBillingAddress(): ?QliroOrderCustomerAddressInterface;

    /**
     * @return \Qliro\QliroOne\Api\Data\QliroOrderCustomerAddressInterface|null
     */
    public function getShippingAddress(): ?QliroOrderCustomerAddressInterface;

    /**
     * @return \Qliro\QliroOne\Api\Data\MerchantPaymentPaymentMethodInterface|null
     */
    public function getPaymentMethod(): ?MerchantPaymentPaymentMethodInterface;

    /**
     * @param string $value
     * @return self
     */
    public function setRequestId(string $value): self;

    /**
     * @param string $value
     * @return $this
     */
    public function setMerchantApiKey(string $value): self;

    /**
     * @param string $value
     * @return $this
     */
    public function setMerchantReference(string $value): self;

    /**
     * @param string $value
     * @return $this
     */
    public function setCurrency(string $value): self;

    /**
     * @param string $value
     * @return $this
     */
    public function setCountry(string $value): self;

    /**
     * @param string $value
     * @return $this
     */
    public function setLanguage(string $value): self;

    /**
     * @param string $value
     * @return $this
     */
    public function setMerchantOrderManagementStatusPushUrl(string $value): self;

    /**
     * @param \Qliro\QliroOne\Api\Data\QliroOrderItemInterface[] $value
     * @return $this
     */
    public function setOrderItems(array $value): self;

    /**
     * @param \Qliro\QliroOne\Api\Data\MerchantPaymentCustomerInterface $value
     * @return $this
     */
    public function setCustomer(MerchantPaymentCustomerInterface $value): self;

    /**
     * @param \Qliro\QliroOne\Api\Data\QliroOrderCustomerAddressInterface $value
     * @return self
     */
    public function setBillingAddress(QliroOrderCustomerAddressInterface $value): self;

    /**
     * @param \Qliro\QliroOne\Api\Data\QliroOrderCustomerAddressInterface $value
     * @return self
     */
    public function setShippingAddress(QliroOrderCustomerAddressInterface $value): self;

    /**
     * @param \Qliro\QliroOne\Api\Data\MerchantPaymentPaymentMethodInterface $value
     * @return self
     */
    public function setPaymentMethod(MerchantPaymentPaymentMethodInterface $value): self;
}
