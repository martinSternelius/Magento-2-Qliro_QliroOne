<?php declare(strict_types=1);

namespace Qliro\QliroOne\Model\QliroOrder\Admin;

use Qliro\QliroOne\Api\Data\AdminOrderItemActionInterface;
use Qliro\QliroOne\Model\QliroOrder\Item;

class OrderItemAction extends Item implements AdminOrderItemActionInterface
{
    private string $actionType = '';

    private ?int $paymentTransactionId = null;

    /**
     * @inheritDoc
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * @inheritDoc
     */
    public function setActionType($value)
    {
        $this->actionType = $value;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentTransactionId()
    {
        return $this->paymentTransactionId;
    }

    /**
     * @inheritDoc
     */
    public function setPaymentTransactionId($value)
    {
        $this->paymentTransactionId = $value;
    }
}
