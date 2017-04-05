<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Model\Io;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as FileDriver;

/**
 * Class File
 * @method File setBaseDir(string $dir)
 * @method string getBaseDir()
 * @method int getReadLength()
 * @method int getWriteLength()
 * @package Unirgy\RapidFlow\Model\Io
 */
class File extends AbstractIo
{
    /**
     * @var DirectoryList
     */
    protected $_directoryList;

    protected $_openMode;
    protected $_filename;
    protected $_fp;
    /**
     * @var FileDriver
     */
    protected $_fileDriver;

    public function __construct(
        FileDriver $fileDriver,
        DirectoryList $directoryList,
        array $data = []
    )
    {
        $this->_fileDriver = $fileDriver;
        $this->_directoryList = $directoryList;

        parent::__construct($data);
    }

    /**
     * @param $filename
     * @param $mode
     * @return File|null
     * @throws \Exception
     */
    public function open($filename, $mode)
    {
        $filename = $this->getFilepath($filename);
        if ($this->_fp) {
            if ($this->_filename == $filename && $this->_openMode == $mode) {
                return null;
            } else {
                $this->close();
            }
        }

        $this->_fp = @fopen($filename, $mode);
        if ($this->_fp === false) {
            $e = error_get_last();
            throw new \Exception(__("Unable to open the file:\n %1\n with mode: '%2',\n error: (%3)", $filename, $mode, $e['message']));
        }

        $this->_openMode = $mode;
        $this->_filename = $filename;

        return $this;
    }

    public function isOpen()
    {
        return (bool)$this->_fp;
    }

    /**
     * Close file and reset file pointer
     */
    public function close()
    {
        if (!$this->_fp) {
            return;
        }
        @fclose($this->_fp);

        $this->_fp = null;
        $this->_filename = null;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        @fseek($this->_fp, $offset, $whence);
        return $this;
    }

    public function tell()
    {
        return ftell($this->_fp);
    }

    public function read()
    {
        $length = $this->getReadLength();
        if ($length) {
            $data = fread($this->_fp, $length);
        } else {
            $data = fread($this->_fp, 1024);
        }
        return $data;
    }

    public function write($data)
    {
        if ($this->getWriteLength()) {
            fwrite($this->_fp, $data, $this->getWriteLength());
        } else {
            fwrite($this->_fp, $data);
        }
        return $this;
    }

    /**
     * @param $filename
     * @return string
     */
    public function getFilepath($filename)
    {
        if (!$this->getBaseDir()) {
            $this->setBaseDir($this->_directoryList->getPath('var') . ('urapidflow'));
        }
        $dir = $this->getBaseDir();
        if ($dir) {
            if (!$this->_fileDriver->isExists($dir)) {
                $this->_fileDriver->createDirectory($dir, 0775);
            }
        }
        $filepath = rtrim($dir, '/') . '/' . ltrim($filename, '/');
        return $filepath;
    }

    public function reset()
    {
        $filename = $this->_filename;
        $openMode = $this->_openMode;
        $this->close();
        @unlink($filename);
        $this->open($filename, $openMode);
        return $this;
    }

    /**
     * Close file on object destruct
     */
    public function __destruct()
    {
        $this->close();
    }
}
