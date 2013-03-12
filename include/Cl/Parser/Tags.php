<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Parser_Tags
{
    public function parseXml($sXmlFile)
    {
        $oXml = new Cl_XmlParserUtil($sXmlFile);

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

    public function parseLogXml($sXmlFile)
    {
        $oXml = new Cl_XmlParserUtil($sXmlFile);

        $aParsed = array();

        $aXmlList = $oXml->getElementTree('log');

        foreach ($aXmlList['children'] as $aXy) {
            if ($aXy['name'] == 'logentry') {
                $iRevision = $aXy['attrs']['revision'];
                $sAuthor = false;
                $sDate = false;
                $sCopyFromPath = false;
                $iCopyFromRev = false;
                $sMessage = false;

                foreach ($aXy['children'] as $aXmlEntry) {
                    if ($aXmlEntry['name'] == 'author') {
                        $sAuthor = $aXmlEntry['cdata'];
                    } elseif ($aXmlEntry['name'] == 'date') {
                        $sDate = $aXmlEntry['cdata'];
                    } elseif ($aXmlEntry['name'] == 'paths') {
                        foreach ($aXmlEntry['children'] as $aXmlTag) {
                            if ($aXmlTag['name'] == 'path') {
                                if (isset($aXmlTag['attrs']['copyfrom-path'])) {
                                    $sCopyFromPath = $aXmlTag['attrs']['copyfrom-path'];
                                }
                                
                                if (isset($aXmlTag['attrs']['copyfrom-rev'])) {
                                    $iCopyFromRev = $aXmlTag['attrs']['copyfrom-rev'];
                                }

                                $sTagName = str_replace('/tags/', '', $aXmlTag['cdata']);
                            }
                        }
                    } elseif ($aXmlEntry['name'] == 'msg') {
                        $sMessage = $aXmlEntry['cdata'];
                    }
                }

                // filter directly..
                if (!preg_match('/1[a-zA-Z0-9_-]$/', $sTagName)) {
                    continue;
                }

                $aParsed[$sTagName] = array(
                    'revision' => $iRevision,
                    'author' => $sAuthor,
                    'date' => $sDate,
                    'copyFromPath' => $sCopyFromPath,
                    'copyFromRev' => $iCopyFromRev,
                    'message' => $sMessage
                    );
            }
        }

        return $aParsed;
    }
}
