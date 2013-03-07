<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
abstract class Cl_DataAbstract
{
    protected $_sDataFile;

    protected $_aData;

    public function __construct()
    {
        $this->_readDbFile();
    }

    protected function _readDbFile()
    {
        if (!file_exists($this->_sDataFile)) {
            $this->_aData = array();

            return;
        }

        $sContent = file_get_contents(
            $this->_sDataFile
            );

        $this->_aData = json_decode(
            $sContent,
            true
            );
    }

    protected function _writeDbFile()
    {
        file_put_contents(
            $this->_sDataFile,
            json_encode($this->_aData)
            );
    }

    public function all()
    {
        return $this->_aData;
    }
    
    public function exists($sKey)
    {
        return (isset($this->_aData[$sKey]));
    }

    public function get($sKey)
    {
        return $this->_aData[$sKey];
    }

    public function insert($sKey, $aData)
    {
        if ($this->exists($sKey)) {
            return false;
        }

        return $this->update($sKey, $aData);
    }

    public function update($sKey, $aData)
    {
        $this->_aData[$sKey] = $aData;

        return true;
    }

    public function remove($sKey)
    {
        unset($this->_aData[$sKey]);

        return true;
    }

    public function truncate()
    {
        $this->_aData = array();

        return true;
    }

    public function __destruct()
    {
        $this->_writeDbFile();
    }
}
