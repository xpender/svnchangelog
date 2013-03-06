<?php
/**
 * Changelog generator
 *
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */

$aConfig = array();

// Data Directory
$aConfig['data.path'] = PROJECT_ROOT . '/data/';

// Projects
$aConfig['projects']['example'] = array(
    'svn.server' => 'svn',
    'svn.repo' => 'example',
    'svn.auth' => 'userpass',
    'svn.user' => 'svn',
    'svn.pass' => '***'
    );
