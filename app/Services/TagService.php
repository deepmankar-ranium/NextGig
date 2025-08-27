<?php
namespace App\Services;
use App\Models\Tag;

class TagService{
    public static function index () {
        return Tag::all();

    }
}