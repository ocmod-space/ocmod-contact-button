<?php

require_once 'ocmodw.ini.php';
require_once 'ocmodw.inc/ocmodw.fun.php';
require_once 'ocmodw.inc/ocmodw.req.php';
require_once 'ocmodw.inc/ocmodw.opt.php';

define('FCLDIR', '_fcl');
define('FCLIGNORE', '.fclignore'); // fcl ignorelist
define('SRCDIR', 'src');
define('ZIPDIR', 'zip');
define('ZIPEXT', '.ocmod.zip');

define('MDIR', 'module'); // module dir
define('ADIR', 'addons'); // addons dir

/*
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // error was suppressed with the @-operator
    if (@error_reporting() === 0) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
*/

$clo = get_clo();
$basename = strtolower(basename(getcwd()));
if (isset($clo[MAKEZIP]) && $clo[MAKEZIP] !== false) {
    $workdir = getWd($clo[MAKEZIP]);
    if ($workdir) {
        $subdir = $clo[WORKDIR] ?: '';
        $srcdir = getConcatPath($workdir, SRCDIR, $subdir);

        if (!is_dir($srcdir)) {
            output('There is no such source directory "' . $srcdir . '"', true);
        }

        $zipdir = getConcatPath($workdir, ZIPDIR, $subdir);

        if (strpos($workdir, ADIR) === 0) {
            $part = explode(DIRECTORY_SEPARATOR, $workdir);
            $basename .= '--' . end($part);

            unset($part);
        }

        define('MODFILE', $basename);

        $zipfile = str_replace('-', '_', getConcatPath($zipdir, MODFILE . ZIPEXT));

        $mod_code = str_replace('--', '|', $basename);

        define('MODCODE', $mod_code);

        $mod_name = str_replace('|', ' ', $mod_code);
        $mod_name = ucwords($mod_name);
        $mod_name = str_replace(' ', '|', $mod_name);
        $mod_name = str_replace('-', ' ', $mod_name);
        $mod_name = ucwords($mod_name);

        define('MODNAME', $mod_name);

        if (chkdir($srcdir) && chkdir($zipdir)) {
            if (is_file($zipfile)) {
                unlink($zipfile);
            }

            mkzip($srcdir, $zipfile, true);
        } else {
            output('Can not create dir: ' . $zipdir, true);
        }
    } else {
        output('There is no directory corresponding to number ' . $clo[MAKEZIP], true);
    }
} elseif (isset($clo[MAKEFCL]) || isset($clo[EXTRFCL]) || isset($clo[LISTFCL])) {
    $fclfile = getConcatPath(FCLDIR, $basename . '.fcl');

    if (isset($clo[MAKEFCL])) {
        chkdir(FCLDIR);

        output(fcl('make', $fclfile, '-f' . fclignore(FCLIGNORE)));
        output(hideg($fclfile));
    } elseif (isset($clo[EXTRFCL]) || isset($clo[LISTFCL])) {
        if (is_file($fclfile . '.g')) {
            output(hideg($fclfile . '.g'));

            if (is_file($fclfile)) {
                if (isset($clo[EXTRFCL])) {
                    output(fcl('extr', $fclfile, '-f'));
                }

                if (isset($clo[LISTFCL])) {
                    output(fcl('list', $fclfile));
                }
            } else {
                output('file "' . $fclfile . '" is missing!', true);
            }
        } else {
            output('file "' . $fclfile . '.g' . '" is missing!', true);
        }
    }

    if (is_file($fclfile)) {
        unlink($fclfile);
    }
} else {
    require_once 'ocmodw.inc/ocmodw.hlp.php';

    output('Numbers:');

    foreach (numbered() as $idx => $name) {
        output('[' . $idx . '] - ' . $name);
    }
}

exit(0);
