<?php

/**
 * OriginPHP Framework
 * Copyright 2018 - 2021 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.originphp.com
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Origin\Test\FileSystem;

use Origin\Filesystem\FileObject;

class FileObjectTest extends \PHPUnit\Framework\TestCase
{
    public function testAccess()
    {
        $data = ['name' => 'foo.txt','path' => 'folder/subfolder/foo.txt','directory' => 'folder/subfolder', 'size' => 32000, 'timestamp' => strtotime('2019-10-31 14:40')];
        $object = new FileObject($data);

        $this->assertEquals('foo.txt', $object['name']);
        $this->assertEquals('folder/subfolder', $object['directory']);

        $this->assertEquals('foo.txt', $object->name);
        $this->assertEquals('folder/subfolder', $object->directory);

        $this->assertTrue(isset($object['name']));
        $this->assertTrue(isset($object->name));

        $this->assertNull($object['abc']);
        $this->assertNull($object->abc);

        unset($object['name']);
        $object->name = 'bar.txt';
        $this->assertEquals('bar.txt', $object->name);

        unset($object->name);
        $object['name'] = 'bar.txt';
        $this->assertEquals('bar.txt', $object->name);

        // test offsetset
        $object[] = 'foo';
        $this->assertEquals('foo', $object[0]);

        // Need to call this to ensure no errors but cant test it.
        unset($object->furion);

        $this->assertEquals('folder/subfolder/foo.txt', (string)  $object);
    }
}
