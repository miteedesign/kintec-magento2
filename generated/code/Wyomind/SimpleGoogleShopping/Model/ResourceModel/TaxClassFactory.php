<?php
namespace Wyomind\SimpleGoogleShopping\Model\ResourceModel;

/**
 * Factory class for @see \Wyomind\SimpleGoogleShopping\Model\ResourceModel\TaxClass
 */
class TaxClassFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Wyomind\\SimpleGoogleShopping\\Model\\ResourceModel\\TaxClass')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Wyomind\SimpleGoogleShopping\Model\ResourceModel\TaxClass
     */
    public function create(array $data = array())
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}