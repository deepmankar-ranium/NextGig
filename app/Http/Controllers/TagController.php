<?php 

namespace App\Http\Controllers;
use App\Models\Tag;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;

class TagController extends Controller
{
    public function index()
    {
       $isEmployer = Auth::user() && Auth::user()->role && Auth::user()->role->name === "Employer";
       if($isEmployer){
        $tags =Tag::all();
        return Inertia::render('Tags',[
            'tags' => $tags
        ]);
       } else {
           throw new UnauthorizedException('Only employers can access tags');
       }

       
    }
    public function store(){
        $data = request()->validate([
            'name' => 'required'
        ]);
        Tag::create($data);
        return redirect('/tags');
    }
    public function destroy(){
        $data = request()->validate([
            'tags' => 'required|array'
        ]);
        Tag::destroy($data['tags']);
        return redirect('/tags');
    }
} 