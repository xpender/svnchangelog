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
        $this->_getSvnAdapter()->llist(
            'tags/',
            true,
            $sTmpFile
            );

        // init parser..
        $oParserTags = new Cl_Parser_Tags();

        // parse tags xml
        $aAllTags = $oParserTags->parseXml(
            $sTmpFile
            );

        // iterate over and store into Cl_Data_Tags
        foreach ($aAllTags as $sTag => $aInfo) {
            // filter non release & feature tags
            if (strpos($sTag, 'RELEASE_') !== 0) {
                continue;
            }

            if (!$oDataTags->exists($sTag)) {
                $oDataTags->insert(
                    $sTag,
                    array(
                        'tag' => $sTag,
                        'revision.scm' => $aInfo['revision'],
                        'revision.local' => 0
                        )
                    );
            } else {
                $aLocal = $oDataTags->get($sTag);

                $oDataTags->update(
                    $sTag,
                    array(
                        'tag' => $sTag,
                        'revision.scm' => $aInfo['revision'],
                        'revision.local' => $aLocal['revision.local']
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

        // generate list of tags by revision
        $aTagsByRev = array();

        foreach ($oDataTags->all() as $aTag) {
            $aTagsByRev[$aTag['revision.scm']] = $aTag['tag'];
        }

        // get revs..
        $aRevs = array_keys($aTagsByRev);

        // get current rev
        $iCurrentRev = array_shift($aRevs);

        // iterate..
        foreach ($aTagsByRev as $sTagName) {
            // get parent revs
            $iParentRev = array_shift($aRevs);

            if ($iParentRev == null) {
                break;
            }

            // check if revision local & scm differs..
            $aTagData = $oDataTags->get($sTagName);

            if ($aTagData['revision.scm'] == $aTagData['revision.local']) {
                echo "[-] Skipping $sTagName\n";

                continue;
            }

            // update this..
            echo "[+] Updating $sTagName\n";

            // tmp file
            $sTmpFile = $sTmpFileBase . '.' . str_replace(array('/', ' ', "'", '"'), '-', $sTagName) . '.log';

            // call svn adapter
            $this->_getSvnAdapter()->logRevMerge(
                $iParentRev,
                $iCurrentRev,
                'tags/' . $sTagName . '/',
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
                        'merges' => (count($aCommit['merges']) > 0) ? $aCommit['merges'] : false
                        )
                    );
            }

            // update data tags
            $aTagData['revision.local'] = $aTagData['revision.scm'];

            $oDataTags->update(
                $sTagName,
                $aTagData
                );

            // update current rev
            $iCurrentRev = $iParentRev;
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
