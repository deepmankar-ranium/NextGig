<?php

namespace App\Services;

use App\Models\Tag;

class TagService
{
    public function getAllTags()
    {
        return Tag::all();
    }

    public function createTag(array $data): Tag
    {
        return Tag::create($data);
    }

    public function deleteTags(array $tagIds): void
    {
        Tag::destroy($tagIds);
    }
}
