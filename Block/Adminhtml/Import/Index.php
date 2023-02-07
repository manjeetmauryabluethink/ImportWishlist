<?php
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Block\Adminhtml\Import;

use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

/**
 * Index block class
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class Index extends \Magento\Backend\Block\Template
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var CollectionFactory
     */
    private $websiteCollectionFactory;
    /**
     * @var File
     */
    protected $io;
    /**
     * @var Glob
     */
    private $glob;
    /**
     * Constructor
     * @param Context $context
     * @param StoreRepositoryInterface $storeRepository
     * @param CollectionFactory $websiteCollectionFactory
     * @param File $io
     * @param Glob $glob
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreRepositoryInterface $storeRepository,
        CollectionFactory $websiteCollectionFactory,
        File $io,
        Glob $glob,
        array $data = []
    ) {
        $this->storeRepository = $storeRepository;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->io = $io;
        $this->glob = $glob;
        parent::__construct($context, $data);
    }
    /**
     * Get Store List
     *
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    public function storelist()
    {
        return $this->storeRepository->getList();
    }

    /**
     * Get Website Lists
     *
     * @return CollectionFactory
     */
    public function getWebsiteLists()
    {
        return $this->websiteCollectionFactory->create();
    }

    /**
     * Get Possible Import Files
     *
     * @return array
     */
    public function getPossibleImportFiles(): array
    {
        return $this->glob->glob(
            $this->_filesystem
                ->getDirectoryRead(DirectoryList::VAR_DIR)
                ->getAbsolutePath() .
                "pricingimport" .
                DIRECTORY_SEPARATOR .
                "*.csv"
        );
    }

    /**
     * Returns basename of file path
     *
     * @param string $file
     * @return string
     */
    public function getBasename($file)
    {
        $fileInfo = $this->io->getPathInfo($file);
        return $fileInfo["basename"];
    }

    /**
     * Get Download SampleUrl
     *
     * @return mixed
     */
    public function getDownloadSampleUrl()
    {
        return $this->getUrl("csvdata/index/index/", [
            "download_sample" => "yes",
        ]);
    }
}
