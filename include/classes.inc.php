<?php
class Command_ShowUsage
{
    public function __construct()
    {
        echo "# ChangeLog Generator\n";
        echo "Usage:\n";
        echo "    [project]\n";
        echo "    user [project] [username]\n";
    }
}

class Command_ByUser
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

        Svn::cmdLog($sSvnBaseUrl, $sLog);

        // get parser for log
        $oParserLog = new Parser_Log();

        // parse
        $aAllCommits = $oParserLog->parseWithUsername($sLog, $this->_sUsername);

        // output
        $sOutput = '';

        foreach ($aAllCommits as $iRev => $aCommit) {
            $i++;

            $sOutput .= 'R' . $iRev . " - " . $aCommit['date'] . "\n";
            $sOutput .= $aCommit['message'] . "\n\n";

            $sDiff = Svn::cmdDiff($sSvnBaseUrl, $iRev - 1, $iRev);

            $sOutput .= $sDiff . "\n\n";

            $sOutput .= str_repeat('-', 30) . "\n\n";
        }

        file_put_contents(PROJECT_ROOT . '/data/log-by-user-' . $this->_sUsername . '.txt', $sOutput);
    }
}

class Command_Build
{
    private $_sProject;

    private $_aConfig;

    public function __construct($sProject)
    {
        global $_PROJECTS;

        $this->_sProject = $sProject;

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
        $sSvnBaseUrl = $this->_getSvnBaseUrl();

        // tmp file base
        $sTmpFileBase = PROJECT_ROOT . '/tmp/' . md5($sSvnBaseUrl/* . microtime(true)*/);

        // fetch list of tags
        $sTagsList = $sTmpFileBase . '.tags.list.xml';

        Svn::cmdList($sSvnBaseUrl . '/tags', $sTagsList);

        // parser all tags
        $oParserTags = new Parser_Tags();

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
        $oParserLog = new Parser_Log();

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

            Svn::cmdLogRevMerge($sSvnBaseUrl . '/tags/' . $sTagName, $iParentRev, $iCurrentRev, $sTagLog);

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

class Parser_Tags
{
    public function __construct()
    {

    }

    public function parseXml($sXmlFile)
    {
        $oXml = new Xml_ParserUtil($sXmlFile);

        $aParsed = array();

        $aXmlList = $oXml->getElementTree('list');

        foreach ($aXmlList['children'] as $aYy) {
            foreach ($aYy['children'] as $aXmlEntry) {
                if ($aXmlEntry['attrs']['kind'] == 'dir') {
                    $sTagName = false;
                    $iRevision = false;
                    $sAuthor = false;
                    $sDate = false;

                    foreach ($aXmlEntry['children'] as $aXmlTag) {
                        if ($aXmlTag['name'] == 'name') {
                            $sTagName = $aXmlTag['cdata'];
                        } elseif ($aXmlTag['name'] == 'commit') {
                            $iRevision = $aXmlTag['attrs']['revision'];

                            foreach ($aXmlTag['children'] as $aXx) {
                                if ($aXx['name'] == 'author') {
                                    $sAuthor = $aXx['cdata'];
                                } elseif ($aXx['name'] == 'date') {
                                    $sDate = $aXx['cdata'];
                                }
                            }
                        }
                    }

                    if ($sTagName && $iRevision && $sAuthor && $sDate) {
                        $aParsed[$sTagName] = array(
                            'revision' => $iRevision,
                            'author' => $sAuthor,
                            'date' => $sDate
                            );
                    }
                }
            }
        }
    
        return $aParsed;
    }
}

class Parser_Log
{
    public function parseWithUsername($sLogFile, $sUsername)
    {
        $sContent = file_get_contents($sLogFile);

        $aSeperatedCommits = explode('------------------------------------------------------------------------', $sContent);

        $aCommits = array();

        foreach ($aSeperatedCommits as $sCommit) {
            $sCommit = trim($sCommit);

            // valid?
            if (preg_match('/r([0-9]+) \| ([a-zA-z]+) \| ([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})/', $sCommit, $aMatches)) {
                $aLines = explode("\n", $sCommit);

                // match stuff
                $iRevision = $aMatches[1];
                $sAuthor = $aMatches[2];
                $sDate = $aMatches[3];
                $sMessage = false;

                // message should start at line 2
                if (isset($aLines[2])) {
                    $sMessage = implode("\n", array_slice($aLines, 2));
                }

                if ($sAuthor != $sUsername) {
                    continue;
                }

                $aCommits[$iRevision] = array(
                    'author' => $sAuthor,
                    'date' => $sDate,
                    'message' => $sMessage
                    );
            }
        }

        return $aCommits;
    }

    public function parseWithMergeHistory($sLogFile)
    {
        $sContent = file_get_contents($sLogFile);

        $aSeperatedCommits = explode('------------------------------------------------------------------------', $sContent);

        $aCommits = array();

        foreach ($aSeperatedCommits as $sCommit) {
            $sCommit = trim($sCommit);

            // valid?
            if (preg_match('/r([0-9]+) \| ([a-zA-z]+) \| ([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})/', $sCommit, $aMatches)) {
                $aLines = explode("\n", $sCommit);

                // match stuff
                $iRevision = $aMatches[1];
                $sAuthor = $aMatches[2];
                $sDate = $aMatches[3];
                $sMessage = false;

                // merged via?
                if (preg_match('/Merged via: r([0-9]+)/', $sCommit, $aXy)) {
                    $iMergedBy = $aXy[1];

                    // message should start at line 3
                    if (isset($aLines[3])) {
                        $sMessage = implode("\n", array_slice($aLines, 3));
                    }

                    $aCommits[$iMergedBy]['merges'][$iRevision] = array(
                        'author' => $sAuthor,
                        'date' => $sDate,
                        'message' => $sMessage
                        );
                } else {
                    // message should start at line 2
                    if (isset($aLines[2])) {
                        $sMessage = implode("\n", array_slice($aLines, 2));
                    }

                    $aCommits[$iRevision] = array(
                        'author' => $sAuthor,
                        'date' => $sDate,
                        'message' => $sMessage
                        );
                }
            }
        }

        return $aCommits;
    }
}

class Svn
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

/**
 * Opens and reads data from an XML file or string.
 *
 * @author Marcel Werk
 * @author Marko Kercmar (Modifications)
 * @copyright 2001-2009 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package net.bigpoint.rama.language
 */
class Xml_ParserUtil
{
    protected $_encoding = 'UTF-8';
    protected $_xmlObj = null;
    
    /**
     * Contructs a new XML object.
     * Optional parameter is a filename of an XML file.
     *
     * @param string $filename
     */
    public function __construct($filename = '')
    {
        if ($filename != '') {
            $this->loadFile($filename);
        }
    }
    
    /**
     * Loads and parses an XML file.
     *
     * @param string $filename
     */
    public function loadFile($filename)
    {
        $this->_encoding = $this->detectEncoding(file_get_contents($filename));
        $this->_xmlObj = simplexml_load_file($filename);
        
        if ($this->_xmlObj === false) {
            throw new Rama_Language_Exception(
                "file '".$filename."' is not a valid xml document"
                );
        }
    }
    
    /**
     * Parses a string of xml data.
     *
     * @param string $sourceContent
     */
    public function loadString($sourceContent)
    {
        $this->_encoding = $this->detectEncoding($sourceContent);
        $this->_xmlObj = simplexml_load_string($sourceContent);

        if ($this->_xmlObj === false) {
            throw new Rama_Language_Exception(
                "given string is not a valid xml document"
                );
        }
    }
    
    /**
     * Sends a xpath query and
     * returns an array of SimpleXMLElements.
     * This is actually a wrapper for SimpleXMLElement::xpath().
     *
     * @param string $path
     * @return array $result
     */
    public function xpath($path)
    {
        return $this->_xmlObj->xpath($path);
    }
    
    /**
     * Returns an array with all elements of an SimpleXML Object.
     * The array has the following structure:
     * [name] => tagName
     * [attrs] => Array
     * (
     * )
     * [children] => Array
     * (
     * )
     *
     *
     * attrs contains all attributes in an associative array.
     * children contains sub elements (with the same structure as above).
     *
     * @param string $name
     * @param SimpleXMLElement $xmlObj
     * @return array $element
     */
    public function getElementTree($name, $xmlObj = null)
    {
        if (!($xmlObj instanceof SimpleXMLElement)) {
            $xmlObj = $this->_xmlObj;
        }

        $element = array('name' => $name);

        $element['attrs'] = $this->getAttributes($xmlObj);
        $element['cdata'] = $this->getCDATA($xmlObj);
        $element['children'] = $this->getChildren($xmlObj, true);
        
        return $element;
    }
    
    /**
     * Returns the CDATA of an XML element.
     *
     * @param SimpleXMLElement $xmlObj
     * @return string $CDATA
     */
    public function getCDATA($xmlObj = null)
    {
        if (!($xmlObj instanceof SimpleXMLElement)) {
            $xmlObj = $this->_xmlObj;
        }

        if (trim((string)$xmlObj) != '') {
            return (string)$xmlObj;
        } else {
            return '';
        }
    }
    
    /**
     * Returns an array of sub elements.
     * If this method is called from XML::getElementTree(), it
     * works recursively.
     *
     * @param SimpleXMLElement $xmlObj
     * @param boolean $tree
     * @return array $childrenArray
     */
    public function getChildren($xmlObj = null, $tree = false)
    {
        if (!($xmlObj instanceof SimpleXMLElement)) {
            $xmlObj = $this->_xmlObj;
        }

        $childrenArray = array();
        
        $children = $xmlObj->children();
        
        foreach ($children as $key => $childObj) {
            if ($tree) {
                $childrenArray[] = $this->getElementTree($key, $childObj);
            } else {
                $childrenArray[] = $childObj;
            }
        }

        return $childrenArray;
    }
    
    /**
     * Returns an associative array with attributes of an XML element.
     *
     * @param SimpleXMLElement $xmlObj
     * @param array $attributesArray
     */
    public function getAttributes($xmlObj = null)
    {
        if (!($xmlObj instanceof SimpleXMLElement)) {
            $xmlObj = $this->_xmlObj;
        }

        $attributesArray = array();
        $attributes = $xmlObj->attributes();

        foreach ($attributes as $key => $val) {
            $attributesArray[$key] = (string)$val;
        }
        
        return $attributesArray;
    }
    
    /**
     * Returns the encoding of this xml document.
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }
    
    /**
     * Detects encoding of an XML file.
     *
     * @param string $xmlSource
     * @return string $encoding
     */
    protected static function detectEncoding($xmlSource)
    {
        $matches = array();

        if (preg_match('/<\?xml.*encoding="([a-z0-9-]+)".*\?>/i', $xmlSource, $matches)) {
            $encoding = strtoupper($matches[1]);
        } else {
            $encoding = 'UTF-8';
        }
        
        return $encoding;
    }
}
