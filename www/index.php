<?php
// include bootstrap
require realpath(str_replace(basename(__FILE__), '', __FILE__) . '/../include/bootstrap.php');

// le controller does the magic ;)
$oController = new Cl_Frontend_Controller();
$oController->dispatch($_REQUEST);
