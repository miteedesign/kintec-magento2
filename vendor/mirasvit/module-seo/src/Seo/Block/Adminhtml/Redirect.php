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



namespace Mirasvit\Seo\Block\Adminhtml;

class Redirect extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_redirect';
        $this->_blockGroup = 'Mirasvit_Seo';
        $this->_headerText = __('Redirect Manager');
        $this->_addButtonLabel = __('Add New Redirect');
        parent::_construct();
    }

    /**
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/add');
    }

    /**
     * Add custom Add button.
     *
     * @return void
     */
    protected function _addNewButton()
    {
        $addButtonProps = [
            'id' => 'add_new_redirect',
            'label' => __('Add Redirect'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->getAddNewButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);
    }

    /**
     * Retrieve options for 'Add Redirect' split button
     *
     * @return array
     */
    protected function getAddNewButtonOptions()
    {
        $splitButtonOptions = [];
        $splitButtonOptions['add'] = [
            'label' => __('Add Redirect'),
            'onclick' => "setLocation('" . $this->getUrl('seo/redirect/add') . "')",
            'default' => true,
        ];
        $splitButtonOptions['import'] = [
            'label' => __('Import/Export Redirects'),
            'onclick' => "setLocation('" . $this->getUrl('seo/redirectImportExport/index') . "')",
        ];

        return $splitButtonOptions;
    }
}
