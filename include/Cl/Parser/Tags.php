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
}
