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
        $this->_sProject = $sProject;

        $this->_sUsername = $sUsername;

        $this->_aConfig = Cl_Config::getInstance()->getProject($sProject);

        $this->_exec();
    }

    private function _exec()
    {
        // init 
        $oSvnAdapter = new Cl_Svn_Adapter(
            $this->_aConfig
            );

        // tmp file base
        $sTmpFileBase = Cl_Config::getInstance()->getTmpPath() . md5($this->_sProject/* . microtime(true)*/);

        // get log
        $sLog = $sTmpFileBase . '.history.log';

        $oSvnAdapter->log(
            '',
            false,
            $sLog
            );

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

            $sDiff = $oSvnAdapter->diff(
                $iRev - 1,
                $iRev,
                '',
                false,
                false
                );

            $sOutput .= $sDiff . "\n\n";

            $sOutput .= str_repeat('-', 30) . "\n\n";
        }

        file_put_contents(PROJECT_ROOT . '/data/log-by-user-' . $this->_sUsername . '.txt', $sOutput);
    }
}
