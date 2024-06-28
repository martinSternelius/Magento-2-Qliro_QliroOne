<?php declare(strict_types=1);

namespace Qliro\QliroOne\Model\Notification;

use Qliro\QliroOne\Api\Data\MerchantNotificationResponseInterface;

/**
 * Data model for Merchant Notification Response
 */
class MerchantNotificationResponse implements MerchantNotificationResponseInterface
{
    private string $callbackResponse;

    private int $callbackResponseCode;

    /**
     * @inheritDoc
     */
    public function getCallbackResponse(): string
    {
        return $this->callbackResponse;
    }

    /**
     * @inheritDoc
     */
    public function getCallbackResponseCode(): int
    {
        return $this->callbackResponseCode;
    }

    /**
     * @inheritDoc
     */
    public function setCallbackResponse(string $value): self
    {
        $this->callbackResponse = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCallbackResponseCode(int $code): self
    {
        $this->callbackResponseCode = $code;
        return $this;
    }
}