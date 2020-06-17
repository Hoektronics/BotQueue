<?php

namespace Tests\Feature\Web;

use App\Enums\FileTypeEnum;
use App\File;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FilesTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function unauthenticatedUserCannotSeeFileCreatePage()
    {
        $this
            ->withExceptionHandling()
            ->get('/files/create')
            ->assertRedirect('/login');
    }

    /** @test */
    public function authenticatedUserCanSeeFileCreatePage()
    {
        $this
            ->actingAs($this->mainUser)
            ->get('/files/create')
            ->assertViewIs('file.create');
    }

    /** @test */
    public function authenticatedUserCanUploadStlFile()
    {
        Storage::fake('public');

        $fileName = $this->faker->userName.'.stl';

        $response = $this
            ->actingAs($this->mainUser)
            ->post('/files', [
                'file' => UploadedFile::fake()->create($fileName),
            ]);

        /** @var File $file */
        $file = File::query()
            ->where('uploader_id', $this->mainUser->id)
            ->where('name', $fileName)
            ->first();

        $this->assertNotNull($file);
        $response->assertRedirect("/jobs/create/file/{$file->id}");

        $this->assertEquals($file->type, FileTypeEnum::STL);
        $this->assertEquals($file->filesystem, 'public');
    }

    /** @test */
    public function authenticatedUserCanUploadGcodeFile()
    {
        Storage::fake('public');

        $fileName = $this->faker->userName.'.gcode';

        $response = $this
            ->actingAs($this->mainUser)
            ->post('/files', [
                'file' => UploadedFile::fake()->create($fileName),
            ]);

        /** @var File $file */
        $file = File::query()
            ->where('uploader_id', $this->mainUser->id)
            ->where('name', $fileName)
            ->first();

        $this->assertNotNull($file);
        $response->assertRedirect("/jobs/create/file/{$file->id}");

        $this->assertEquals($file->type, FileTypeEnum::GCODE);
        $this->assertEquals($file->filesystem, 'public');
    }

    /** @test */
    public function extensionCaseGetsSetAsLowerCaseOnUpload()
    {
        Storage::fake('public');

        $fileName = $this->faker->userName.'.STL';

        $response = $this
            ->actingAs($this->mainUser)
            ->post('/files', [
                'file' => UploadedFile::fake()->create($fileName),
            ]);

        /** @var File $file */
        $file = File::query()
            ->where('uploader_id', $this->mainUser->id)
            ->where('name', $fileName)
            ->first();

        $this->assertNotNull($file);
        $response->assertRedirect("/jobs/create/file/{$file->id}");

        $this->assertEquals($file->type, FileTypeEnum::STL);
        $this->assertEquals($file->filesystem, 'public');
    }
}
