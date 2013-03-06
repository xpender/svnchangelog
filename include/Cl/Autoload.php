<?php
class Cl_Autoload
{
    public static function autoload($sClassName)
    {
        if (class_exists($sClassName, false)) {
            return false;
        }

        require str_replace('_', PATH_SEPARATOR, $sClassName) . '.php';
    }
}
