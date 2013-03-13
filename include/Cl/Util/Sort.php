<?php
/**
 * svnchangelog
 *
 * Sorting helper functions
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Util_Sort
{
    public static function sortByBranchVersion($a, $b)
    {
        // remove prefix..
        $a = str_replace('RELEASE_', '', $a);
        $b = str_replace('RELEASE_', '', $b);

        // split by '.'
        $at = explode('.', $a);
        $bt = explode('.', $b);

        // create nice variables
        $a_master = (isset($at[0]) ? $at[0] : 0);
        $a_major = (isset($at[1]) ? $at[1] : 0);
        $a_minor = (isset($at[2]) ? $at[2] : 0);
        $a_patch = (isset($at[3]) ? $at[3] : 0);

        $b_master = (isset($bt[0]) ? $bt[0] : 0);
        $b_major = (isset($bt[1]) ? $bt[1] : 0);
        $b_minor = (isset($bt[2]) ? $bt[2] : 0);
        $b_patch = (isset($bt[3]) ? $bt[3] : 0);

        // compare master
        if ($a_master > $b_master) {
            return 1;
        } elseif ($a_master < $b_master) {
            return -1;
        }

        // compare major
        if ($a_major > $b_major) {
            return 1;
        } elseif ($a_major < $b_major) {
            return -1;
        }

        // compare minor
        if ($a_minor > $b_minor) {
            return 1;
        } elseif ($a_minor < $b_minor) {
            return -1;
        }

        // compare patch
        if ($a_patch > $b_minor) {
            return 1;
        } elseif ($a_patch < $b_patch) {
            return -1;
        }

        // must be identical - whut ;)
        return 0;
    }
}
