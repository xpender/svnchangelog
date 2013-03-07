<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_CmdLine_Command_Help extends Cl_CmdLine_CommandAbstract
{
    protected function _execute()
    {
        echo "svnchangelog generator\n";
        echo "\n";
        echo "Commands:\n";
        echo "    update [project] - Fetches latest informations from SCM\n";
        echo "    clean  [project] - Cleans local stored data for Project\n";
        echo "    config           - Displays config\n";
    }
}
