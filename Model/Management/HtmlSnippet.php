<?php
/**
 * Copyright © Qliro AB. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Qliro\QliroOne\Model\Management;

/**
 * QliroOne management class
 */
class HtmlSnippet extends AbstractManagement
{
    /**
     * @var QliroOrder
     */
    private $qliroOrder;

    /**
     * Inject dependencies
     *
     * @param QliroOrder $qliroOrder
     */
    public function __construct(
        QliroOrder $qliroOrder
    ) {
        $this->qliroOrder = $qliroOrder;
    }

    /**
     * Fetch an HTML snippet from QliroOne order
     *
     * @return string
     */
    public function get()
    {
        try {
            return $this->qliroOrder->setQuote($this->getQuote())->get()->getOrderHtmlSnippet();
        } catch (\Exception $exception) {
            $openTag = '<a href="javascript:;" onclick="location.reload(true)">';
            $closeTag = '</a>';

            return __('QliroOne Checkout has failed to load. Please try to %1reload page%2.', $openTag, $closeTag);
        }
    }
}
