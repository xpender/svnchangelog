<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Svn_Parser_Log
{
    private function _parseXmlLogEntry($aXmlLogEntry)
    {
        $iRevision = false;
        $sAuthor = false;
        $sDate = false;
        $sMsg = false;
        $aPaths = array();
        $aMerges = array();

        if (isset($aXmlLogEntry['attrs']['revision'])) {
            $iRevision = $aXmlLogEntry['attrs']['revision'];
        }

        foreach ($aXmlLogEntry['children'] as $v) {
            if ($v['name'] == 'author') {
                $sAuthor = $v['cdata'];
            } elseif ($v['name'] == 'date') {
                $sDate = $v['cdata'];
            } elseif ($v['name'] == 'msg') {
                $sMsg = $v['cdata'];
            } elseif ($v['name'] == 'paths') {
                foreach ($v['children'] as $v2) {
                    $aTmp = array(
                        'kind' => $v2['attrs']['kind'],
                        'action' => $v2['attrs']['action'],
                        'name' => $v2['cdata']
                        );

                    if (isset($v2['attrs']['copyfrom-path'])) {
                        $aTmp['copyfrom-path'] = $v2['attrs']['copyfrom-path'];
                    }

                    if (isset($v2['attrs']['copyfrom-rev'])) {
                        $aTmp['copyfrom-rev'] = $v2['attrs']['copyfrom-rev'];
                    }

                    $aPaths[] = $aTmp;
                }
            } elseif ($v['name'] == 'logentry') {
                $aMerges[] = $this->_parseXmlLogEntry(
                    $v
                    );
            }
        }
            
        $aParsed = array(
            'revision' => $iRevision,
            'author' => $sAuthor,
            'date' => $sDate,
            'msg' => $sMsg,
            'paths' => $aPaths,
            'merges' => $aMerges
            );
    
        return $aParsed;
    }

    public function parseXml($sContent)
    {
        // init xml parser util
        $oXml = new Cl_XmlParserUtil();
        $oXml->loadString($sContent);

        // empty parsed array
        $aParsed = array();

        // get xml->log tree
        $aXmlLog = $oXml->getElementTree('log');

        // iterate over log children, which must be "logentry" childs..
        foreach ($aXmlLog['children'] as $aXmlLogEntry) {
            $aParsed[] = $this->_parseXmlLogEntry(
                $aXmlLogEntry
                );
        }

        return $aParsed;
    }
}
