<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageTest extends TestCase
{
    use CreateBaseData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_admin_can_upload_image(): void
    {
        $headers = $this->loginAsAdmin();

        $response = $this->withHeaders($headers)->postJson('/api/imagenes', [
            'image' => UploadedFile::fake()->image('foto.png'),
        ]);

        $response->assertStatus(200)->assertJsonStructure(['message', 'image']);
        Storage::disk('public')->assertExists('images/'.basename($response->json('image')));
    }

    public function test_uploaded_file_name_is_generated_by_server(): void
    {
        $headers = $this->loginAsAdmin();

        $response = $this->withHeaders($headers)->postJson('/api/imagenes', [
            'image' => UploadedFile::fake()->image('nombre-del-cliente.png'),
        ]);

        $response->assertStatus(200);
        $this->assertStringNotContainsString('nombre-del-cliente', $response->json('image'));
    }

    public function test_file_with_php_extension_is_rejected(): void
    {
        $headers = $this->loginAsAdmin();

        $this->withHeaders($headers)->postJson('/api/imagenes', [
            'image' => UploadedFile::fake()->image('shell.php'),
        ])->assertStatus(422);

        $this->assertEmpty(Storage::disk('public')->allFiles('images'));
    }

    public function test_normal_user_cannot_upload_image(): void
    {
        $headers = $this->loginAsUser();

        $this->withHeaders($headers)->postJson('/api/imagenes', [
            'image' => UploadedFile::fake()->image('foto.png'),
        ])->assertStatus(403);
    }

    public function test_upload_requires_authentication(): void
    {
        $this->postJson('/api/imagenes', [
            'image' => UploadedFile::fake()->image('foto.png'),
        ])->assertStatus(401);
    }

    public function test_svg_and_non_image_files_are_rejected(): void
    {
        $headers = $this->loginAsAdmin();

        $this->withHeaders($headers)->postJson('/api/imagenes', [
            'image' => UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        ])->assertStatus(422);

        $this->withHeaders($headers)->postJson('/api/imagenes', [
            'image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ])->assertStatus(422);
    }
}
