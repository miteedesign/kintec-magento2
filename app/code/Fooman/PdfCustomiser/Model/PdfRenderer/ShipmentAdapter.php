<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_EmailAttachments
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fooman\PdfCustomiser\Model\PdfRenderer;

use \Fooman\EmailAttachments\Model\Api\PdfRendererInterface;

class ShipmentAdapter implements PdfRendererInterface
{
    private $pdfRendererFactory;

    private $pdfRenderer;

    public function __construct(
        \Fooman\PdfCore\Model\PdfRendererFactory $pdfRendererFactory,
        \Fooman\PdfCustomiser\Block\ShipmentFactory $shipmentDocumentFactory
    ) {
        $this->pdfRendererFactory = $pdfRendererFactory;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
    }

    public function getPdfAsString(array $salesObjects)
    {
        $this->pdfRenderer = $this->pdfRendererFactory->create();
        foreach ($salesObjects as $shipment) {
            $document = $this->shipmentDocumentFactory->create(
                ['data' => ['shipment' => $shipment]]
            );
            $this->pdfRenderer->addDocument($document);
        }

        return $this->pdfRenderer->getPdfAsString();
    }

    public function getFileName()
    {
        return $this->pdfRenderer->getFilename(true);
    }

    public function canRender()
    {
        return true;
    }
}
