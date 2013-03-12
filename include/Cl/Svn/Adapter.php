<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Svn_Adapter
{
    private $_aConfig;

    public function __construct($aConfig)
    {
        $this->_aConfig = $aConfig;
    }

    public function setConfig($aConfig)
    {
        $this->_aConfig = $aConfig;
    }

    public function getSvnUrl()
    {
        $sSvnUrl = '';

        if ($this->_aConfig['svn.proto'] == 'http') {
            $sSvnUrl .= 'http://';
        } elseif ($this->_aConfig['svn.proto'] == 'svn') {
            $sSvnUrl .= 'svn://';
        } elseif ($this->_aConfig['svn.proto'] == 'svn+ssh') {
            $sSvnUrl .= 'svn+ssh://';
        } elseif ($this->_aConfig['svn.proto'] == 'file') {
            $sSvnUrl .= 'file://';
        } else {
            throw new Exception(
                'Unknown svn.proto'
                );
        }

        $sSvnUrl .= $this->_aConfig['svn.server'];
        $sSvnUrl .= '/' . $this->_aConfig['svn.repo'] . '/';

        return $sSvnUrl;
    }

    private function _execute($sCommand, $sArguments, $sOutputFile = false)
    {
        $sCommand = 'svn ' . $sCommand . ' --non-interactive ';

        if ($this->_aConfig['svn.auth'] == 'userpass') {
            $sCommand .= '--username ' . $this->_aConfig['svn.user'] . ' ';
            $sCommand .= '--password ' . $this->_aConfig['svn.pass'] . ' ';
        } elseif ($this->_aConfig['svn.auth'] == 'kerberos') {
            $sCommand .= '--username ' . $this->_aConfig['svn.user'] . ' ';
        } elseif ($this->_aConfig['svn.auth'] == 'none') {
            // Nothing
        } else {
            throw new Exception(
                'Unknown svn.auth'
                );
        }

        $sCommand .= $sArguments;

        echo "[#] " . $sCommand . "\n";

        ob_start();
        passthru($sCommand, $iReturn);

        $sOutput = ob_get_contents();

        ob_end_clean();

        if ($iReturn != 0) {
            throw new Exception(
                'svn errcode ' . $iReturn
                );
        }

        if ($sOutputFile) {
            file_put_contents(
                $sOutputFile,
                $sOutput
                );
        } else {
            return $sOutput;
        }

        return true;
    }

    public function diff($sOldRev, $sNewRev, $sPath = '', $bXml = false, $sOutputFile = false)
    {
        return self::_execute(
            'diff',
            ($bXml ? '--xml ' : '') . '-r ' . $sOldRev . ':' . $sNewRev . ' ' . $this->getSvnUrl() . $sPath,
            $sOutputFile
            );
    }

    public function llist($sPath = '', $bXml = false, $sOutputFile = false)
    {
        return self::_execute(
            'list',
            ($bXml ? '--xml ' : '') . $this->getSvnUrl() . $sPath,
            $sOutputFile
            );
    }

    public function log($sPath = '', $bVerbose = false, $bXml = false, $sOutputFile = false)
    {
        return self::_execute(
            'log',
            ($bVerbose ? '--verbose ' : '') . ($bXml ? '--xml ' : '') . $this->getSvnUrl() . $sPath,
            $sOutputFile
            );
    }

    public function logRevMerge($sOldRev, $sNewRev, $sPath = '', $bXml = false, $sOutputFile = false)
    {
        return self::_execute(
            'log',
            '-g ' . ($bXml ? '--xml ' : '') .  '-r ' . $sOldRev . ':' . $sNewRev . ' ' . $this->getSvnUrl() . $sPath,
            $sOutputFile
            );
    }
}
