<?php
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Driver\File;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var FileFactory
     */
    protected $downloader;

    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var DriverInterface
     */
    protected $driverInterface;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param FileFactory $fileFactory
     * @param DirectoryList $directory
     * @param DriverInterface $driverInterface
     * @param File $fileDriver
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        FileFactory $fileFactory,
        DirectoryList $directory,
        DriverInterface $driverInterface,
        File $fileDriver
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->downloader = $fileFactory;
        $this->directory = $directory;
        $this->driverInterface = $driverInterface;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Execute Action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (isset($this->getRequest()->getParams()["download_sample"])) {
            $heading = ["email", "sku"];

            $filename = "wishlist_importer_sample.csv";
            $handle = $this->driverInterface->fileOpen($filename, "w");
            $this->driverInterface->filePutCsv($handle, $heading);

            $data = $this->getSampleData();
            foreach ($data as $d) {
                $this->driverInterface->filePutCsv($handle, $d);
            }

            $this->downloadCsv($filename);
        }

        return $resultPage = $this->resultPageFactory->create();
    }

    /**
     * Download Csv file
     *
     * @param array $filename
     * @return \Magento\Framework\App\ResponseInterface|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function downloadCsv($filename)
    {
        if ($this->fileDriver->isExists($filename)) {
            $filePath =
                $this->directory->getPath("pub") .
                DIRECTORY_SEPARATOR .
                $filename;

            return $this->downloader->create(
                $filename,
                $this->driverInterface->fileGetContents($filePath)
            );
        }
    }

    /**
     * Get sample data
     *
     * @return string[][]
     */
    public function getSampleData()
    {
        $data = [
            [
                'roni_cost@example.com',
                '24-WB02,24-WB03,24-WB07,24-WB04,24-UG04'
            ],
        ];
        return $data;
    }
}
