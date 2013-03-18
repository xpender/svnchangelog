<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Svn_Parser_List
{
    public function parseXml($sContent)
    {
        // init xml parser util
        $oXml = new Cl_XmlParserUtil();
        $oXml->loadString($sContent);

        // empty parsed array
        $aParsed = array();

        // get xml->lists tree
        $aXmlLists = $oXml->getElementTree('lists');

        // get xml->list tree
        $aXmlList = array_shift($aXmlLists['children']);

        // iterate..
        foreach ($aXmlList['children'] as $aXmlEntry) {
            // TODO: we only care about dir's
            if (isset($aXmlEntry['attrs']['kind']) && 'dir' == $aXmlEntry['attrs']['kind']) {
                $sName = false;
                $iRevision = false;
                $sAuthor = false;
                $sDate = false;

                foreach ($aXmlEntry['children'] as $v) {
                    if ($v['name'] == 'name') {
                        $sName = $v['cdata'];
                    } elseif ($v['name'] == 'commit') {
                        $iRevision = $v['attrs']['revision'];

                        foreach ($v['children'] as $v2) {
                            if ($v2['name'] == 'author') {
                                $sAuthor = $v2['cdata'];
                            } elseif ($v2['name'] == 'date') {
                                $sDate = $v2['cdata'];
                            }
                        }
                    }
                }

                $aParsed[] = array(
                    'kind' => 'dir',
                    'name' => $sName,
                    'revision' => $iRevision,
                    'author' => $sAuthor,
                    'date' => $sDate
                    );
            }
        }

        return $aParsed;
    }
}
