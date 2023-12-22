<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PostDescription;
use Auth;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $posts = Post::with('postDescription')->get();
            return response()->json(array('status' => TRUE, 'data' => $posts));   
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSinglePostWithLanguage(Request $request)
    {
        $request->validate([
            'post_id' => 'required',
            'language' => 'required',
        ]);
        
        try {
            $postDescription = PostDescription::wherePostId($request->post_id)->first();
            if(!empty($postDescription)){
                $language = $request->language;
                if($postDescription->$language){
                    $posts = Post::with('postDescription')->findOrFail($request->post_id);
                    return response()->json(array('status' => TRUE, 'data' => array('post' => $posts))); 
                } else{
                    return response()->json(array('status' => FALSE, 'message' => 'Data not available in this language.')); 
                }
            } else{
                return response()->json(array('status' => FALSE, 'message' => 'Data not available.')); 
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'eng' => 'required_without_all:eng,hindi',
            'hindi' => 'required_without_all:eng,hindi',
        ]);
        
        try {
            $input = $request->all();
            $input['user_id'] = Auth::user()->id;
            $post = Post::create($input); 
            $postDescription = PostDescription::create([
                "post_id" => $post->id,
                "eng" => isset($input['eng']) ? $input['eng'] : NULL,
                "hindi" => isset($input['hindi']) ? $input['hindi'] : NULL,
            ]);
            return response()->json(array('status' => TRUE, 'data' => array('post' => $post, 'postDescription' => $postDescription)));   
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required',
            'eng' => 'required_without_all:eng,hindi',
            'hindi' => 'required_without_all:eng,hindi',
        ]);

        try {
            $postId = $post->id;
            $input = $request->all();
            $input['user_id'] = Auth::user()->id;
            $post = $post->update($input);
            $postDescription = PostDescription::where('post_id', $postId)->update([
                "eng" => isset($input['eng']) ? $input['eng'] : NULL,
                "hindi" => isset($input['hindi']) ? $input['hindi'] : NULL,
            ]);
            
            return response()->json(array('status' => TRUE, 'message' => 'Post update successfully.', 'data' => array('post' => $post, 'postDescription' => $postDescription)));   
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json(array('status' => TRUE, 'message' => 'Post delete successfully'));
    }
}
