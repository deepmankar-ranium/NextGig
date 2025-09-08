<?php

namespace App\Services;

use App\Models\Tag;

class TagService
{
    public function getAllTags()
    {
        return Tag::all();
    }
}