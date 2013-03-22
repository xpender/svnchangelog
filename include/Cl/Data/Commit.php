<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Data_Commit extends Cl_DataAbstract
{
    private $_sProject;

    private $_iRevision;

    public function __construct($sProject, $iRevision)
    {
        $this->_sProject = $sProject;

        $this->_iRevision = $iRevision;

        $this->_sDataFile = Cl_Config::getInstance()->getDataPath() . '/' . $sProject . '.commit.' . $iRevision . '.db.txt';

        parent::__construct();
    }
}
