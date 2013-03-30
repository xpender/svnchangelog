<?php
/**
 * Changelog Generator
 *
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */

// project root
define('PROJECT_ROOT', realpath(str_replace(basename(__FILE__), '', __FILE__) . '/../'));

// set include_path
ini_set(
    'include_path',
    '.:' . PROJECT_ROOT . '/include/:' . ini_get('include_path')
    );

// autoloader
require 'Cl/Autoload.php';

spl_autoload_register(
    array(
        'Cl_Autoload',
        'autoload'
        )
    );

// get config
require 'config.inc.php';
