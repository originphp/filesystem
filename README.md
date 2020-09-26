# Filesystem

![license](https://img.shields.io/badge/license-MIT-brightGreen.svg)
[![build](https://travis-ci.org/originphp/filesystem.svg?branch=master)](https://travis-ci.org/originphp/filesystem)
[![coverage](https://coveralls.io/repos/github/originphp/filesystem/badge.svg?branch=master)](https://coveralls.io/github/originphp/filesystem?branch=master)

Filesystem includes the `File` and `Folder` classes for working with your filesystem.

## Installation

To install this package

```linux
$ composer require originphp/filesystem
```


## File

he file utility wraps some important functions in an easy to use and predictable way.

To use the File utility add the following to the top of your file.

```php
use Origin\Filesystem\File
```

### Info

> As of 2.0 path was renamed to directory and filename was renamed to name

To get information about a file

```php
$info = File::info('/var/www/config/insurance.csv');
```

Which will return an array like this

```
Array
(
    [name] => insurance.csv
    [directory] => /var/www/config
    [timestamp] => 1560067334
    [size] => 1878
    [extension] => csv
    [type] => text/plain
)
```

### Read

To read a file

```php
$contents = File::read('/path/somefile');
```

### Write

To write to a file


```php
File::write('/path/somefile','data goes here');
```

### Append

To append contents to a file

```php
File::append('/path/somefile','and here.');
```

### Delete

To delete a file

```php
File::delete('/path/somefile');
```

### Exists

To check if file exists

```php
$result = File::exists('/path/somefile');
```

### Tmp

When needing to work with temporary files, use tmp, this will create the file, put the contents and return to you the name of the file with path.

```php
$tmpFile = File::tmp('Some temp data');
```

### Copy

To copy a file

```php
File::copy('/path/somefile','somefile-backup');
File::copy('/path/somefile','/another_path/somefile');
```

### Rename

To rename a file

```php
File::rename('/path/somefile','new_name');
```

### Move

To move a file

```php
File::move('/path/somefile','/another_path/somefile');
```

### Permissions

#### Get Permissions

To get the permissions of a file

```php
$permissions = File::perms('/path/somefile'); // returns 0744
```

#### Changing Permissions (chmod)

To change the permissions of a file.

```php
File::chmod('/path/somefile','www-data');
```

#### Getting the owner of the file

```php
$owner = File::owner('/path/somefile'); // returns root
```

#### Changing Ownership (chown)

To change the ownership of a file

```php
File::chown('/path/somefile','www-data');
```

#### Getting the group

To get the group that the file belongs to.

```php
$group = File::group('/path/somefile'); // returns root
```

#### Changing Group (chgrp)

To change the group that the file belongs to.

```php
File::chgrp('/path/somefile','www-data');
```

## Folder

The folder utility helps you work with folders on your file system.

To use the Folder utility add the following to the top of your file.

```php
use Origin\Filesystem\Folder
```

### Create

To create a folder

```php
Folder::create('/var/www/new_folder');
```

To create a folder recursively

```php
Folder::create('/var/www/level1/level2/level3/new_folder',['recursive'=>true]);
```

To set the permissions on the newly created folder

```php
Folder::create('/var/www/new_folder',['mode'=>0755]);
```

### Delete

To delete a folder

```php
Folder::delete('/var/www/bye-bye')
```

To delete a folder recursively, including all files and sub directories.

```php
Folder::delete('/var/www/docs',['recursive'=>true])
```

### Exists

To check if a directory exists

```php
$result = Folder::exists('/path/somedirectory');
```

### List

> As of 2.0 path was renamed to directory and each file is a FileObject

To list all contents of a directory

```php
$results = Folder::list('/path/somedirectory');
```

This will return an array of arrays of `FileObjects`

```php
[
    Origin\Filesystem\FileObject Object
    (
        'name' => 'foo.txt',
        'directory' => '/var/www/my_directory',
        'path' =>  '/var/www/my_directory/foo.txt',
        'extension' => 'txt',
        'timestamp' => 14324234,
        'size' => 1234,
        'type' => 'file'
    )
]
```

When the `FileObject` is converted to a string it will become a path e.g. `/var/www/my_directory/foo.txt`.

You can also get the listing recursively

```php
$results = Folder::list('/path/somedirectory',['recursive'=>true]);
```

To include directories in the results

```php
$results = Folder::list('/path/somedirectory',['directories'=>true]);
```

### Copy

To copy a directory

```php
Folder::copy('/path/somedir','somedir-backup');
Folder::copy('/path/somedir','/another_path/somedir');
```

### Rename

To rename a directory

```php
Folder::rename('/path/somedir','new_name');
```

### Move

To move a directory

```php
Folder::move('/path/somedir','/another_path/somedir');
```

### Permissions

#### Get Permissions

To get the permissions of a directory.

```php
$permissions = Folder::perms('/path/somedir'); // returns 0744
```

#### Changing Permissions (chmod)

To change the permissions of a directory

```php
Folder::chmod('/path/somedir','www-data');
Folder::chmod('/path/somedir','www-data',['recursive'=>true]); // recursive
```

#### Getting the owner of a directory

```php
$owner = Folder::owner('/path/somedir'); // returns root
```

#### Changing Ownership (chown)

To change the ownership of a directory

```php
Folder::chown('/path/somedir','www-data');
Folder::chown('/path/somedir','www-data',['recursive'=>true]);
```

#### Getting the group

To get the group that a directory belongs to.

```php
$group = Folder::group('/path/somedir'); // returns root
```

#### Changing Group (chgrp)

To change the group that the folder belongs to.

```php
Folder::chgrp('/path/somedir','www-data');
Folder::chgrp('/path/somedir','www-data',['recursive'=>true]);
```
