<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Data_TagCommits extends Cl_DataAbstract
{
    private $_sProject;

    private $_sTag;

    public function __construct($sProject, $sTag)
    {
        $this->_sProject = $sProject;

        $this->_sTag = $sTag;

        $this->_sDataFile = Cl_Config::getInstance()->getDataPath() . '/' . $sProject . '.tag.' . $sTag . '.db.txt';

        parent::__construct();
    }
}
