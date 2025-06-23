<?php
namespace App\Actions;
use App\Models\Tag;

class CreateTags {
    public function store ($validatedData){
    $tag = Tag::create([
        'name' => $validatedData['name'],
    ]);
    return $tag;

    }

    public function destroy($validatedData){

        Tag::destroy($validatedData['tags']);

        return redirect('/tags');
    }

}