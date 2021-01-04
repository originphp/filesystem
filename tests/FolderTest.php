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

namespace Origin\Test\Utility;

use Origin\Filesystem\File;
use Origin\Filesystem\Folder;
use Origin\Filesystem\Exception\NotFoundException;

class FolderTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid();
        $this->assertTrue(Folder::create($tmp));
        $this->assertFalse(Folder::create($tmp . '/depth1/depth2/depth3'));
        $this->assertTrue(Folder::create($tmp  . '/depth1/depth2/depth3', ['recursive' => true]));
    }

    public function testExists()
    {
        $this->assertTrue(Folder::exists(__DIR__));
        $this->assertFalse(Folder::exists('/foo'));
    }

    public function testList()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid();
        $this->assertTrue(Folder::create($tmp  . '/depth1/depth2/depth3', ['recursive' => true]));
        file_put_contents($tmp  . '/depth1/depth2/foo.txt', 'bar');

        $result = Folder::list($tmp . '/depth1/depth2');

        $this->assertEquals('foo.txt', $result[0]['name']);
        $this->assertEquals('file', $result[0]['type']);

        $result = Folder::list($tmp  . '/depth1/depth2', ['directories' => true]);
        
        $this->assertEquals('depth3', $result[0]['name']);
        $this->assertEquals('directory', $result[0]['type']);

        $this->assertEquals('foo.txt', $result[1]['name']);
        $this->assertEquals('file', $result[1]['type']);
        $this->assertEquals('txt', $result[1]['extension']);
        $this->assertEquals($tmp  . '/depth1/depth2/foo.txt', $result[1]['path']);

        // check that /tmp/5d9b15f4f26f5/depth1/depth2/foo.txt can be found using recursive
        $result = Folder::list($tmp, ['recursive' => true]);

        $this->assertEquals('foo.txt', $result[0]['name']);
    }

    public function testListException()
    {
        $this->expectException(NotFoundException::class);
        Folder::list('/foo');
    }

    /**
     * @depends testCreate
     *
     * @return void
     */
    public function testDelete()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid();
        $this->assertTrue(Folder::create($tmp  . '/depth1/depth2/depth3', ['recursive' => true]));
        file_put_contents($tmp  . '/depth1/depth2/foo.txt', 'bar');
        $this->assertFalse(Folder::delete($tmp  . '/depth1/depth2'));
        $this->assertTrue(Folder::delete($tmp  . '/depth1/depth2', ['recursive' => true]));
        $this->assertFalse(Folder::exists($tmp  . '/depth1/depth2'));
    }

    public function testDeleteException()
    {
        $this->expectException(NotFoundException::class);
        Folder::delete('/foo');
    }

    /**
     * @depends testCreate
     *
     */
    public function testCopy()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid() . '/';
        $this->assertTrue(Folder::create($tmp . 'docs/archive', ['recursive' => true]));
        file_put_contents($tmp . 'docs/file1.txt', 'foo');
        file_put_contents($tmp . 'docs/archive/file2.txt', 'foo');
        $this->assertTrue(Folder::copy($tmp . 'docs', 'docs2'));
    
        $this->assertTrue(file_exists($tmp . 'docs2/file1.txt'));
        $this->assertTrue(file_exists($tmp . 'docs2/archive/file2.txt'));
    }

    public function testCopyException()
    {
        $this->expectException(NotFoundException::class);
        Folder::copy('/foo', 'bar');
    }

    public function testRename()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid() . '/';
        $this->assertTrue(Folder::create($tmp . 'docs/archive', ['recursive' => true]));
        $this->assertTrue(Folder::rename($tmp . 'docs', 'docs-again'));
        $this->assertFalse(Folder::exists($tmp . 'docs'));
        $this->assertTrue(Folder::exists($tmp . 'docs-again'));
    }

    public function testRenameException()
    {
        $this->expectException(NotFoundException::class);
        Folder::rename('/foo', 'bar');
    }

    public function testMove()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid() . '/';
        $this->assertTrue(Folder::create($tmp . 'docs/archive', ['recursive' => true]));
        $this->assertTrue(Folder::move($tmp . 'docs', 'docs-again'));
        $this->assertFalse(Folder::exists($tmp . 'docs'));
        $this->assertTrue(Folder::exists($tmp . 'docs-again'));
    }

    public function testMoveException()
    {
        $this->expectException(NotFoundException::class);
        Folder::move('/foo', 'bar');
    }

    public function testPerms()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid() . '/';
        Folder::create($tmp, ['recursive' => false,'mode' => 0644]);

        $this->assertEquals('0644', Folder::perms($tmp));

        $this->assertTrue(Folder::chmod($tmp, 0775));
        clearstatcache(); // stat stuff is cached, so for next assert to work clear cache
        $this->assertEquals('0775', Folder::perms($tmp));
    }

    /**
     * Use 0644 , since 0664.
     *
     */
    public function testPermsRecursive()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid() . '/';
        Folder::create($tmp . 'docs/archive', ['recursive' => true]);
        $this->assertTrue((bool) file_put_contents($tmp . 'docs/archive/test.txt', 'foo'));
        $this->assertTrue(chmod($tmp . 'docs/archive/test.txt', 0644));
        clearstatcache(); // stat stuff is cached, so for next assert to work clear cache
        $this->assertEquals('0775', Folder::perms($tmp . 'docs'));
        $this->assertEquals('0775', Folder::perms($tmp . 'docs/archive'));
        $this->assertEquals('0644', File::perms($tmp . 'docs/archive/test.txt'));
        $this->assertTrue(Folder::chmod($tmp . 'docs', 0777, ['recursive' => true]));

        clearstatcache(); // stat stuff is cached, so for next assert to work clear cache
        $this->assertEquals('0777', Folder::perms($tmp . 'docs'));
        $this->assertEquals('0777', Folder::perms($tmp . 'docs/archive'));
        $this->assertEquals('0777', File::perms($tmp . 'docs/archive/test.txt'));
    }

    public function testPermsException()
    {
        $this->expectException(NotFoundException::class);
        Folder::perms('/foo');
    }

    public function testOwner()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid() . '/';
        Folder::create($tmp);

        $owner = Folder::owner($tmp);
        $this->assertRegExp('/^[a-z0-9]+$/i', $owner);
        $this->assertTrue(Folder::chown($tmp, $owner));
    }

    public function testOwnerRecursive()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid() . '/';
        Folder::create($tmp . 'docs/archive', ['recursive' => true]);
        $this->assertTrue((bool) file_put_contents($tmp . 'docs/archive/test.txt', 'foo'));
        
        $owner = Folder::owner($tmp . 'docs');

        $this->assertTrue(Folder::chown($tmp . 'docs', $owner, ['recursive' => true]));

        /**
         * This is the old testing, for docker/linux. It is detailed as each step
         * can be verified. Testing on different system does not allow. I am leaving here
         * for now, until a better way to verify can be done
         */
        if ($owner === 'root') {
            $this->assertEquals('root', Folder::owner($tmp . 'docs'));
            $this->assertEquals('root', Folder::owner($tmp . 'docs/archive'));
            $this->assertEquals('root', File::owner($tmp . 'docs/archive/test.txt'));
            $this->assertTrue(Folder::chown($tmp . 'docs', 'www-data', ['recursive' => true]));
    
            clearstatcache(); // stat stuff is cached, so for next assert to work clear cache
            $this->assertEquals('www-data', Folder::owner($tmp . 'docs'));
            $this->assertEquals('www-data', Folder::owner($tmp . 'docs/archive'));
            $this->assertEquals('www-data', File::owner($tmp . 'docs/archive/test.txt'));
        }
    }

    public function testOwnerException()
    {
        $this->expectException(NotFoundException::class);
        Folder::owner('/foo');
    }

    public function testGroup()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid() . '/';
        Folder::create($tmp);

        $group = Folder::group($tmp);
        $this->assertRegExp('/^[a-z0-9]+$/i', $group);
        $this->assertTrue(Folder::chgrp($tmp, $group));
    }

    public function testGroupRecursive()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid() . '/';
        Folder::create($tmp . 'docs/archive', ['recursive' => true]);
        $this->assertTrue((bool) file_put_contents($tmp . 'docs/archive/test.txt', 'foo'));
        
        $group = Folder::group($tmp . 'docs');
        $this->assertTrue(Folder::chgrp($tmp . 'docs', $group, ['recursive' => true]));
        
        /**
         * This is the old testing, for docker/linux. It is detailed as each step
         * can be verified. Testing on different system does not allow. I am leaving here
         * for now, until a better way to verify can be done
         */
        if ($group === 'root') {
            $this->assertEquals('root', Folder::group($tmp . 'docs'));
            $this->assertEquals('root', Folder::group($tmp . 'docs/archive'));
            $this->assertEquals('root', File::group($tmp . 'docs/archive/test.txt'));
            $this->assertTrue(Folder::chgrp($tmp . 'docs', 'www-data', ['recursive' => true]));
    
            clearstatcache(); // stat stuff is cached, so for next assert to work clear cache
            $this->assertEquals('www-data', Folder::group($tmp . 'docs'));
            $this->assertEquals('www-data', Folder::group($tmp . 'docs/archive'));
            $this->assertEquals('www-data', File::group($tmp . 'docs/archive/test.txt'));
        }
    }
    
    public function testGroupException()
    {
        $this->expectException(NotFoundException::class);
        Folder::group('/foo');
    }

    public function testChmodException()
    {
        $this->expectException(NotFoundException::class);
        Folder::chmod('/foo', 0775);
    }

    public function testChownException()
    {
        $this->expectException(NotFoundException::class);
        Folder::chown('/foo', 'some-user');
    }

    public function testChgrpException()
    {
        $this->expectException(NotFoundException::class);
        Folder::chgrp('/foo', 'some-group');
    }
}
