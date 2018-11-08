<?php

/**
 * Description of App1\Commands\Composer\Installer
 *
 * @link https://getcomposer.org/doc/articles/scripts.md
 */

namespace App1\Commands\Composer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Installer
{

    private static $setup;

    /**
     * preInstall
     *
     * provides access to the current ComposerIOConsoleIO
     * stream for terminal input/output
     * ok, continue on to composer install
     * exit composer and terminate installation process
     *
     * @param \Composer\Script\Event $event
     * @return boolean
     */
    public static function preInstall(Event $event)
    {
        //$io = $event->getIO();
        self::$setup = [];
        if (self::getIo($event)->askConfirmation("Are you sure you want to proceed ? (Y/N) ", false)) {
            if (self::getIo($event)->askConfirmation("Want to setup ?  (Y/N) ", false)) {
                self::setupDbPool($event);
                return true;
            }
            return true;
        }

        exit;
    }

    /**
     * postInstall
     *
     * provides access to the current Composer instance
     * run any post install tasks here
     *
     * @param Composer\Script\Event $event
     */
    public static function postInstall(Event $event)
    {
        $composer = $event->getComposer();
    }

    /**
     * postPackageInstall
     *
     * any tasks to run after the package is installed?
     *
     * @param Composer\Installer\PackageEvent $event
     */
    public static function postPackageInstall(PackageEvent $event)
    {
        $installedPackage = $event->getComposer()->getPackage();
    }

    /**
     * getIo
     *
     * @param Event $event
     * @return type
     */
    private static function getIo(Event $event)
    {
        return $event->getIO();
    }

    private static function setupDbPool(Event $event)
    {
        $poolParams = ['adapter', 'name', 'host', 'user', 'port', 'password'];
        $pdoAdaptersName = ['PdoMysql', 'PdoPgsql'];
        $mysqlSchemaDbName = 'information_schema';
        self::$setup['dbPool'] = [
            'db0' => [
                'adapter' => 'PdoMysql',
                'name' => 'information_schema',
                'host' => '127.0.0.1',
                'user' => 'pierre',
                'port' => '3306',
                'password' => 'pierre'
            ],
            'db1' => [
                'adapter' => 'PdoMysql',
                'name' => 'pimapp',
                'host' => '127.0.0.1',
                'user' => 'pierre',
                'port' => '3306',
                'password' => 'pierre'
            ],
            'db2' => [
                'adapter' => 'Pdopgsql',
                'name' => 'rdmax',
                'host' => '127.0.0.1',
                'user' => 'pierre',
                'port' => '5432',
                'password' => 'pierre'
            ]];
        $io = $event->getIO();

        $io->write('Create admin account');
        flush(); // Enforce order of messages
        $email = $io->ask('- E-Mail: ');
        $passwd = $io->askAndHideAnswer('- Password: ');

        $dbConfigQuestions = array_keys(self::$setup['dbPool']['db0']);
        foreach ($dbConfigQuestions as $key) {
            $config[$key] = $io->ask('- ' . $key . ' (' . $config[$key] . '): ', $config[$key]);
        }
        $config['DB_PASSWORD'] = $io->askAndHideAnswer('- DB_PASSWORD: ', $config['DB_PASSWORD']);

        if (self::getIo($event)->ask("Admin login : ", 'admin')) {
            if (self::getIo($event)->askAndHideAnswer("Admin password : ")) {
                if (self::getIo($event)->ask("Db name : ", 'pimapp')) {
                    if (self::getIo($event)->ask("Db login : ", 'root')) {
                        if (self::getIo($event)->askAndHideAnswer("Db password : ")) {
                            return true;
                        }
                        return true;
                    }
                    return true;
                }
                return true;
            }
            return true;
        }
    }
}
