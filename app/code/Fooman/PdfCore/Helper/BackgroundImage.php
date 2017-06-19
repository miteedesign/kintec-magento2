<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PdfCore\Helper;

class BackgroundImage
{
    
    const XML_PATH_BACKGROUND_IMAGE = 'sales_pdf/all/page/allbgimage';

    
    /**
     * BackgroundImage Helper constructor.
     *
     * @param \Magento\Framework\Filesystem\Io\File $file,
     * @param \Magento\Framework\Filesystem         $filesystem
     */
    public function __construct(
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->file = $file;
        $this->filesystem = $filesystem;
    }

    /**
     * @param  \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @return string|bool
     */
    public function getBackgroundImageFilePath(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $storeId
    ) {
        $file = $scopeConfig->getValue(
            self::XML_PATH_BACKGROUND_IMAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $fullPath = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath('pdf_background/' . $file);
        if ($this->file->fileExists($fullPath)) {
            return $fullPath;
        }
        return false;
    }
}
