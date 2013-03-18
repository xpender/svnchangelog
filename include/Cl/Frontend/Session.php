<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Frontend_Session
{
    private static $_aSession = false;

    public static function init(&$aSession)
    {
        self::$_aSession =& $aSession;
    }

    public static function setProject($sProject)
    {
        if (!is_array(self::$_aSession)) {
            throw new Exception(
                __CLASS__ . ' not initialized'
                );
        }
        
        if (!Cl_Config::getInstance()->hasProject($sProject)) {
            return false;
        }

        self::$_aSession['cl_project'] = $sProject;
    }

    public static function getProject()
    {
        if (!is_array(self::$_aSession)) {
            throw new Exception(
                __CLASS__ . ' not initialized'
                );
        }

        if (isset(self::$_aSession['cl_project'])) {
            return self::$_aSession['cl_project'];
        }

        return false;
    }
}
