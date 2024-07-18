<?php declare(strict_types=1);

namespace Qliro\QliroOne\Model\Notification;

use Qliro\QliroOne\Api\Data\MerchantSavedCreditCardNotificationInterface;

class MerchantSavedCreditCard implements MerchantSavedCreditCardNotificationInterface
{
    private string $orderId = '';

    private string $id = '';

    private string $cardBrandName = '';

    private string $cardBin = '';

    private string $cardLast4Digits = '';

    private string $expiryYear = '';

    private string $expiryMonth = '';

    private string $savedCreditCardToken = '';

    private string $timeStamp = '';

    /**
     * @inheritDoc
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @inheritDoc
     */
    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCardBin(): string
    {
        return $this->cardBin;
    }

    /**
     * @inheritDoc
     */
    public function setCardBin(string $cardBin): self
    {
        $this->cardBin = $cardBin;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCardBrandName(): string
    {
        return $this->cardBrandName;
    }

    /**
     * @inheritDoc
     */
    public function setCardBrandName(string $cardBrandName): self
    {
        $this->cardBrandName = $cardBrandName;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCardLast4Digits(string $cardLast4Digits): self
    {
        $this->cardLast4Digits = $cardLast4Digits;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCardLast4Digits(): string
    {
        return $this->cardLast4Digits;
    }

    /**
     * @inheritDoc
     */
    public function setExpiryMonth(string $expiryMonth): self
    {
        $this->expiryMonth = $expiryMonth;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExpiryMonth(): string
    {
        return $this->expiryMonth;
    }

    /**
     * @inheritDoc
     */
    public function getExpiryYear(): string
    {
        return $this->expiryYear;
    }

    /**
     * @inheritDoc
     */
    public function setExpiryYear(string $expiryYear): self
    {
        $this->expiryYear = $expiryYear;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setTimeStamp(string $timeStamp): self
    {
        $this->timeStamp = $timeStamp;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTimeStamp(): string
    {
        return $this->timeStamp;
    }

    /**
     * @inheritDoc
     */
    public function getSavedCreditCardToken(): string
    {
        return $this->savedCreditCardToken;
    }

    /**
     * @inheritDoc
     */
    public function setSavedCreditCardToken(string $savedCreditCardToken): self
    {
        $this->savedCreditCardToken = $savedCreditCardToken;
        return $this;
    }
}
