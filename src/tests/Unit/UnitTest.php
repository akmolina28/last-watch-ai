<?php

namespace Tests\Unit;

use App\DetectionEvent;
use App\DetectionProfile;
use App\ImageFile;
use App\SmbCifsCopyConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UnitTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function smb_cifs_copy_config_local_path()
    {
        $imageFile = factory(ImageFile::class)->create();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);

        $smbConfig = factory(SmbCifsCopyConfig::class)->create();

        $localPath = $smbConfig->getLocalPath($event);

        $this->assertEquals('/var/www/app/storage/app/public_testing/'.$imageFile->path, $localPath);
    }

    /**
     * @test
     */
    public function smb_cifs_copy_config_dest_path()
    {
        $imageFile = factory(ImageFile::class)->create();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);

        $profile = factory(DetectionProfile::class)->create([
            'name' => 'Test Profile',
        ]);

        $smbConfig = factory(SmbCifsCopyConfig::class)->create([
            'overwrite' => false,
        ]);

        $destPath = $smbConfig->getDestPath($event, $profile);

        $this->assertEquals($imageFile->file_name, $destPath);
    }

    /**
     * @test
     */
    public function smb_cifs_copy_config_dest_path_overwrite()
    {
        $imageFile = factory(ImageFile::class)->create();

        $event = factory(DetectionEvent::class)->create([
            'image_file_id' => $imageFile->id,
        ]);

        $profile = factory(DetectionProfile::class)->create([
            'name' => 'Test Profile',
        ]);

        $smbConfig = factory(SmbCifsCopyConfig::class)->create([
            'overwrite' => true,
        ]);

        $destPath = $smbConfig->getDestPath($event, $profile);

        $this->assertEquals($profile->slug.'.jpg', $destPath);
    }

    /**
     * @test
     */
    public function smb_cifs_copy_config_smbclient_cmd()
    {
        $smbConfig = factory(SmbCifsCopyConfig::class)->create([
            'servicename' => '//192.168.1.101/share',
            'user' => 'smith',
            'password' => 'secret',
            'remote_dest' => '/dest/folder',
            'overwrite' => true,
        ]);

        $localPath = '/local/path/my-image.jpg';
        $destPath = 'dest-path.jpg';

        $cmd = $smbConfig->getSmbclientCommand($localPath, $destPath);

        // @codingStandardsIgnoreLine
        $this->assertEquals('smbclient //192.168.1.101/share -U smith%secret -c \'cd "/dest/folder" ; put "/local/path/my-image.jpg" "dest-path.jpg"\'', $cmd);
    }
}
