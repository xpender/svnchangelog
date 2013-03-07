<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
abstract class Cl_CmdLine_CommandAbstract
{
    public function __construct()
    {
        $this->_execute();
    }

    abstract protected function _execute();
}
