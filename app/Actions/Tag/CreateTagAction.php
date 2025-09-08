<?php

namespace App\Actions\Tag;

use App\Models\Tag;

class CreateTagAction
{
    public function execute(array $data): Tag
    {
        return Tag::create($data);
    }
}
