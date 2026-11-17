<?php

namespace Tests\Feature\Security;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class TagOwnershipTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    public function test_created_tag_belongs_to_its_creator(): void
    {
        $employer = $this->employerUser();

        $this->actingAs($employer)->post('/create-tags', ['name' => 'remote'])->assertRedirect('/tags');

        $this->assertDatabaseHas('tags', ['name' => 'remote', 'created_by' => $employer->id]);
    }

    public function test_job_seeker_cannot_create_or_delete_tags(): void
    {
        $tag = Tag::create(['name' => 'remote']);
        $seeker = $this->seekerUser();

        $this->actingAs($seeker)->post('/create-tags', ['name' => 'onsite'])->assertForbidden();
        $this->actingAs($seeker)->post('/delete-tags', ['tags' => [$tag->id]])->assertForbidden();

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    public function test_employer_can_delete_own_tag_used_only_by_own_jobs(): void
    {
        $employer = $this->employerUser();
        $tag = Tag::create(['name' => 'remote', 'created_by' => $employer->id]);
        $this->jobFor($employer)->tags()->attach($tag);

        $this->actingAs($employer)->post('/delete-tags', ['tags' => [$tag->id]])->assertRedirect('/tags');

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_employer_cannot_delete_another_employers_tag(): void
    {
        $owner = $this->employerUser();
        $tag = Tag::create(['name' => 'remote', 'created_by' => $owner->id]);

        $this->actingAs($this->employerUser())
            ->post('/delete-tags', ['tags' => [$tag->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    public function test_employer_cannot_delete_own_tag_used_by_another_employers_job(): void
    {
        $owner = $this->employerUser();
        $tag = Tag::create(['name' => 'remote', 'created_by' => $owner->id]);
        $this->jobFor($this->employerUser())->tags()->attach($tag);

        $this->actingAs($owner)
            ->post('/delete-tags', ['tags' => [$tag->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    public function test_tags_without_an_owner_cannot_be_deleted_by_employers(): void
    {
        $tag = Tag::create(['name' => 'legacy']);

        $this->actingAs($this->employerUser())
            ->post('/delete-tags', ['tags' => [$tag->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    public function test_one_forbidden_tag_blocks_the_whole_batch(): void
    {
        $employer = $this->employerUser();
        $mine = Tag::create(['name' => 'mine', 'created_by' => $employer->id]);
        $theirs = Tag::create(['name' => 'theirs', 'created_by' => $this->employerUser()->id]);

        $this->actingAs($employer)
            ->post('/delete-tags', ['tags' => [$mine->id, $theirs->id]])
            ->assertForbidden();

        $this->assertDatabaseCount('tags', 2);
    }
}
