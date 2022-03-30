<?php

namespace App\Http\Repositories;

use App\Http\Models\Posts;
use DB;

class PostsRepository extends BaseRepository
{
    public function getPosts(Int $type=null)
    {
        // $posts = Posts::with(['modifier' => function($query) {
        //     $query->select('name', 'account', 'email');
        // }]);
        $posts = Posts::with('modifier');
        if (is_null($type)) {
            return $posts->get();
        }

        return $posts->where('post_type', $type)->first();
    }

}
