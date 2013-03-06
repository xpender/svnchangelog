<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_CmdLine_Command_ByUser
{
    private $_sProject;

    private $_sUsername;

    private $_aConfig;

    public function __construct($sProject, $sUsername)
    {
        global $_PROJECTS;

        $this->_sProject = $sProject;

        $this->_sUsername = $sUsername;

        $this->_aConfig = $_PROJECTS[$sProject];

        $this->_build();
    }

    private function _getSvnBaseUrl()
    {
        return 'svn://' . $this->_aConfig['svn.server'] . '/' . $this->_aConfig['svn.project'];
    }

    private function _build()
    {
        // svn base url
        $sSvnBaseUrl = $this->_getSvnBaseUrl() . '/trunk';

        // tmp file base
        $sTmpFileBase = PROJECT_ROOT . '/tmp/' . md5($sSvnBaseUrl/* . microtime(true)*/);

        // get log
        $sLog = $sTmpFileBase . '.history.log';

        Cl_Svn_Adapter::cmdLog($sSvnBaseUrl, $sLog);

        // get parser for log
        $oParserLog = new Cl_Parser_Log();

        // parse
        $aAllCommits = $oParserLog->parseWithUsername($sLog, $this->_sUsername);

        // output
        $sOutput = '';

        foreach ($aAllCommits as $iRev => $aCommit) {
            $i++;

            $sOutput .= 'R' . $iRev . " - " . $aCommit['date'] . "\n";
            $sOutput .= $aCommit['message'] . "\n\n";

            $sDiff = Cl_Svn_Adapter::cmdDiff($sSvnBaseUrl, $iRev - 1, $iRev);

            $sOutput .= $sDiff . "\n\n";

            $sOutput .= str_repeat('-', 30) . "\n\n";
        }

        file_put_contents(PROJECT_ROOT . '/data/log-by-user-' . $this->_sUsername . '.txt', $sOutput);
    }
}
