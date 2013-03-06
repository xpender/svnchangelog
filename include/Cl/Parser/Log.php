<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Parser_Log
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
