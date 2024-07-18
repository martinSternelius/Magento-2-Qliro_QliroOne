<?php declare(strict_types=1);

namespace Qliro\QliroOne\Api\Data;

/**
 * Interface for Merchant Saved Credit Card Notification request data model
 */
interface UpdateOrderSavedCreditCardInterface
{
    /**
     * @return string
     */
    public function getQliroOrderId(): string;

    /**
     * @param string $qliroOrderId
     * @return void
     */
    public function setQliroOrderId(string $qliroOrderId): void;

    /**
     * @return string
     */
    public function getId(string $id): string;

    /**
     * @param string $id
     * @return void
     */
    public function setId(string $id): void;

    /**
     * @param string $cardBrandName
     * @return void
     */
    public function setCardBrandName(string $cardBrandName): void;

    /**
     * @return string
     */
    public function getCardBrandName(): string;

    /**
     * @param string $cardBin
     * @return void
     */
    public function setCardBin(string $cardBin): void;

    /**
     * @return string
     */
    public function getCardBin(): string;

    /**
     * @param string $cardLast4Digits
     * @return void
     */
    public function setCardLast4Digits(string $cardLast4Digits): void;

    /**
     * @return string
     */
    public function getCardLast4Digits(): string;

    /**
     * @param string $expiryYear
     * @return void
     */
    public function setExpiryYear(string $expiryYear): void;

    /**
     * @return string
     */
    public function getExpiryYear(): string;

    /**
     * @param string $expiryMonth
     * @return void
     */
    public function setExpiryMonth(string $expiryMonth): void;

    /**
     * @return string
     */
    public function getExpiryMonth(): string;

    /**
     * @return string
     */
    public function getSavedCreditCardToken(): string;

    /**
     * @param string $timeStamp
     * @return void
     */
    public function setTimeStamp(string $timeStamp): void;

    /**
     * @return string
     */
    public function getTimeStamp(): string;
}