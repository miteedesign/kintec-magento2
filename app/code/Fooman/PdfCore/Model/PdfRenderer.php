<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PdfCore\Model;

use \Fooman\PdfCore\Block\Pdf\DocumentRendererInterface;

class PdfRenderer
{
    const XML_PATH_SIDE_MARGINS = 'sales_pdf/all/allmarginsides';
    const XML_PATH_TOP_MARGIN = 'sales_pdf/all/allmargintop';
    const XML_PATH_BOTTOM_MARGIN = 'sales_pdf/all/allmarginbottom';

    protected $pdf;
    protected $hasPrintContent = false;

    /**
     * @var \Fooman\PdfCore\Helper\ParamKey
     */
    protected $paramKeyHelper;

    /**
     * @var \Fooman\PdfCore\Helper\Filename
     */
    protected $filenameHelper;

    /**
     * @var \Fooman\PdfCore\Helper\BackgroundImage
     */
    protected $backgroundImageHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * PdfRenderer constructor.
     *
     * @param Tcpdf\Defaults                  $defaults
     * @param Tcpdf\Pdf                       $pdf
     * @param \Fooman\PdfCore\Helper\ParamKey $paramKeyHelper
     * @param \Fooman\PdfCore\Helper\Filename $filenameHelper
     */
    public function __construct(
        \Fooman\PdfCore\Model\Tcpdf\Defaults $defaults,
        \Fooman\PdfCore\Model\Tcpdf\Pdf $pdf,
        \Fooman\PdfCore\Helper\ParamKey $paramKeyHelper,
        \Fooman\PdfCore\Helper\Filename $filenameHelper,
        \Fooman\PdfCore\Model\IntegratedLabels\ProcessorFactory $integratedLabelsProcessorFactory,
        \Fooman\PdfCore\Helper\BackgroundImage $backgroundImageHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->pdf = $pdf;
        $this->paramKeyHelper = $paramKeyHelper;
        $this->filenameHelper = $filenameHelper;
        $this->integratedLabelsProcessorFactory = $integratedLabelsProcessorFactory;
        $this->backgroundImageHelper = $backgroundImageHelper;
        $this->eventManager = $eventManager;
    }

    public function hasPrintContent()
    {
        return $this->hasPrintContent;
    }

    public function addDocument(DocumentRendererInterface $document)
    {
        $this->applyStoreConfig($document);
        $this->applyBackgroundImage($document->getScopeConfig(), $document->getStoreId());
        $this->pdf->startPageGroup();
        $this->filenameHelper->addDocument($document->getTitle(), $document->getIncrement());
        $this->applyFooterContent($document);
        if ($document->getForcedPageOrientation()) {
            $this->pdf->setPageOrientation($document->getForcedPageOrientation());
        }
        $this->pdf->startPage();
        $html = $document->renderHtmlTemplate();
        $transport = new \Magento\Framework\DataObject(
            ['html' => $html]
        );
        $this->eventManager->dispatch(
            'fooman_pdfcore_before_write_html',
            [

                'pdf' => $this->pdf,
                'transport' => $transport,
                'document' => $document
            ]
        );

        $this->pdf->writeHTML($this->preProcessHtmlForPdf($transport->getHtml()), false);
        $this->applyIntegratedLabelsContent($document);
        $this->pdf->endPage();
        $this->hasPrintContent = true;
    }

    protected function applyBackgroundImage(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $storeId
    ) {
        $image = $this->backgroundImageHelper->getBackgroundImageFilePath($scopeConfig, $storeId);
        if ($image) {
            $this->pdf->setBackgroundImage($image);
        }
    }

    protected function applyIntegratedLabelsContent(DocumentRendererInterface $document)
    {
        if ($document->canApplyIntegratedLabelsContent()) {
            $integratedLabelsProcessor = $this->integratedLabelsProcessorFactory
                ->create(['document' => $document, 'pdf' => $this->pdf]);
            $integratedLabelsProcessor->process();
        }
    }

    protected function applyFooterContent(DocumentRendererInterface $document)
    {
        $this->pdf->setPrintFooter(true);
        $this->pdf->setFooterContent(
            $document->getFooterContent()
        );
    }

    protected function getBottomPageBreak(DocumentRendererInterface $document)
    {
        if ($document->canApplyIntegratedLabelsContent()) {
            return 75;
        }
        return $this->pdf->getFooterMargin() + 10;
    }

    protected function applyStoreConfig(
        $document
    ) {
        $scopeConfig = $document->getScopeConfig();
        $storeId = $document->getStoreId();
        $this->pdf->SetMargins(
            $scopeConfig->getValue(
                self::XML_PATH_SIDE_MARGINS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            $scopeConfig->getValue(
                self::XML_PATH_TOP_MARGIN,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            -1,
            true
        );
        $this->pdf->setHeaderMargin(0);

        $this->pdf->setFooterMargin(
            $scopeConfig->getValue(
                self::XML_PATH_BOTTOM_MARGIN,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );

        $this->pdf->SetAutoPageBreak(true, $this->getBottomPageBreak($document));

        //set image scale factor 3 pixels = 1mm
        $this->pdf->setImageScale(Tcpdf\Defaults::FACTOR_PIXEL_PER_MM);

        $this->pdf->setJPEGQuality(95);

        $this->pdf->setFontSubsetting(true);

        $this->pdf->SetFont(
            $scopeConfig->getValue(
                \Fooman\PdfCore\Block\Pdf\PdfAbstract::XML_PATH_FONT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            '',
            $scopeConfig->getValue(
                \Fooman\PdfCore\Block\Pdf\PdfAbstract::XML_PATH_FONT_SIZE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
        $this->pdf->SetFillColor(0); //black
    }

    public function preProcessHtmlForPdf($input)
    {
        return $this->convertTcpdfParams($input);
    }

    /**
     * Take the json_encoded params and make them applicable to this pdf
     * by calling serializeTCPDFtagParameters on them
     *
     * @param $input
     *
     * @return mixed
     */
    protected function convertTcpdfParams($input)
    {
        $matches = [];
        $regex = $this->paramKeyHelper->getDecodeRegex();

        if (preg_match_all($regex, $input, $matches)) {
            $i = 0;
            foreach ($matches[0] as $match) {
                $fullyEncodedParam = $this->pdf->serializeTCPDFtagParameters(
                    json_decode(urldecode($matches[1][$i]), true)
                );
                $replaced = str_replace($matches[1][$i], $fullyEncodedParam, $match);
                $input = str_replace($match, $replaced, $input);
                $i++;
            }
        }
        return $input;
    }

    /**
     * Get final pdf as string
     *
     * @return string
     */
    public function getPdfAsString()
    {
        return $this->pdf->Output(__DIR__ . 'doc.pdf', 'S');
    }

    /**
     * Get filename from helper
     * @param bool $reset
     *
     * @return mixed
     */
    public function getFileName($reset = false)
    {
        return $this->filenameHelper->getFilename($reset);
    }
}
