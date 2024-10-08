<?php

return '<?php

/**
 * Quantum PHP Framework
 *
 * An open source software development framework for PHP
 *
 * @package Quantum
 * @author Arman Ag. <arman.ag@softberg.org>
 * @copyright Copyright (c) 2018 Softberg LLC (https://softberg.org)
 * @link http://quantum.softberg.org/
 * @since 2.9.0
 */

namespace Modules\Api\Controllers;

use Quantum\Factory\ServiceFactory;
use Shared\Services\PostService;
use Quantum\Http\Response;
use Quantum\Http\Request;

/**
 * Class PostController
 * @package Modules\Api
 */
class PostController extends BaseController
{

    /**
     * Post service
     * @var PostService
     */
    public $postService;

    /**
     * Works before an action
     */
    public function __before()
    {
        $this->postService = ServiceFactory::get(PostService::class);
    }

    /**
     * Action - get posts list
     * @param Request $request 
     * @param Response $response
     */
    public function posts(Request $request, Response $response)
    {
        $posts = $this->postService->getPosts($request);
        $response->json([
            \'status\' => \'success\',
            \'data\' => $posts->data,
            \'pagination\' => [
                \'total_records\' => $posts->total(),
                \'current_page\' => $posts->currentPageNumber(),
                \'total_pages\' => (int) ceil($posts->total()/$posts->perPage()),
                \'next_page\' => $posts->nextPageNumber(),
                \'prev_page\' => $posts->previousPageNumber(),
            ]
        ]);
    }

    /**
     * Action - get single post
     * @param Response $response
     * @param string|null $lang
     * @param string $postId
     */
    public function post(Response $response, ?string $lang, string $postId)
    {
        $response->json([
            \'status\' => \'success\',
            \'data\' => $this->postService->getPost($postId)
        ]);
    }

    /**
     * Action - get my posts
     * @param Response $response
     */
    public function myPosts(Response $response)
    {
        $response->json([
            \'status\' => \'success\',
            \'data\' => $this->postService->getMyPosts((int)auth()->user()->id)
        ]);
    }

    /**
     * Action - create post
     * @param Request $request 
     * @param Response $response
     */
    public function create(Request $request, Response $response)
    {
        $postData = [
            \'user_id\' => (int)auth()->user()->id,
            \'title\' => $request->get(\'title\', null, true),
            \'content\' => $request->get(\'content\', null, true),
            \'image\' => \'\',
            \'updated_at\' => date(\'Y-m-d H:i:s\'),
        ];

        if ($request->hasFile(\'image\')) {
            $imageName = $this->postService->saveImage(
                $request->getFile(\'image\'),
                auth()->user()->uuid,
                slugify($request->get(\'title\'))
            );

            $postData[\'image\'] = $imageName;
        }

        $this->postService->addPost($postData);

        $response->json([
            \'status\' => \'success\',
            \'message\' => t(\'common.created_successfully\')
        ]);
    }

    /**
     * Action - amend post 
     * @param Request $request 
     * @param Response $response
     * @param string|null $lang
     * @param string $postId
     */
    public function amend(Request $request, Response $response, ?string $lang, string $postId)
    {
        $postData = [
            \'title\' => $request->get(\'title\', null, true),
            \'content\' => $request->get(\'content\', null, true),
            \'updated_at\' => date(\'Y-m-d H:i:s\'),
        ];

        $post = $this->postService->getPost($postId, false);

        if ($request->hasFile(\'image\')) {
            if ($post[\'image\']) {
                $this->postService->deleteImage(auth()->user()->uuid . DS . $post[\'image\']);
            }

            $imageName = $this->postService->saveImage(
                $request->getFile(\'image\'),
                auth()->user()->uuid,
                slugify($request->get(\'title\'))
            );

            $postData[\'image\'] = $imageName;
        }

        $this->postService->updatePost($postId, $postData);

        $response->json([
            \'status\' => \'success\',
            \'message\' => t(\'common.updated_successfully\')
        ]);
    }

    /**
     * Action - delete post
     * @param Response $response
     * @param string|null $lang
     * @param string $postId
     */
    public function delete(Response $response, ?string $lang, string $postId)
    {
        $post = $this->postService->getPost($postId, false);

        if ($post[\'image\']) {
            $this->postService->deleteImage(auth()->user()->uuid . DS . $post[\'image\']);
        }

        $this->postService->deletePost($postId);

        $response->json([
            \'status\' => \'success\',
            \'message\' => t(\'common.deleted_successfully\')
        ]);
    }

    /**
     * Action - delete image of the post
     * @param Response $response
     * @param string|null $lang 
     * @param string $postId 
     */
    public function deleteImage(Response $response, ?string $lang, string $postId)
    {
        $post = $this->postService->getPost($postId, false);

        if ($post[\'image\']) {
            $this->postService->deleteImage(auth()->user()->uuid . DS . $post[\'image\']);
        }

        $this->postService->updatePost($postId, [
            \'title\' => $post[\'title\'],
            \'content\' => $post[\'content\'],
            \'image\' => \'\',
            \'updated_at\' => date(\'Y-m-d H:i:s\'),
        ]);

        $response->json([
            \'status\' => \'success\',
            \'message\' => t(\'common.deleted_successfully\')
        ]);
    }
}';