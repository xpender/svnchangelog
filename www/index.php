<?php
// include bootstrap
require realpath(str_replace(basename(__FILE__), '', __FILE__) . '/../include/bootstrap.php');

// start session
session_start();

// simple session adapter..
Cl_Frontend_Session::init($_SESSION);

// project switch..
if (isset($_REQUEST['project'])) {
    if (Cl_Frontend_Session::getProject() != $_REQUEST['project']) {
        Cl_Frontend_Session::setProject(
            $_REQUEST['project']
            );
    }
}

// le controller does the magic ;)
$oController = new Cl_Frontend_Controller();

$oController->dispatch(
    $_REQUEST
    );
