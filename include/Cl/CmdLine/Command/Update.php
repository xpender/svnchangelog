<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_CmdLine_Command_Update extends Cl_CmdLine_CommandAbstract
{
    /**
     * project..
     *
     * @var string
     */
    private $_sProject;

    /**
     * project specific config
     *
     * @var array
     */
    private $_aConfig;

    /**
     * svn adapter
     *
     * @var Cl_Svn_Adapter
     */
    private $_oSvnAdapter;

    /**
     * __construct
     *
     * @param string $sProject
     */
    public function __construct($sProject)
    {
        // is project valid?
        if (!Cl_Config::getInstance()->hasProject($sProject)) {
            echo '[!] unknown project given: ' . $sProject . "\n";

            exit;
        }

        // set project
        $this->_sProject = $sProject;

        // load project specific config
        $this->_aConfig = Cl_Config::getInstance()->getProject($sProject);

        // parent construct..
        parent::__construct();
    }

    /**
     * executes command
     */
    protected function _execute()
    {
        // get tags data storage
        $oDataTags = new Cl_Data_Tags(
            $this->_sProject
            );

        // update tags with latest from SCM
        echo "[*] updating tags from scm\n";

        $this->_updateTagsData($oDataTags);

        // update commits with latest from SCM
        echo "[*] updating commits..\n";

        $this->_updateCommitData($oDataTags);

        // finished..
        echo "[*] update finished\n";
    }

    /**
     * fetches latest tags from SCM and updates given data storage
     *
     * @param Cl_Data_Tags $oDataTags
     */
    private function _updateTagsData(Cl_Data_Tags &$oDataTags)
    {
        // tmp file for scm..
        $sTmpFile = Cl_Config::getInstance()->getTmpPath() . md5($this->_sProject) . '.tags.list.xml';

        // call SCM..
        $bSvn = $this->_getSvnAdapter()->log(
            'tags/',
            true,
            true,
            $sTmpFile
            );

        if (!$bSvn) {
            echo "[!] svn error\n";
            exit;
        }

        // init parser..
        $oParserTags = new Cl_Parser_Tags();

        // parse
        $aAllTags = $oParserTags->parseLogXml(
            $sTmpFile
            );

        // iterate over and store into Cl_Data_Tags
        foreach ($aAllTags as $sTag => $aInfo) {
            // filter non release & feature tags
            if (strpos($sTag, 'RELEASE_') !== 0) {
                continue;
            }

            if (!preg_match('/^\/branches\/[a-zA-Z0-9_\-\.]+$/', $aInfo['copyFromPath'])) {
                continue;
            }

            $aInfo['branch'] = str_replace('/branches/', '', $aInfo['copyFromPath']);

            if (!$oDataTags->exists($sTag)) {
                $oDataTags->insert(
                    $sTag,
                    array(
                        'tag' => $sTag,
                        'revision.scm' => $aInfo['revision'],
                        'revision.local' => 0,
                        'branch.name' => $aInfo['branch'],
                        'branch.rev' => $aInfo['copyFromRev']
                        )
                    );
            } else {
                $aLocal = $oDataTags->get($sTag);

                $oDataTags->update(
                    $sTag,
                    array(
                        'tag' => $sTag,
                        'revision.scm' => $aInfo['revision'],
                        'revision.local' => $aLocal['revision.local'],
                        'branch.name' => $aInfo['branch'],
                        'branch.rev' => $aInfo['copyFromRev']
                        )
                    );
            }
        }
    }

    /**
     * update commits for all tags with SCM data
     *
     * @param Cl_Data_Tags $oDataTags
     */
    private function _updateCommitData(Cl_Data_Tags &$oDataTags)
    {
        // tmp file base..
        $sTmpFileBase = Cl_Config::getInstance()->getTmpPath() . md5($this->_sProject);

        // init log parser..
        $oParserLog = new Cl_Parser_Log();

        // generate helper arrays
        $aTagsByRev = array();
        $aTagsToBranch = array();
        $aBranchRevisions = array();

        foreach ($oDataTags->all() as $aTag) {
            // rev -> tag
            $aTagsByRev[$aTag['revision.scm']] = $aTag['tag'];
            
            // tag -> branch
            $aTagsToBranch[$aTag['tag']] = array(
                'name' => $aTag['branch.name'],
                'rev' => $aTag['branch.rev']
                );

            // branch -> revisions
            $aBranchRevisions[$aTag['branch.name']][] = $aTag['branch.rev'];

            arsort($aBranchRevisions[$aTag['branch.name']], SORT_NUMERIC);

            // rev -> branch
            $aBranchByRev[$aTag['branch.rev']] = $aTag['branch.name'];

            $aBranchRevs[] = $aTag['branch.rev'];
        }

        krsort($aTagsByRev);

        uksort(
            $aBranchRevisions,
            'Cl_Util_Sort::sortByBranchVersion'
            );

        krsort($aBranchRevs);

        // iterate over tags..
        foreach ($aTagsByRev as $sTagName) {
            // branch infos
            $aCurBranch = $aTagsToBranch[$sTagName];
            $aCurBranchRevs = $aBranchRevisions[$aCurBranch['name']];

            // check if revision local & scm differs..
            $aTagData = $oDataTags->get($sTagName);

            if ($aTagData['revision.scm'] == $aTagData['revision.local']) {
                echo "[-] Skipping $sTagName\n";

                continue;
            }
            
            // log
            echo '[+] Updating ' . $sTagName . ' - Branch: ' . $aCurBranch['name'] . '@' . $aCurBranch['rev'] . "\n";

            // find previous branch rev on which we compare..
            $iParentRev = false;

            $k = array_search($aCurBranch['rev'], $aCurBranchRevs) + 1;

            if (isset($aCurBranchRevs[$k])) {
                $iParentRev = $aCurBranchRevs[$k];
            }
            
            // tmp file
            $sTmpFile = $sTmpFileBase . '.' . str_replace(array('/', ' ', "'", '"'), '-', $sTagName) . '.log';

            if (!$iParentRev) {
                /**
                 * Sorry, in this if case this get's stupid and wired.
                 *
                 * Basically, old version of svnchangelog compared tags by parent tag revision and current tag revision
                 * using "svn log" with parameter "--use-merge-history".
                 *
                 * The problem we found on our repositories that on some tags it runs randomly into a loop.
                 * It's really one tag doesn't work. Another some minutes ago works.. Both been copied from the same branch
                 * and the only difference was one/two files been merged in or commited directly to the branch.
                 * 
                 * Happend at more than one repostiroy. Maybe, we shouldn't use SVN with Branches.. But yeah..
                 *
                 * That's the reason I rewrote this tool and this solution works better.. Still not perfect.
                 *
                 * Probably I need to redo this again and get every commit with all meta data (incl. svn:merge-info) and create
                 * the branch/tag/merge tree on my own. Next time :/
                 */

                $k = array_search($aCurBranch['rev'], $aBranchRevs) + 1;

                if (!isset($aBranchRevs[$k])) {
                    echo "[!] Skipped: " . $sTagName . "\n";
                    
                    continue;
                }

                // get parent branch$
                $sParentBranch = $aBranchByRev[$aBranchRevs[$k]];

                $iParentBranchRev = array_shift($aBranchRevisions[$sParentBranch]);

                // call svn adapter
                $bSvn = $this->_getSvnAdapter()->logRevMerge(
                    $iParentBranchRev,
                    $aCurBranch['rev'],
                    'tags/' . $sTagName . '/',
                    true,
                    false,
                    $sTmpFile
                    );
            } else {
                // call svn adapter
                $bSvn = $this->_getSvnAdapter()->logRevMerge(
                    $iParentRev,
                    $aCurBranch['rev'],
                    'branches/' . $aCurBranch['name'] . '/',
                    false,
                    false,
                    $sTmpFile
                    );
            }

            if (!$bSvn) {
                echo "[!] svn error\n";

                continue;
            }

            // parse commit info
            $aAllCommits = $oParserLog->parseWithMergeHistory(
                $sTmpFile
                );

            // get data class
            $oDataTagCommits = new Cl_Data_TagCommits(
                $this->_sProject,
                $sTagName
                );

            // truncate local data..
            $oDataTagCommits->truncate();

            // save them to data..
            foreach ($aAllCommits as $iRevision => $aCommit) {
                $oDataTagCommits->update(
                    $iRevision,
                    array(
                        'revision' => $iRevision,
                        'author' => $aCommit['author'],
                        'date' => $aCommit['date'],
                        'message' => (isset($aCommit['message']) && strlen(trim($aCommit['message'])) > 0) ? $aCommit['message'] : false,
                        'merges' => (isset($aCommit['merges']) && count($aCommit['merges']) > 0) ? $aCommit['merges'] : false
                        )
                    );
            }

            // update data tags
            $aTagData['revision.local'] = $aTagData['revision.scm'];

            $oDataTags->update(
                $sTagName,
                $aTagData
                );
        }
    }

    /**
     * inits and returns svn adapter
     *
     * @return Cl_Svn_Adapter
     */
    private function _getSvnAdapter()
    {
        if (null === $this->_oSvnAdapter) {
            $this->_oSvnAdapter = new Cl_Svn_Adapter(
                $this->_aConfig
                );
        }

        return $this->_oSvnAdapter;
    }
}
