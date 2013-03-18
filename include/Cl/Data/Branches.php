<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Data_Branches extends Cl_DataAbstract
{
    private $_sProject;

    public function __construct($sProject)
    {
        $this->_sProject = $sProject;

        $this->_sDataFile = Cl_Config::getInstance()->getDataPath() . '/' . $sProject . '.branches.db.txt';

        parent::__construct();
    }
}
