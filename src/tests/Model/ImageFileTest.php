<?php

namespace Tests\Model;

use App\ImageFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ImageFileTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @test
     */
    public function image_file_can_be_findorcreated()
    {
        $imageFile1 = factory(ImageFile::class)->create([
            'path' => 'events/myimagefile.jpeg'
        ]);

        $this->assertCount(1, ImageFile::all());

        $imageFile2 = ImageFile::findOrCreate([
            'path' => 'events/myimagefile.jpeg'
        ]);

        $this->assertCount(1, ImageFile::all());
        $this->assertEquals($imageFile1->id, $imageFile2->id);

        $imageFile3 = ImageFile::findOrCreate([
            'file_name' => 'eufy101.20210315_105649119.jpg',
            'path' => 'events/myimagefile.foo.jpeg',
            'width' => 1920,
            'height' => 1080
        ]);

        $this->assertCount(2, ImageFile::all());
    }
}
