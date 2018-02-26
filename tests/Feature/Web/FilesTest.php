<?php

namespace Tests\Feature\Web;

use App\Enums\FileTypeEnum;
use App\File;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\HasUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FilesTest extends TestCase
{
    use HasUser;
    use WithFaker;
    use RefreshDatabase;

    /** @test */
    public function unauthenticatedUserCannotSeeFileCreatePage()
    {
        $this->get('/files/create')
            ->assertRedirect('/login');
    }

    /** @test */
    public function authenticatedUserCanSeeFileCreatePage()
    {
        $this->actingAs($this->user)
            ->get('/files/create')
            ->assertViewIs('file.create');
    }

    /** @test */
    public function authenticatedUserCanUploadStlFile()
    {
        Storage::fake('public');

        $fileName = $this->faker->userName . '.stl';

        $response = $this->actingAs($this->user)
            ->post('/files', [
                'file' => UploadedFile::fake()->create($fileName),
            ]);

        /** @var File $file */
        $file = File::query()
            ->where('uploader_id', $this->user->id)
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

        $fileName = $this->faker->userName . '.gcode';

        $response = $this->actingAs($this->user)
            ->post('/files', [
                'file' => UploadedFile::fake()->create($fileName),
            ]);

        /** @var File $file */
        $file = File::query()
            ->where('uploader_id', $this->user->id)
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

        $fileName = $this->faker->userName . '.STL';

        $response = $this->actingAs($this->user)
            ->post('/files', [
                'file' => UploadedFile::fake()->create($fileName),
            ]);

        /** @var File $file */
        $file = File::query()
            ->where('uploader_id', $this->user->id)
            ->where('name', $fileName)
            ->first();

        $this->assertNotNull($file);
        $response->assertRedirect("/jobs/create/file/{$file->id}");

        $this->assertEquals($file->type, FileTypeEnum::STL);
        $this->assertEquals($file->filesystem, 'public');
    }
}
