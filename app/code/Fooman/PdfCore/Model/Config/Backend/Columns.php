<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PdfCore\Model\Config\Backend;

class Columns extends \Magento\Framework\App\Config\Value
{
    public function beforeSave()
    {
        $values = $this->getValue();
        if ($values) {
            $check = [];
            foreach ($values as $value) {
                if (isset($value['columntype'])) {
                    if (isset($check[$value['columntype']])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Each column type can only appear once.')
                        );
                    } else {
                        $check[$value['columntype']] = true;
                    }
                }
            }
        }
        if (is_array($values)) {
            unset($values['__empty']);
            $this->setValue(json_encode($values));
        }

        parent::beforeSave();
    }

    protected function _afterLoad()
    {
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = json_decode($values, true);
            foreach ($values as $key => $value) {
                if (!isset($values[$key]['title'])) {
                    $values[$key]['title'] = null;
                }
            }
            $this->setValue(empty($values) ? false : $values);
        }
    }
}
