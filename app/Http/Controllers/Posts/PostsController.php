<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Repositories\PostsRepository;
use App\Util\UtilResponse;
use App\Util\Validate;
use App\Crypto\Crypto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    private $postsRepository;

    public function __construct(PostsRepository $postsRepository)
    {
        $this->postsRepository = $postsRepository;
    }

    public function privacy()
    {
        $post = $this->filterFiels($this->getPost(1));  //隱私權

        return UtilResponse::successResponse("success", $post);
    }

    public function points()
    {
        $post = $this->filterFiels($this->getPost(2));  //紅利點數

        return UtilResponse::successResponse("success", $post);
    }

    public function values()
    {
        $post = $this->filterFiels($this->getPost(3));  //儲值金

        return UtilResponse::successResponse("success", $post);
    }

    private function getPost(Int $type=null)
    {
        return $this->postsRepository->getPosts($type);
    }

    private function filterFiels($post)
    {
        unset(
            $post->id,
            $post->post_type,
            $post->created_at,
            $post->deleted_at,
            $post->last_modify,
            $post->modifier->id,
            $post->modifier->role_id,
            $post->modifier->created_at,
            $post->modifier->updated_at,
            $post->modifier->deleted_at
        );

        return $post;
    }
}
