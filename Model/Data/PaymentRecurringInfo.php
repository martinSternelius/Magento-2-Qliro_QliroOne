<?php declare(strict_types=1);

namespace Qliro\QliroOne\Model\Data;

use Magento\Framework\DataObject;

/**
 * Recurring info Data Model. Stored in Quote and Order Payment Additional Info as JSON.
 *
 * @method void setEnabled()
 * @method bool getEnabled()
 * @method void setFrequencyOption(string $recurringFrequency)
 * @method string getFrequencyOption()
 * @method void setNextOrderDate(string $nextOrderDate)
 * @method string getNextOrderDate()
 * @method void setCanceledDate(string $canceledDate)
 * @method string|null getCanceledDate()
 * @method void setPaymentMethodName(string $name)
 * @method string getPaymentMethodName()
 * @method void setPaymentMethodSubType(string $subType)
 * @method string getPaymentMethodSubType()
 * @method void setPaymentMethodSelectedLetterInvoiceOption(bool $option)
 * @method bool getPaymentMethodSelectedLetterInvoiceOption()
 * @method void setPaymentMethodMerchantSavedCreditCardId()
 * @method string getPaymentMethodMerchantSavedCreditCardId()
 */
class PaymentRecurringInfo extends DataObject
{
}
