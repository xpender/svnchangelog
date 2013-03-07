<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Config
{
    private $_aConfig;

    private static $_oInstance;

    public function __construct()
    {
        require PROJECT_ROOT . '/include/config.inc.php';

        $this->_aConfig = $aConfig;
    }

    public static function getInstance()
    {
        if (null === self::$_oInstance) {
            self::$_oInstance = new self();
        }

        return self::$_oInstance;
    }

    public function all()
    {
        return $this->_aConfig;
    }

    public function getDataPath()
    {
        return $this->_aConfig['data.path'];
    }

    public function getTmpPath()
    {
        return $this->_aConfig['tmp.path'];
    }

    public function hasProject($sProject)
    {
        if (isset($this->_aConfig['projects'][$sProject])) {
            return true;
        }

        return false;
    }

    public function getProject($sProject)
    {
        if (isset($this->_aConfig['projects'][$sProject])) {
            return $this->_aConfig['projects'][$sProject];
        }

        return false;
    }
}
