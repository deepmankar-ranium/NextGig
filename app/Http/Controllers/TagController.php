<?php 

namespace App\Http\Controllers;

use App\Http\Requests\TagRequest;
use App\Models\Tag;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use App\Services\TagService;
use App\Actions\CreateTags;
use App\Http\Requests\DeleteTagRequest;

class TagController extends Controller
{
    public function index()
    {
       $user = Auth::user();

       $isEmployer = $user->isEmployer();

       if($isEmployer){
        $tags = TagService::index();

        return Inertia::render('Tags',['tags' => $tags]);
        
       } else {
           throw new UnauthorizedException('Only employers can access tags');
       }

       
    }
    public function store(TagRequest $request, CreateTags $CreateTags){
        $data = $request->validated();
        $tag = $CreateTags->store($data);
        return redirect('/tags');
    }
    public function destroy(DeleteTagRequest $request, CreateTags $CreateTags){

        $data = $request->validated();
        $CreateTags->destroy($data);
        return redirect('/tags');
    }
} 