<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Client_Command_ShowUsage
{
    public function __construct()
    {
        echo "# ChangeLog Generator\n";
        echo "Usage:\n";
        echo "    [project]\n";
        echo "    user [project] [username]\n";
    }
}
