<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;



class ExportExcelReport extends AbstractProfile
{
    public function execute()
    {
        $profile = $this->_getProfile();

        $profile->exportExcelReport();

        $this->_pipeFile(
            $profile->getExcelReportBaseDir() . DIRECTORY_SEPARATOR . $profile->getExcelReportFilename(),
            $profile->getExcelReportFilename(),
            'application/vnd.ms-excel'
        );
    }
}
