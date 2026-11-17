<?php

namespace App\Http\Controllers;

use App\Actions\Tag\CreateTagAction;
use App\Actions\Tag\DeleteTagsAction;
use App\Http\Requests\DestroyTagRequest;
use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    public function __construct(private TagService $tagService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Tag::class);

        $user = $request->user();

        return Inertia::render('Tags', [
            'tags' => $this->tagService->getAllTags()->map(fn (Tag $tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'can_delete' => $user->can('delete', $tag),
            ]),
        ]);
    }

    public function store(StoreTagRequest $request, CreateTagAction $action)
    {
        $action->execute($request->validated(), $request->user());

        return redirect('/tags');
    }

    public function destroy(DestroyTagRequest $request, DeleteTagsAction $action)
    {
        $action->execute($request->validated('tags'));

        return redirect('/tags');
    }
}
