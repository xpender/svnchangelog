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
        $this->_getSvnAdapter()->log(
            'tags/',
            true,
            true,
            $sTmpFile
            );

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
        }

        krsort($aTagsByRev);

        uksort(
            $aBranchRevisions,
            'Cl_Util_Sort::sortByBranchVersion'
            );

        // iterate over tags..
        foreach ($aTagsByRev as $sTagName) {
            // branch infos
            $aBranch = $aTagsToBranch[$sTagName];
            $aBranchRevs = $aBranchRevisions[$aBranch['name']];

            // check if revision local & scm differs..
            $aTagData = $oDataTags->get($sTagName);

            if ($aTagData['revision.scm'] == $aTagData['revision.local']) {
                echo "[-] Skipping $sTagName\n";

                continue;
            }
            
            // log
            echo '[+] Updating ' . $sTagName . ' - Branch: ' . $aBranch['name'] . '@' . $aBranch['rev'] . "\n";

            // find previous branch rev on which we compare..
            $iParentRev = false;

            $k = array_search($aBranch['rev'], $aBranchRevs) + 1;

            if (isset($aBranchRevs[$k])) {
                $iParentRev = $aBranchRevs[$k];
            }

            // TODO: implement compare between branches
            if (!$iParentRev) {
                echo "[!] Skipped: " . $sTagName . "\n";

                continue;
            }
            
            // tmp file
            $sTmpFile = $sTmpFileBase . '.' . str_replace(array('/', ' ', "'", '"'), '-', $sTagName) . '.log';

            // call svn adapter
            $this->_getSvnAdapter()->logRevMerge(
                $iParentRev,
                $aBranch['rev'],
                'branches/' . $aBranch['name'] . '/',
                false,
                $sTmpFile
                );

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
