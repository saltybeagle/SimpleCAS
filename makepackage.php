<?php
/**
 * Make package file for the SimpleCAS package.
 * 
 * PHP version 5
 * 
 * @category  Authentication 
 * @package   SimpleCAS
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2008 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://code.google.com/p/simplecas/
 */

/**
 * Require the PEAR_PackageFileManager2 classes, and other
 * necessary classes for package.xml file creation.
 */
require_once 'PEAR/PackageFileManager2.php';
require_once 'PEAR/PackageFileManager/File.php';
require_once 'PEAR/Task/Postinstallscript/rw.php';
require_once 'PEAR/Config.php';
require_once 'PEAR/Frontend.php';

PEAR::setErrorHandling(PEAR_ERROR_DIE);
chdir(dirname(__FILE__));

$options = array(
    'packagedirectory'  => dirname(__FILE__),
    'baseinstalldir'    => '/',
    'filelistgenerator' => 'svn',
    'ignore' => array(  'package.xml',
                        '.project',
                        '*.tgz',
                        'makepackage.php',
                        'TODO',
                        '*tests*',
                        '*scripts*',
                        '*HTTP*'),
    'simpleoutput' => true,
    'roles'        => array('php'=>'php'),
    'exceptions'   => array()
);

if (file_exists(dirname(__FILE__).'/package.xml')) {
    $pfm = PEAR_PackageFileManager2::importOptions('package.xml', $options);  
} else {
    $pfm = new PEAR_PackageFileManager2();
    $pfm->setOptions($options);
}

$pfm->setPackage('SimpleCAS');
$pfm->setPackageType('php'); // this is a PEAR-style php script package
$pfm->setSummary('A PHP5 library for CAS Authentication.');
$pfm->setDescription('This package is a PHP5 only library for identifying users
in a JA-SIG CAS secured environment.');
$pfm->setChannel('simplecas.googlecode.com/svn');
$pfm->setAPIStability('alpha');
$pfm->setReleaseStability('alpha');
$pfm->setAPIVersion('0.1.1');
$pfm->setReleaseVersion('0.1.1');
$pfm->setNotes('
* Fix Notice: Trying to get property of non-object in SimpleCAS/Server/Version2/ValidationResponse.php on line 23
* Change PHP dependency to 5.2.5
');

$pfm->updatemaintainer('lead','saltybeagle','Brett Bieber','brett.bieber@gmail.com');
$pfm->setLicense('BSD License', 'http://www1.unl.edu/wdn/wiki/Software_License');

$pfm->clearDeps();
$pfm->setPhpDep('5.2.5');
$pfm->setPearinstallerDep('1.4.3');
$pfm->addPackageDepWithChannel('required', 'HTTP_Request2', 'pear.php.net', '0.1.0');

$pfm->generateContents();
$pfm->writePackageFile();
