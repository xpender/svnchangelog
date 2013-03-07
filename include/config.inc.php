<?php
/**
 * Changelog generator
 *
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */

$aConfig = array();

// Data Directory
$aConfig['data.path'] = PROJECT_ROOT . '/data/';
$aConfig['tmp.path'] = PROJECT_ROOT . '/tmp/';

// Projects
$aConfig['projects']['example'] = array(
    'svn.proto' => 'http',
    'svn.server' => 'svn',
    'svn.repo' => 'example',
    'svn.auth' => 'userpass',
    'svn.user' => 'svn',
    'svn.pass' => '***'
    );
