<?php

namespace App\Actions\Tag;

use App\Models\Tag;
use App\Models\User;

class CreateTagAction
{
    public function execute(array $data, User $creator): Tag
    {
        return Tag::create([...$data, 'created_by' => $creator->id]);
    }
}
