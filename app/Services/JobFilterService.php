<?php
// app/Services/JobFilterService.php

namespace App\Services;

use App\Models\JobListing;

class JobFilterService
{
    public function filterByTag(?string $tag = null, int $perPage = 6)
    {
        return JobListing::with(['employer', 'tags'])
            ->when($tag, function ($query) use ($tag) {
                $query->whereHas('tags', function ($q) use ($tag) {
                    $q->where('name', $tag);
                });
            })
            ->latest()
            ->paginate($perPage);
    }
}
