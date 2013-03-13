<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
abstract class Cl_Frontend_ActionAbstract
{
    protected $_oTemplate;

    public function __construct()
    {
        // init template
        $this->_oTemplate = new Cl_Frontend_Template(
            PROJECT_ROOT . '/include/Cl/Frontend/Templates/'
            );

        // execute
        $this->_execute();
    }

    abstract protected function _execute();
}
