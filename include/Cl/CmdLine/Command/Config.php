<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_CmdLine_Command_Config extends Cl_CmdLine_CommandAbstract
{
    protected function _execute()
    {
        $aConfig = Cl_Config::getInstance()->all();

        // data path
        echo "data.path: " . $aConfig['data.path'] . "\n";
        
        // tmp path
        echo "tmp.path: " . $aConfig['tmp.path'] . "\n";

        // projects
        foreach ($aConfig['projects'] as $sProject => $aProject) {
            echo "\n";
            echo "project." . $sProject . "\n";
            echo "- svn.proto: " . $aProject['svn.proto'] . "\n";
            echo "- svn.server: " . $aProject['svn.server'] . "\n";
            echo "- svn.repo: " . $aProject['svn.repo'] . "\n";
            echo "- svn.auth: " . $aProject['svn.auth'] . "\n";
        }
    }
}
