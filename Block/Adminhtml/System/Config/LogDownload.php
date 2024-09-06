<?php
namespace Qliro\QliroOne\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Log Download field download
 */
class LogDownload extends Field
{
    protected $_template = 'Qliro_QliroOne::system/config/log_download.phtml';

    /**
     * Retrieve element HTML markup.
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Generate button HTML.
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        );
        
        /** @var \Magento\Backend\Block\Widget\Button $button */
        $button->setData(
            [
                'id' => 'download_logs_button',
                'label' => __('Download'),
                'onclick' => 'setLocation(\'' . $this->getLogDownloadUrl() . '\')',
            ]
        );
        return $button->toHtml();
    }

    /**
     * Get download URL.
     *
     * @return string
     */
    public function getLogDownloadUrl()
    {
        return $this->getUrl('qliroone/log/download');
    }
}
