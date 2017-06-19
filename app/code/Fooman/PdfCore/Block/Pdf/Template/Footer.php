<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PdfCore\Block\Pdf\Template;

class Footer extends \Fooman\PdfCore\Block\Pdf\Block
{
    protected $_template = 'Fooman_PdfCore::pdf/footer.phtml';
    protected $parameters = [];

    const MARGIN_IN_BETWEEN = 5; //in percent

    const XML_PATH_FOOTER = 'sales_pdf/all/allfooter';


    /**
     * do we have any footer content to output
     *
     * @return bool
     * @access public
     */
    public function hasFooter()
    {
        $footers = $this->getFooterBlocks();
        return (bool)$footers[0];
    }

    /**
     * return data for all blocks set for the footers
     * maximum 4
     *
     * @return array    array[0] contains how many blocks we need to set up
     * @access public
     */
    public function getFooterBlocks()
    {
        if (!isset($this->parameters[$this->getStoreId()]['footers'])) {
            $this->parameters[$this->getStoreId()]['footers'][0] = 0;
            for ($i = 1; $i < 5; $i++) {
                $this->parameters[$this->getStoreId()]['footers'][$i] = nl2br(
                    $this->_scopeConfig->getValue(
                        self::XML_PATH_FOOTER . $i,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $this->getStoreId()
                    )
                );
                if (!empty($this->parameters[$this->getStoreId()]['footers'][$i])) {
                    $this->parameters[$this->getStoreId()]['footers'][0] = $i;
                }
            }
        }
        return $this->parameters[$this->getStoreId()]['footers'];
    }

    /**
     * @return int
     */
    public function getMarginBetween()
    {
        return self::MARGIN_IN_BETWEEN;
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        if ($this->hasFooter()) {
            $marginBetween = $this->getMarginBetween();
            $footers = $this->getFooterBlocks();
            $num = $footers[0];
            return (100 - ($num - 1) * $marginBetween) / $num;
        } else {
            return 100;
        }
    }

}
