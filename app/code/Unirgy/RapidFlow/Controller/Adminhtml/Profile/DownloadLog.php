<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;



class DownloadLog extends AbstractProfile
{
    public function execute()
    {
        $profile = $this->_getProfile();

        $this->_pipeFile(
            $profile->getLogBaseDir() . DIRECTORY_SEPARATOR . $profile->getLogFilename(),
            $profile->getLogFilename(),
            'application/vnd.ms-excel'
        );
    }
}
