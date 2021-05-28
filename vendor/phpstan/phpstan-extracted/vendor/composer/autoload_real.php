<?php

namespace RectorPrefix20210528;

// autoload_real.php @generated by Composer
class ComposerAutoloaderInit76efdea48d97ddd5e412eb932aafadf4
{
    private static $loader;
    public static function loadClassLoader($class)
    {
        if ('Composer\\Autoload\\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }
    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }
        \spl_autoload_register(array('RectorPrefix20210528\\ComposerAutoloaderInit76efdea48d97ddd5e412eb932aafadf4', 'loadClassLoader'), \true, \true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(\dirname(__FILE__)));
        \spl_autoload_unregister(array('ComposerAutoloaderInit76efdea48d97ddd5e412eb932aafadf4', 'loadClassLoader'));
        $useStaticLoader = \PHP_VERSION_ID >= 50600 && !\defined('HHVM_VERSION') && (!\function_exists('zend_loader_file_encoded') || !\zend_loader_file_encoded());
        if ($useStaticLoader) {
            require __DIR__ . '/autoload_static.php';
            \call_user_func(\RectorPrefix20210528\Composer\Autoload\ComposerStaticInit76efdea48d97ddd5e412eb932aafadf4::getInitializer($loader));
        } else {
            $classMap = (require __DIR__ . '/autoload_classmap.php');
            if ($classMap) {
                $loader->addClassMap($classMap);
            }
        }
        $loader->setClassMapAuthoritative(\true);
        $loader->register(\true);
        if ($useStaticLoader) {
            $includeFiles = \RectorPrefix20210528\Composer\Autoload\ComposerStaticInit76efdea48d97ddd5e412eb932aafadf4::$files;
        } else {
            $includeFiles = (require __DIR__ . '/autoload_files.php');
        }
        foreach ($includeFiles as $fileIdentifier => $file) {
            \RectorPrefix20210528\composerRequire76efdea48d97ddd5e412eb932aafadf4($fileIdentifier, $file);
        }
        return $loader;
    }
}
// autoload_real.php @generated by Composer
\class_alias('RectorPrefix20210528\\ComposerAutoloaderInit76efdea48d97ddd5e412eb932aafadf4', 'ComposerAutoloaderInit76efdea48d97ddd5e412eb932aafadf4', \false);
function composerRequire76efdea48d97ddd5e412eb932aafadf4($fileIdentifier, $file)
{
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        require $file;
        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = \true;
    }
}
