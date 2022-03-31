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
            $post->modified_by->id,
            $post->modified_by->role_id,
            $post->modified_by->created_at,
            $post->modified_by->updated_at,
            $post->modified_by->deleted_at
        );

        return $post;
    }

    public function privacyUpdate(Request $request)
    {
        return $this->postUpdate(1, $request->get('usersId'), $request->input('content'));
    }

    public function pointsUpdate(Request $request)
    {
        return $this->postUpdate(2, $request->get('usersId'), $request->input('content'));
    }

    public function valuesUpdate(Request $request)
    {
        return $this->postUpdate(3, $request->get('usersId'), $request->input('content'));
    }

    public function postUpdate(Int $content_type, Int $modified_by=null, String $content=null)
    {
        if (empty($modified_by)) {
            return UtilResponse::errorResponse("unknown modifified by");
        }

        if (empty($content)) {
            return UtilResponse::errorResponse("empty content data");
        }

        if ($post = $this->postsRepository->savePost($content_type, $modified_by, $content)) {
            return UtilResponse::successResponse("success");
        } else {
            return UtilResponse::errorResponse("content update failed");
        }
    }
}
