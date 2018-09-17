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

    /**
     * preInstall
     *
     * provides access to the current ComposerIOConsoleIO
     * stream for terminal input/output
     * ok, continue on to composer install
     * exit composer and terminate installation process
     *
     * @param Composer\Script\Event $event
     * @return boolean
     */
    public static function preInstall(Event $event)
    {
        $io = $event->getIO();
        if ($io->askConfirmation("Are you sure you want to proceed? ", false)) {
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
}
