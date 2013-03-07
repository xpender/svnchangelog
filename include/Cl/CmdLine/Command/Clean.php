<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_CmdLine_Command_Clean extends Cl_CmdLine_CommandAbstract
{
    /**
     * project
     *
     * @var string
     */
    private $_sProject;

    /**
     * __construct
     *
     * @param string $sProject
     */
    public function __construct($sProject)
    {
        // is project valid?
        if (!Cl_Config::getInstance()->hasProject($sProject)) {
            echo '[!] unknown project given: ' . $sProject . "\n";

            exit;
        }

        // set project
        $this->_sProject = $sProject;

        // parent construct..
        parent::__construct();
    }

    /**
     *executes command
     */
    protected function _execute()
    {
        // clear tags data storage
        echo "[*] truncating tags data\n";

        $oDataTags = new Cl_Data_Tags(
            $this->_sProject
            );
        
        $oDataTags->truncate();

        // TODO: think about unlink'ing files..
    }
}
