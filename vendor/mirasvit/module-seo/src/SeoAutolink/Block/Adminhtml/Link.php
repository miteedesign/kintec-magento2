<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-seo
 * @version   1.0.51
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SeoAutolink\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container as GridContainer;

class Link extends GridContainer
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_link';
        $this->_blockGroup = 'Mirasvit_SeoAutolink';

        parent::_construct();
    }

    /**
     * Add custom Add button.
     * @return void
     */
    protected function _addNewButton()
    {
        $addButtonProps = [
            'id'           => 'add_new_link',
            'label'        => __('Add Link'),
            'class'        => 'add',
            'button_class' => '',
            'class_name'   => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options'      => [
                'add'    => [
                    'label'   => __('Add Link'),
                    'onclick' => "setLocation('" . $this->getUrl('seoautolink/link/add') . "')",
                    'default' => true,
                ],
                'import' => [
                    'label'   => __('Import Links'),
                    'onclick' => "setLocation('" . $this->getUrl('seoautolink/import/index') . "')",
                ]
            ]
        ];

        $this->buttonList->add('add_new', $addButtonProps);
    }
}
