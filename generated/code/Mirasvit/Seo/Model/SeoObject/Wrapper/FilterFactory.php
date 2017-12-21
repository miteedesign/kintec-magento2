<?php
namespace Mirasvit\Seo\Model\SeoObject\Wrapper;

/**
 * Factory class for @see \Mirasvit\Seo\Model\SeoObject\Wrapper\Filter
 */
class FilterFactory
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
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Mirasvit\\Seo\\Model\\SeoObject\\Wrapper\\Filter')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Mirasvit\Seo\Model\SeoObject\Wrapper\Filter
     */
    public function create(array $data = array())
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
