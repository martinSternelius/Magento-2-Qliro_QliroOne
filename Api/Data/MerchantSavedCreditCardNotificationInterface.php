<?php declare(strict_types=1);

namespace Qliro\QliroOne\Api\Data;

/**
 * Interface for Merchant Saved Credit Card Notification data model
 * @link https://developers.qliro.com/docs/qliro-one/get-started/notifications-checkout#merchant-saved-credit-card
 */
interface MerchantSavedCreditCardNotificationInterface extends ContainerInterface
{
    /**
     * Qliro Order ID
     *
     * @return string
     */
    public function getOrderId(): string;

    /**
     * Qliro Order ID
     *
     * @param string $qliroOrderId
     * @return self
     */
    public function setOrderId(string $qliroOrderId): self;

    /**
     * Merchant saved credit card ID
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Merchant saved credit card ID
     *
     * @param string $id
     * @return self
     */
    public function setId(string $id): self;

    /**
     * @param string $cardBrandName
     * @return self
     */
    public function setCardBrandName(string $cardBrandName): self;

    /**
     * @return string
     */
    public function getCardBrandName(): string;

    /**
     * @param string $cardBin
     * @return self
     */
    public function setCardBin(string $cardBin): self;

    /**
     * @return string
     */
    public function getCardBin(): string;

    /**
     * @param string $cardLast4Digits
     * @return self
     */
    public function setCardLast4Digits(string $cardLast4Digits): self;

    /**
     * @return string
     */
    public function getCardLast4Digits(): string;

    /**
     * @param string $expiryYear
     * @return self
     */
    public function setExpiryYear(string $expiryYear): self;

    /**
     * @return string
     */
    public function getExpiryYear(): string;

    /**
     * @param string $expiryMonth
     * @return self
     */
    public function setExpiryMonth(string $expiryMonth): self;

    /**
     * @return string
     */
    public function getExpiryMonth(): string;

    /**
     * @return string
     */
    public function getSavedCreditCardToken(): string;

    /**
     * @param string $savedCreditCardToken
     * @return self
     */
    public function setSavedCreditCardToken(string $savedCreditCardToken): self;

    /**
     * @param string $timeStamp
     * @return self
     */
    public function setTimeStamp(string $timeStamp): self;

    /**
     * @return string
     */
    public function getTimeStamp(): string;
}