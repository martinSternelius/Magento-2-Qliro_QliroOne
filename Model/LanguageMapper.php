<?php
/**
 * Copyright © Qliro AB. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Qliro\QliroOne\Model;

use Magento\Framework\Locale\Resolver;
use Qliro\QliroOne\Api\LanguageMapperInterface;

/**
 * QliroOne order language mapper class
 */
class LanguageMapper implements LanguageMapperInterface
{
    private $languageMap = [
        'sv_SE' => 'sv-se',
        'en_US' => 'en-us',
        'fi_FI' => 'fi-fi',
        'da_DK' => 'da-dk',
        'de_DE' => 'de-de',
        'nb_NO' => 'nb-no',
        'nn_NO' => 'nb-no',
    ];

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $localeResolver;

    /**
     * Inject dependencies
     *
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     */
    public function __construct(
        Resolver $localeResolver
    ) {
        $this->localeResolver = $localeResolver;
    }

    /**
     * Get a prepared string that contains a QliroOne compatible language
     *
     * @return string
     */
    public function getLanguage()
    {
        $locale = $this->localeResolver->getLocale();

        return $this->languageMap[$locale] ?? 'en-us';
    }
}
