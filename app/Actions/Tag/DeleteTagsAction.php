<?php

namespace App\Actions\Tag;

use App\Models\Tag;

class DeleteTagsAction
{
    public function execute(array $tagIds): void
    {
        Tag::destroy($tagIds);
    }
}
