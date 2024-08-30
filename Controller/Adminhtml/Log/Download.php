<?php declare(strict_types=1);

namespace Qliro\QliroOne\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Response\Http\FileFactory;

/**
 * Controller for dowloading Qliroone Log Files
 */
class Download extends Action
{
    const ADMIN_RESOURCE = 'Qliro_QliroOne::log_download';

    const DOWLOAD_FILE_NAME = 'qliroone_logs.zip';

    protected FileFactory $fileFactory;

    protected DirectoryList $directoryList;

    protected array $allowedLogFiles = [
        'qliroone.log',
        'qliroone_error.log'
    ];

    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        DirectoryList $directoryList
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
    }

    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        try {
            $zipFilePath = sprintf(
                '%s/%s/%s',
                $this->directoryList->getPath(DirectoryList::VAR_DIR),
                DirectoryList::LOG,
                self::DOWLOAD_FILE_NAME
            );

            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new LocalizedException(__('Unable to create zip archive.'));
            }

            $filesAdded = false;
            foreach ($this->allowedLogFiles as $logFile) {
                $logFilePath = sprintf(
                    '%s/%s/%s',
                    $this->directoryList->getPath(DirectoryList::VAR_DIR),
                    DirectoryList::LOG,
                    $logFile
                );

                if (file_exists($logFilePath)) {
                    $zip->addFile($logFilePath, $logFile);
                    $filesAdded = true;
                }
            }

            $zip->close();

            if (!$filesAdded) {
                throw new LocalizedException(__('No log files were found to download.'));
            }

            return $this->fileFactory->create(
                self::DOWLOAD_FILE_NAME,
                [
                    'type' => 'filename',
                    'value' => sprintf('%s/%s', DirectoryList::LOG, self::DOWLOAD_FILE_NAME),
                    'rm' => true
                ],
                DirectoryList::VAR_DIR
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while trying to download the logs. Please try again later.'));
        }

        return $this->_redirect('admin/system_config/edit/section/payment');
    }
}
