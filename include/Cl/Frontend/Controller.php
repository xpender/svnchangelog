<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Frontend_Controller
{
    public function dispatch($aRequest)
    {
        if (isset($aRequest['action'])) {
            $sAction = $aRequest['action'];
        } else {
            $sAction = 'index';
        }

        $sClassName = 'Cl_Frontend_Action_' . ucfirst($sAction);
        new $sClassName();
    }
}
