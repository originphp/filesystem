<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2020 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.originphp.com
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types = 1);
namespace Origin\Filesystem;

use Origin\Filesystem\Exception\NotFoundException;

class Folder
{
    /**
     * Creates a folder or folders recursively
     *
     * @param string $directory
     * @param array $options
     *  - recursive: default false. like -p creates all directories in one go
     *  - mode: default 0775. Folder permissions
     * @return boolean
     */
    public static function create(string $directory, array $options = []): bool
    {
        $options += ['recursive' => false,'mode' => 0775];
        defer($a, 'umask', umask(0));

        return @mkdir($directory, $options['mode'], $options['recursive']); # use@ No such file or directory
    }

    /**
     * Checks if a directory exists
     *
     * @param string $directory
     * @return boolean
     */
    public static function exists(string $directory): bool
    {
        return file_exists($directory) && is_dir($directory);
    }

    /**
     * Includes a list of files
     *
     * @param string $directory
     * @param array $options Options keys are
     *  - directories: default false, includes directories
     *  - recursive: default false. Recursively gets contents
     * @return array
     */
    public static function list(string $directory, array $options = []): array
    {
        $options += ['directories' => false,'recursive' => false];
        if (self::exists($directory)) {
            $results = [];
            $files = array_diff(scandir($directory), ['.', '..']);
            foreach ($files as $file) {
                if ($options['recursive'] && ! is_file($directory . DIRECTORY_SEPARATOR . $file)) {
                    $results = array_merge($results, static::list($directory . DIRECTORY_SEPARATOR . $file, $options));
                }
                if (! $options['directories'] && ! is_file($directory . DIRECTORY_SEPARATOR . $file)) {
                    continue;
                }
                $stats = stat($directory . DIRECTORY_SEPARATOR . $file);
                $results[] = new FileObject([
                    'name' => $file,
                    'directory' => $directory,
                    'timestamp' => $stats['mtime'],
                    'size' => $stats['size'],
                    'type' => is_dir($directory. DIRECTORY_SEPARATOR . $file) ? 'directory' : 'file',
                ], $directory . DIRECTORY_SEPARATOR . $file);
            }

            return $results;
        }
       
        throw new NotFoundException(sprintf('%s does not exist', $directory));
    }

    /**
     * Deletes a directory. For saftey reasons recrusive is disabled by default,
     * since this will delete files etc.
     *
     * @param string $directory
     * @param array $options These are the options can you use:
     *    - recursive: If set to true, it will delete all contents and sub folders
     * @return bool
     */
    public static function delete(string $directory, array $options = []): bool
    {
        $options += ['recursive' => false];
        if (self::exists($directory)) {
            if ($options['recursive']) {
                $files = array_diff(scandir($directory), ['.', '..']);
                foreach ($files as $filename) {
                    if (is_dir($directory . DIRECTORY_SEPARATOR . $filename)) {
                        self::delete($directory . DIRECTORY_SEPARATOR . $filename, $options);
                        continue;
                    }
                    @unlink($directory . DIRECTORY_SEPARATOR . $filename);
                }
            }

            return @rmdir($directory);
        }
        
        throw new NotFoundException(sprintf('%s does not exist', $directory));
    }

