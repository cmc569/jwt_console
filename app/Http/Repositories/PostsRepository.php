<?php

namespace App\Http\Repositories;

use App\Http\Models\Posts;
use DB;

class PostsRepository extends BaseRepository
{
    public function getPosts(Int $type=null)
    {
        $posts = Posts::with('modified_by');
        if (is_null($type)) {
            return $posts->get();
        }

        return $posts->where('post_type', $type)->first();
    }

    public function savePost(Int $content_type, Int $modified_by, String $content)
    {
        return Posts::updateOrCreate(
            ['post_type' => $content_type],
            ['last_modify' => $modified_by, 'content' => $content]
        );
    }
}
