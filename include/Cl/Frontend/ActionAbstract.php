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

        // set some default stuff to template engine
        $aConfig = Cl_Config::getInstance()->all();

        $this->_oTemplate->assign(
            'aProjects',
            array_keys($aConfig['projects'])
            );

        // current project if set..
        $this->_oTemplate->assign(
            'sCurrentProject',
            Cl_Frontend_Session::getProject()
            );

        // current action
        $this->_oTemplate->assign(
            'sCurrentAction',
            $this->getName()
            );

        // execute
        $this->_execute();
    }

    abstract public function getName();

    abstract protected function _execute();
}