    /**
     * Renames a directory
     *
     * @param string $directory full patth e.g. /var/www/tmp/my_project
     * @param string $to  directory name. project_name
     * @return boolean
     */
    public static function rename(string $directory, string $to): bool
    {
        if (self::exists($directory)) {
            if (strpos($to, DIRECTORY_SEPARATOR) === false) {
                $to = pathinfo($directory, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . $to;
            }

            return @rename($directory, $to);
        }
        throw new NotFoundException(sprintf('%s could not be found', $directory));
    }

    /**
     * Moves a directory
     *
     * @param string $source /var/www/tmp/docs
     * @param string $destination /var/www/tmp/documents
     * @return bool
     */
    public static function move(string $source, string $destination): bool
    {
        if (self::exists($source)) {
            if (strpos($destination, DIRECTORY_SEPARATOR) === false) {
                $destination = pathinfo($source, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . $destination;
            }

            return @rename($source, $destination);
        }
        throw new NotFoundException(sprintf('%s could not be found', $source));
    }

    /**
     * Copies a directory
     *
     * @param string $source /var/www/tmp/my_project
     * @param string $destination project_name or /var/www/tmp/project_name
     * @param array $options The options array supports the following keys
     *   - recursive: default true. recursively copy the contents of folder
     * @return boolean
     */
    public static function copy(string $source, string $destination, array $options = []): bool
    {
        $options += ['recursive' => true];

        if (self::exists($source)) {
            if (strpos($destination, DIRECTORY_SEPARATOR) === false) {
                $destination = pathinfo($source, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . $destination;
            }

            @mkdir($destination);

            $files = array_diff(scandir($source), ['.', '..']);
            foreach ($files as $filename) {
                if ($options['recursive'] && is_dir($source . DIRECTORY_SEPARATOR . $filename)) {
                    self::copy($source . DIRECTORY_SEPARATOR . $filename, $destination . DIRECTORY_SEPARATOR . $filename, $options);
                    continue;
                }
                @copy($source . DIRECTORY_SEPARATOR . $filename, $destination . DIRECTORY_SEPARATOR . $filename);
            }

            return self::exists($destination);
        }
        throw new NotFoundException(sprintf('%s could not be found', $source));
    }

    /**
    * Gets or sets the permissions (mode) for a directory
    *
    * @param string $directory filename with full path
    * @return string
    */
    public static function mode(string $directory): string
    {
        if (self::exists($directory)) {
            return  (string) substr(sprintf('%o', fileperms($directory)), -4);
        }
        throw new NotFoundException(sprintf('%s could not be found', $directory));
    }

    /**
    * Alias for mode. Gets the mode for a file aka permissions
    *
    * @param string $directory
    * @return string|null
    */
    public static function perms(string $directory): ?string
    {
        return static::mode($directory);
    }

    /**
      * Gets the owner of directory
      *
      * @param string $directory filename with full path
      * @return string|null
      */
    public static function owner(string $directory): ?string
    {
        if (self::exists($directory)) {
            return posix_getpwuid(fileowner($directory))['name'];
        }
        throw new NotFoundException(sprintf('%s could not be found', $directory));
    }

    /**
     * Gets the group that the directory belongs to.
     *
     * @param string $directory filename with full path
     * @return string|null
     */
    public static function group(string $directory): ?string
    {
        if (self::exists($directory)) {
            return posix_getgrgid(filegroup($directory))['name'];
        }
        throw new NotFoundException(sprintf('%s could not be found', $directory));
    }

    /**
    * Changes the permissions of directory
    *
    * @param string $directory filename with full path
    * @param int $mode e.g 0755 (remember 0 infront)
     * @param array $options (recursive default false)
     *  - recursive: If set to true, it will delete all contents and sub folders
    * @return bool
    */
    public static function chmod(string $directory, int $mode, array $options = []): bool
    {
        $options += ['recursive' => false];

        if (self::exists($directory)) {
            if ($options['recursive']) {
                $files = array_diff(scandir($directory), ['.', '..']);
                foreach ($files as $filename) {
                    if (is_dir($directory . DIRECTORY_SEPARATOR . $filename)) {
                        self::chmod($directory . DIRECTORY_SEPARATOR . $filename, $mode, $options);
                        continue;
                    }
                    @chmod($directory . DIRECTORY_SEPARATOR . $filename, $mode);
                }
            }

            return @chmod($directory, $mode);
        }
        throw new NotFoundException(sprintf('%s could not be found', $directory));
    }

    /**
     * Changes the owner of the directory
     *
     * @param string $directory filename with full path
     * @param string $user  e.g. root, www-data
     * @param array $options Support options keys are
     *  - recursive: default false. If set to true, it will delete all contents and sub folders
     * @return bool
     */
    public static function chown(string $directory, string $user, array $options = []): bool
    {
        $options += ['recursive' => false];
        if (self::exists($directory)) {
            if ($options['recursive']) {
                $files = array_diff(scandir($directory), ['.', '..']);
                foreach ($files as $filename) {
                    if (is_dir($directory . DIRECTORY_SEPARATOR . $filename)) {
                        self::chown($directory . DIRECTORY_SEPARATOR . $filename, $user, $options);
                        continue;
                    }
                    @chown($directory . DIRECTORY_SEPARATOR . $filename, $user);
                }
            }

            return @chown($directory, $user);
        }
        throw new NotFoundException(sprintf('%s could not be found', $directory));
    }

    /**
    * Changes the group that the directory belongs to
    *
    * @param string $directory filename with full path
    * @param string $group  e.g. root, www-data
    * @param array $options (recursive default false)
    *  - recursive: If set to true, it will delete all contents and sub folders
    * @return bool
    */
    public static function chgrp(string $directory, string $group = null, array $options = []): bool
    {
        $options += ['recursive' => false];
        if (self::exists($directory)) {
            if ($options['recursive']) {
                $files = array_diff(scandir($directory), ['.', '..']);
                foreach ($files as $filename) {
                    if (is_dir($directory . DIRECTORY_SEPARATOR . $filename)) {
                        self::chgrp($directory . DIRECTORY_SEPARATOR . $filename, $group, $options);
                        continue;
                    }
                    @chgrp($directory . DIRECTORY_SEPARATOR . $filename, $group);
                }
            }

            return @chgrp($directory, $group);
        }
        throw new NotFoundException(sprintf('%s could not be found', $directory));
    }
}
