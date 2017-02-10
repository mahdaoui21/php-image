<?php


//
// Oussama Elgoumri
// contact@sec4ar.com
//
// Fri Feb 10 11:42:41 WET 2017
//


namespace OussamaElgoumri;


class ImagePathTest extends TestCommon
{
    public function test_setBasePath()
    {
        global $base_path;
        $base_path = null;

        list($obj, $m) = $this->getMethod('setBasePath');
        $results = $m->invoke($obj, __DIR__);
        $this->assertFileExists($results);
    }

    public function test_sanitize()
    {
        list($obj, $m) = $this->getMethod('sanitize');
        $results = $m->invoke($obj, '/foo', '/bar//', 'baz');
        $this->assertEquals($results, '/foo/bar/baz');
    }

    public function test_createDirs()
    {
        // Test default:
        list($obj, $m) = $this->getMethod('createDirs');
        $results = $m->invoke($obj);
        $this->assertEquals($results, base_path(getenv('IMAGE_PUBLIC') . '/' . date('Y/m/d')));

        // Test custom:
        putenv('IMAGE_PUBLIC=tests/public/storage');
        putenv('IMAGE_DIRS=d/H');
        $results = $m->invoke($obj);
        $this->assertEquals($results, base_path('tests/public/storage/' . date('d/H')));
    }

    /**
     * @expectedException OussamaElgoumri\Exceptions\ImagePublicPathNotSetException
     */
    public function test_fail_createDirs()
    {
        list($obj, $m) = $this->getMethod('createDirs');
        putenv('IMAGE_PUBLIC=');
        $m->invoke($obj);
    }

    public function test_getFrom()
    {
        list($obj, $m) = $this->getMethod('getFrom');

        // Test existing file:
        $file = tempnam('', '');
        $results = $m->invoke($obj, $file);
        $this->assertEquals($file, $results);

        // Test remote file
        $results = $m->invoke($obj, $this->faker->imageUrl(1, 1));
        $this->assertFileExists($results);

        // Test uploaded file
        $_FILES = ['image'];
        $_FILES['image'] = [
            'tmp_name' => $file,
        ];
        $results = $m->invoke($obj, 'image');
        $this->assertEquals($results, $file);
    }

    public function test_copy()
    {
        $img = new ImagePath($this->faker->image('/tmp', 1, 1));
        $uuid = sha1_file($img->getPath()) . image_type_to_extension(exif_imagetype($img->getPath()));
        $img->copy($uuid);

        $this->assertTrue(strpos($img->getPath(), $img->getRelativePath()) > 0);
    }
}