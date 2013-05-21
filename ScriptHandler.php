<?php

namespace laurin1\IISPermissionHandlerWindowsAuth;

class ScriptHandler
{

    /**
     * @param $event
     * @throws \RuntimeException
     */
    public static function fixPermissions($event)
    {

        // Only applicable to Windows
        if (!self::isWindows()) {
            return;
        }

        $options = self::getOptions($event);
        $directories = $options['iis-permission-fix-folders'];

        echo 'Setting file permissions on folders: ' . implode(", ", $directories) . "\n";

        foreach ($directories as $dir) {

            if (!is_dir($dir)) {
                throw new \RuntimeException(sprintf('"%s" is not a valid directory.', escapeshellarg($dir)));
            }

            echo 'Processing folder: ' . $dir . "\n";

            $commands =
                [
                    "Set Administrators to owner..." => self::getSetAdministratorsOwnerCommand($dir),
                    "Granting Users Read...." => self::getSetUsersCommand($dir),
                    "Granting IUSR Read...." => self::getSetIUSRCommand($dir)
                ];

            foreach ($commands as $text => $command) {

                echo $text . "\n";

                if (null == $output = shell_exec($command)) {
                    throw new \RuntimeException(sprintf(
                        'An error occurred when executing the "%s" command.',
                        escapeshellarg($command)
                    ));
                }

                if (isset($options['iis-permission-fix-debug'])) {
                    echo $output . "\n";
                }

            }

        }

        echo 'Set permissions on folders: ' . implode(", ", $directories) . "\n";
    }

    /**
     * @return bool True if windows, false otherwise
     */
    protected static function isWindows()
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }

    /**
     * @param object $event
     * @return array
     */
    protected static function getOptions($event)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $options = array_merge(
            array(
                'iis-permission-fix-folders' => array(
                    'app' . DIRECTORY_SEPARATOR . 'cache',
                    'app' . DIRECTORY_SEPARATOR . 'logs',
                    'vendor'
                )
            ),
            $event->getComposer()->getPackage()->getExtra()
        );
        /** @noinspection PhpUndefinedMethodInspection */
        $options['process-timeout'] = $event->getComposer()->getConfig()->get('process-timeout');

        return $options;
    }

    /**
     * Get command to run our permissions fixer
     *
     * @param string $dir
     * @return string
     */
    protected static function getSetAdministratorsOwnerCommand($dir)
    {
        return "icacls \"$dir\" /setowner administrators /t";
    }

    /**
     * Get command to run our permissions fixer
     *
     * @param string $dir
     * @return string
     */
    protected static function getSetUsersCommand($dir)
    {
        return "icacls \"$dir\" /grant:r Users:\"(OI)(CI)\"F /t /inheritance:e";
    }

    /**
     * Get command to run our permissions fixer
     *
     * @param string $dir
     * @return string
     */
    protected static function getSetIUSRCommand($dir)
    {
        return "icacls \"$dir\" /grant:r IUSR:\"(OI)(CI)\"F /t /inheritance:e";
    }

}
