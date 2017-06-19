<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PdfCore\Block\Pdf\Column;

use Magento\Framework\Module\Dir;

class Checkbox extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 8;
    const DEFAULT_TITLE = '';
    const COLUMN_TYPE = 'fooman_checkbox';

    protected $moduleReader;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        array $data = []
    ) {
        $this->moduleReader = $moduleReader;
        parent::__construct($context, $data);
    }


    public function getGetter()
    {
        return [$this, 'getCheckbox'];
    }

    public function getCheckbox()
    {
        //return '☐'; Not available in all fonts
        /*return '<img src="'.$this
                    ->getViewFileUrl('Fooman_PdfCore::images/tickbox-image.png').'"/>';*/
        return '<img src="'.$this->moduleReader->getModuleDir(Dir::MODULE_VIEW_DIR, 'Fooman_PdfCore').'/images/tickbox-image.png"/>';
    }
}
