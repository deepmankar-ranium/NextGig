<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
    }

    public function test_user_can_change_their_name(): void
    {
        $user = $this->seekerUser();

        $this->actingAs($user)->post('/profile', ['full_name' => '  Ada Lovelace '])->assertRedirect();

        $this->assertSame('Ada Lovelace', $user->fresh()->full_name);
        $this->assertSame('Ada Lovelace', $user->jobseeker->fresh()->name);
    }

    public function test_photo_is_cropped_to_a_square_jpeg_on_the_uploads_disk(): void
    {
        $user = $this->employerUser();

        $this->actingAs($user)->post('/profile', [
            'full_name' => $user->full_name,
            'photo' => UploadedFile::fake()->image('me.png', 800, 500),
        ])->assertSessionHasNoErrors();

        $path = $user->fresh()->photo_path;
        Storage::disk('uploads')->assertExists($path);
        $this->assertStringStartsWith('avatars/', $path);

        [$w, $h, $type] = getimagesizefromstring(Storage::disk('uploads')->get($path));
        $this->assertSame([320, 320, IMAGETYPE_JPEG], [$w, $h, $type]);
    }

    public function test_replacing_and_removing_a_photo_deletes_the_old_file(): void
    {
        $user = $this->seekerUser();
        $this->actingAs($user)->post('/profile', ['full_name' => 'A B', 'photo' => UploadedFile::fake()->image('a.jpg', 200, 200)]);
        $first = $user->fresh()->photo_path;

        $this->actingAs($user)->post('/profile', ['full_name' => 'A B', 'photo' => UploadedFile::fake()->image('b.jpg', 200, 200)]);
        $second = $user->fresh()->photo_path;
        $this->assertNotSame($first, $second);
        Storage::disk('uploads')->assertMissing($first);

        $this->actingAs($user)->post('/profile', ['full_name' => 'A B', 'remove_photo' => true]);
        $this->assertNull($user->fresh()->photo_path);
        Storage::disk('uploads')->assertMissing($second);
    }

    public function test_rejects_non_images_and_blank_names(): void
    {
        $user = $this->seekerUser();

        $this->actingAs($user)->post('/profile', [
            'full_name' => '',
            'photo' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors(['full_name', 'photo']);

        $this->assertEmpty(Storage::disk('uploads')->allFiles());
    }

    public function test_photo_is_served_to_signed_in_users_only_and_path_is_hidden(): void
    {
        $owner = $this->seekerUser();
        $this->actingAs($owner)->post('/profile', ['full_name' => 'A B', 'photo' => UploadedFile::fake()->image('a.jpg', 100, 100)]);
        $owner->refresh();

        $this->assertArrayNotHasKey('photo_path', $owner->toArray());
        $this->assertNotNull($owner->photo_url);

        $this->actingAs($this->employerUser())->get($owner->photo_url)
            ->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        auth()->logout();
        $this->get($owner->photo_url)->assertRedirect('/login');
    }

    public function test_photo_route_404s_without_a_photo(): void
    {
        $user = $this->seekerUser();
        $this->actingAs($user)->get("/users/{$user->id}/photo")->assertNotFound();
    }
}
