<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;


use Unirgy\RapidFlow\Model\Profile;

class ExportCsv extends AbstractProfile
{
    public function execute()
    {
        $fileName = 'urapidflow_profiles.csv';
        /** @var \Unirgy\RapidFlow\Block\Adminhtml\Profile\Grid $grid */
        $grid = $this->_view->getLayout()->createBlock('Unirgy\RapidFlow\Block\Adminhtml\Profile\Grid');
        $content = $grid->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }
}
