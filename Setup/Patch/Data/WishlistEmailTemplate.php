<?php
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Setup\Patch\Data;

use Magento\Framework\Module\Dir;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Email\Model\TemplateFactory;
use Magento\Email\Model\ResourceModel\Template as TemplateResource;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir\Reader;

class WishlistEmailTemplate implements DataPatchInterface
{
    private const MODULE_DIR = "Bluethinkinc_ImportWishlist";
    private const TEMPLATE_SUBJECT = "Product Added to Your Wishlist";
    private const TEMPLATE_CODE = "product_added_to_your_wishlist";
    private const TEMPLATE_NAME = "email_template";

    /** @var TemplateFactory */
    private TemplateFactory $templateFactory;

    /** @var TemplateResource */
    private TemplateResource $templateResource;

    /** @var File */
    private File $filesystem;

    /** @var Reader */
    private Reader $moduleReader;

    /**
     * @param TemplateFactory  $templateFactory
     * @param TemplateResource $templateResource
     * @param File             $filesystem
     * @param Reader           $moduleReader
     */
    public function __construct(
        TemplateFactory $templateFactory,
        TemplateResource $templateResource,
        File $filesystem,
        Reader $moduleReader
    ) {
        $this->templateFactory = $templateFactory;
        $this->templateResource = $templateResource;
        $this->filesystem = $filesystem;
        $this->moduleReader = $moduleReader;
    }
    /**
     * @inheritDoc
     */
    public function apply()
    {
        $template = $this->templateFactory->create();
        $templateText = $this->filesystem->fileGetContents(
            $this->getDirectory(self::TEMPLATE_NAME . ".html")
        );

        $template
            ->setTemplateSubject(self::TEMPLATE_SUBJECT)
            ->setTemplateCode(self::TEMPLATE_CODE)
            ->setTemplateText($templateText)
            ->setTemplateType(2);
        $this->templateResource->save($template);
    }

    /**
     * Returns email directory
     *
     * @param string $templateName
     *
     * @return string
     */
    private function getDirectory(string $templateName): string
    {
        $viewDir = $this->moduleReader->getModuleDir(
            Dir::MODULE_VIEW_DIR,
            self::MODULE_DIR
        );
        return $viewDir . "/frontend/email/" . $templateName;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
