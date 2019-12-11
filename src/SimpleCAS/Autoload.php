<?php
function SimpleCAS_Autoload($class)
{
    if (substr($class, 0, 9) !== 'SimpleCAS') {
        return false;
    }
    $fp = @fopen(str_replace('_', '/', $class) . '.php', 'r', true);
    if ($fp) {
        fclose($fp);
        require str_replace('_', '/', $class) . '.php';
        if (!class_exists($class, false) && !interface_exists($class, false)) {
            die(new Exception('Class ' . $class . ' was not present in ' .
                str_replace('_', '/', $class) . '.php (include_path="' . get_include_path() .
                '") [SimpleCAS_Autoload version 0.1.0]'));
        }
        return true;
    }
    $e = new Exception('Class ' . $class . ' could not be loaded from ' .
        str_replace('_', '/', $class) . '.php, file does not exist (include_path="' . get_include_path() .
        '") [SimpleCAS_Autoload version 0.1.0]');
    $trace = $e->getTrace();
    if (isset($trace[2]) && isset($trace[2]['function']) &&
          in_array($trace[2]['function'], array('class_exists', 'interface_exists'))) {
        return false;
    }
    if (isset($trace[1]) && isset($trace[1]['function']) &&
          in_array($trace[1]['function'], array('class_exists', 'interface_exists'))) {
        return false;
    }
    die ((string) $e);
}

// set up __autoload
if (!($_____t = spl_autoload_functions()) || !in_array('SimpleCAS_Autoload', spl_autoload_functions())) {
    spl_autoload_register('SimpleCAS_Autoload');
    if (function_exists('__autoload') && ($_____t === false)) {
        // __autoload() was being used, but now would be ignored, add
        // it to the autoload stack
        spl_autoload_register('__autoload');
    }
}
unset($_____t);

// set up include_path if it doesn't register our current location
$____paths = explode(PATH_SEPARATOR, get_include_path());
$____found = false;
foreach ($____paths as $____path) {
    if ($____path == dirname(dirname(__FILE__))) {
        $____found = true;
        break;
    }
}
if (!$____found) {
    set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)));
}
unset($____paths);
unset($____path);
unset($____found);
