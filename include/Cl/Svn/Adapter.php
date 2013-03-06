<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Svn_Adapter
{
    private static $_svnUser = 'svn';
    private static $_svnPass = '***';

    private static function _execute($sCommand, $sOutputFile, $bReturn = false)
    {
        echo '- exec: ' . str_replace(self::$_svnPass, '***', $sCommand) . "\n";

        ob_start();

        passthru($sCommand);

        $sOutput = ob_get_contents();
        
        ob_end_clean();

        if ($bReturn) {
            return $sOutput;
        } else {
            file_put_contents($sOutputFile, $sOutput);

            unset($sOutput);
        }
    }

    public static function cmdDiff($sSvnUrl, $sOldRev, $sNewRev)
    {
        $sCommand = 'svn diff --non-interactive --username ' . self::$_svnUser . ' --password ' . self::$_svnPass . ' -r' . $sOldRev . ':' . $sNewRev . ' ' . $sSvnUrl;

        return self::_execute($sCommand, null, true);
    }

    public static function cmdList($sSvnUrl, $sOutputFile)
    {
        $sCommand = 'svn list --non-interactive --xml --username ' . self::$_svnUser . ' --password ' . self::$_svnPass . ' ' . $sSvnUrl;

        return self::_execute($sCommand, $sOutputFile);
    }

    public static function cmdLog($sSvnUrl, $sOutputFile)
    {
        $sCommand = 'svn log --non-interactive --username ' . self::$_svnUser . ' --password ' . self::$_svnPass . ' ' . $sSvnUrl;

        return self::_execute($sCommand, $sOutputFile);
    }

    public static function cmdLogRevMerge($sSvnUrl, $iRevStart, $iRevEnd, $sOutputFile)
    {
        $sCommand = 'svn log --non-interactive --incremental --username ' . self::$_svnUser . ' --password ' . self::$_svnPass . ' -g -r' . $iRevStart . ':' . $iRevEnd .  ' ' . $sSvnUrl;

        return self::_execute($sCommand, $sOutputFile);
    }
}
