<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyTagRequest;
use App\Http\Requests\StoreTagRequest;
use App\Services\TagService;
use Inertia\Inertia;
use Illuminate\Validation\UnauthorizedException;

class TagController extends Controller
{
    public function __construct(private TagService $tagService)
    {
    }

    public function index()
    {
        if (auth()->user()?->role->name !== 'Employer') {
            throw new UnauthorizedException('Only employers can access tags');
        }

        return Inertia::render('Tags', [
            'tags' => $this->tagService->getAllTags(),
        ]);
    }

    public function store(StoreTagRequest $request)
    {
        $this->tagService->createTag($request->validated());
        return redirect('/tags');
    }

    public function destroy(DestroyTagRequest $request)
    {
        $this->tagService->deleteTags($request->validated('tags'));
        return redirect('/tags');
    }
}