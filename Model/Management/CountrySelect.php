<?php declare(strict_types=1);

namespace Qliro\QliroOne\Model\Management;

use Qliro\QliroOne\Model\Config;
use Magento\Checkout\Model\Session;

class CountrySelect
{
    private Config $config;

    private Session $checkoutSession;

    public function __construct(
        Config $config,
        Session $checkoutSession
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Country selector is enabled in config
     *
     * @return boolean
     */
    public function isEnabled(): bool
    {
        return $this->config->isUseCountrySelector();
    }

    /**
     * Set selected country code
     *
     * @param string $countryId
     */
    public function setSelectedCountry(string $countryId): void
    {
        $this->checkoutSession->setSelectedCountry($countryId);
    }

    /**
     * Get selected country code
     *
     * @return string
     */
    public function getSelectedCountry(): string
    {
        return $this->checkoutSession->getSelectedCountry() ?? '';
    }

    /**
     * If country has changed
     *
     * @return boolean
     */
    public function countryHasChanged(): bool
    {
        $quote = $this->checkoutSession->getQuote();
        $mainAddress = $quote->getShippingAddress();
        if ($quote->isVirtual()) {
            $mainAddress = $quote->getBillingAddress();
        }
        return $this->checkoutSession->getSelectedCountry() !== $mainAddress->getCountryId();
    }
}
