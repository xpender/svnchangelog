<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_CmdLine_Command_Build
{
    private $_sProject;

    private $_aConfig;

    public function __construct($sProject)
    {
        $this->_sProject = $sProject;

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

        // fetch list of tags
        $sTagsList = $sTmpFileBase . '.tags.list.xml';

        $oSvnAdapter->llist(
            'tags/',
            true,
            $sTagsList
            );

        // parser all tags
        $oParserTags = new Cl_Parser_Tags();

        $aAllTags = $oParserTags->parseXml($sTagsList);

        $aReleaseTags = array();
        $aReleaseByRevs = array();

        foreach ($aAllTags as $sTag => $aInfo) {
            if (strpos($sTag, 'RELEASE_') === 0) {
                $aReleaseTags[$sTag] = $aInfo;

                $aReleaseByRevs[$aInfo['revision']] = $sTag;
            }
        }

        krsort($aReleaseByRevs);

        // get parser for log
        $oParserLog = new Cl_Parser_Log();

        // go through revision..
        $aRevs = array_keys($aReleaseByRevs);

        $iCurrentRev = array_shift($aRevs);

        // html head
        $sHtml  = '';
        $sHtml .= '<!doctype html>' . "\n";
        $sHtml .= '<html>' . "\n";
        $sHtml .= '<head>' . "\n";
        $sHtml .= '<title>ChangeLog for ' . $this->_sProject . '</title>' . "\n";
        $sHtml .= '</head>' . "\n";
        $sHtml .= '<body>' . "\n";
        $sHtml .= 'Generated at: ' . date('Y-m-d H:i:s') . "<br />\n";

        foreach ($aReleaseByRevs as $sTagName) {
            $iParentRev = array_shift($aRevs);

            if ($iParentRev == null) {
                break;
            }

            // echo
            echo '- ' . $sTagName . ' - comparing: ' . $iParentRev . '->' . $iCurrentRev . "\n";

            // fetch log
            $sTagLog = $sTmpFileBase . '.' . $sTagName . '.log';

            $oSvnAdapter->logRevMerge(
                $iParentRev,
                $iCurrentRev,
                'tags/' . $sTagName . '/',
                false,
                $sTagLog
                );

            // parse commit info
            $aAllCommits = $oParserLog->parseWithMergeHistory($sTagLog);

            // add to html changelog
            $sHtml .= '<h3>' . $sTagName . ' - revisions: ' . $iParentRev . ' to ' . $iCurrentRev . '</h3>' . "\n";
            $sHtml .= '<table>' . "\n";
            $sHtml .= '<tr><th>Revision</th><th>Date</th><th>Author</th><th>Message</th></tr>' . "\n";

            foreach ($aAllCommits as $iRevision => $aCommit) {
                $sHtml .= '<tr>' . "\n";
                $sHtml .= '<td>' . $iRevision . '</td>' . "\n";
                $sHtml .= '<td>' . $aCommit['date'] . '</td>' . "\n";
                $sHtml .= '<td>' . $aCommit['author'] . '</td>' . "\n";
                $sHtml .= '<td>';
                
                if ($aCommit['message']) {
                    $sHtml .= $aCommit['message'];
                } else {
                    $sHtml .= '<span style="color: red;"> !!! NO COMMIT MESSAGE !!! </span>';
                }

                $sHtml .= '</td>' . "\n";
                $sHtml .= '</tr>' . "\n";

                if (count($aCommit['merges'])) {
                    $sHtml .= '<tr>' . "\n";
                    $sHtml .= '<td></td>' . "\n";
                    $sHtml .= '<td>Contains merges:</td>' . "\n";
                    $sHtml .= '<td colspan="2">' . "\n";

                    $sHtml .= '<table>' . "\n";

                    foreach ($aCommit['merges'] as $iMergeRev => $aMerge) {
                        $sHtml .= '<tr>' . "\n";
                        $sHtml .= '<td>' . $iMergeRev . '</td>' . "\n";
                        $sHtml .= '<td>' . $aMerge['date'] . '</td>' . "\n";
                        $sHtml .= '<td>' . $aMerge['author'] . '</td>' . "\n";
                        $sHtml .= '<td>';
                
                        if ($aMerge['message']) {
                            $sHtml .= $aMerge['message'];
                        } else {
                            $sHtml .= '<span style="color: red;"> !!! NO COMMIT MESSAGE !!! </span>';
                        }

                        $sHtml .= '</td>' . "\n";
                        $sHtml .= '</tr>' . "\n";
                    }

                    $sHtml .= '</table>' . "\n";

                    $sHtml .= '</td>' . "\n";
                }
            }

            $sHtml .= '</table' . "\n";

            $sHtml .= '<hr />' . "\n";

            $iCurrentRev = $iParentRev;
        }
        
        file_put_contents(PROJECT_ROOT . '/www/' . $this->_sProject . '.html', $sHtml);
    }
}
