<?php
/**
 * Copyright © Qliro AB. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Qliro\QliroOne\Api\Data;

/**
 * QliroOne Order Shipping Config interface
 *
 * @api
 */
interface QliroOrderShippingConfigInterface extends ContainerInterface
{
    /**
     * @return \Qliro\QliroOne\Api\Data\QliroOrderShippingConfigUnifaunInterface
     */
    public function getUnifaun();

    /**
     * @param \Qliro\QliroOne\Api\Data\QliroOrderShippingConfigUnifaunInterface $value
     * @return $this
     */
    public function setUnifaun($value);
}
