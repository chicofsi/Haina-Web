<?php

namespace App\Http\Controllers\Api\Forum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Subforum;
use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumImage;
use App\Models\ForumVideo;
use App\Models\ForumMod;
use App\Models\ForumUpvote;

use App\Http\Controllers\Api\Notification\NotificationController;

use DateTime;

use App\Http\Resources\ValueMessage;

class ForumController extends Controller
{

    public function createSubforum (Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required' 
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{

            $check = Subforum::where('name', $request->name)->first();

            if($check){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Subforum already exists!','data'=> '']), 401);
            }
            else{
                $subforum = [
                    'name' => $request->name,
                    'description' => $request->description
                ];

                $new_subforum = Subforum::create($subforum);

                $new_mod = ForumMod::create([
                    'user_id' => Auth::id(),
                    'role' => 'mod',
                    'subforum_id' => $new_subforum->id
                ]);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Subforum successfully created!','data'=> $new_subforum]), 200);
            }
        }

    }

    public function showAllSubforum(){
        $check = Subforum::all();

        if(count($check) != 0){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Subforum found!','data'=> $check]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'No subforum found!','data'=> '']), 404);
        }
    }

    public function showAllPost(Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $list_post = ForumPost::where('subforum_id', $request->subforum_id)->get();



        }
    }

    public function createPost(Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required',
            'title' => 'required',
            'content' => 'required',
            ['images' => 'image|mimes:png,jpg|max:1024'],
            'video' => 'mimes:mp4,mov,3gp,qt|max:12000'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{

            $check = ForumPost::where('subforum_id', $request->subforum_id)->where('user_id', Auth::id())->where('title', $request->title)->first();

            if($check){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'You have created the same topic in the subforum!','data'=> '']), 401);
            }
            else{
                $post = [
                    'user_id' => Auth::id(),
                    'subforum_id' => $request->subforum_id,
                    'title' => $request->title,
                    'content' => $request->content
                ];

                $new_post = ForumPost::create($post);

                if($request->images){
                    $files = $request->file('images');
                    $this->storeImage($new_post->id, $files);
                }
                if($request->video){

                }

                return response()->json(new ValueMessage(['value'=>1,'message'=>'New Post Successfully Posted!','data'=> $new_post]), 200);

            }
        }

    }

    public function deletePost(Request $request){
        
    }

    public function createComment(Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $comment = [
                'user_id' => Auth::id(),
                'post_id' => $request->post_id,
                'content' => $request->content
            ];

            $new_comment = ForumComment::create($comment);

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Post Comment Success!','data'=> $new_comment]), 200);
        }
    }

    public function deleteComment(Request $request){

    }

    public function storeImage($id, $files){
        
        $post = ForumPost::where('id', $id)->first();

        if(!$post){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Not Found!','data'=> '']), 404);
        }
        else{
            $num = 1;

            foreach($files as $file){

                $fileName = str_replace(' ','-', $post['title'].'_'.$num);
                $guessExtension = $file->guessExtension();
                //dd($guessExtension);
                $store = Storage::disk('public')->putFileAs('forum/post/'.$id, $file ,$fileName.'.'.$guessExtension);


                $post_image = ForumImage::create([
                    'post_id' => $id,
                    'filename' => $fileName,
                    'path' => 'http://hainaservice.com/storage/'.$store
                ]);
                //dd($property_image);
                $num += 1; 
            }

            $posted_images = ForumImage::where('post_id', $id)->get();

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Post Image Success!','data'=> $posted_images]), 200);
        }
    }

    public function storeVideo($id, $video){
        $post = ForumPost::where('id', $id)->first();

        if(!$property){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Not Found!','data'=> '']), 404);
        }
        else{
            $fileName = str_replace(' ','-', 'video-'.$post['title']);
            $guessExtension = $file->guessExtension();
            //dd($guessExtension);
            $store = Storage::disk('public')->putFileAs('forum/post/'.$id, $video ,$fileName.'.'.$guessExtension);

            $post_video = ForumVideo::create([
                'post_id' => $id,
                'filename' => $fileName,
                'path' => 'http://hainaservice.com/storage/'.$store
            ]);

            $posted_video = ForumVideo::where('post_id', $id)->get();

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Post Video Success!','data'=> $posted_video]), 200);
        }
    }

    public function giveUpvote(Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check = Post::where('id', $request->post_id)->first();

            if($check){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Not Found!','data'=> '']), 404);
            }
            else{
                if($check['user_id'] == Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Cannot Own Upvote Post!','data'=> '']), 401);
                }
                else{
                    $new_upvote = ForumUpvote::create([
                        'user_id' => Auth::id(),
                        'post_id' => $request->post_id()
                    ]);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Upvote Success!','data'=> $new_upvote]), 200);
                }

            }
        }
    }

    public function assignMod(Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required',
            'user_id' => 'required',
            'role' => 'in:mod|submod'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $checkmod = ForumMod::where('user_id', Auth::id())->where('subforum_id', $request->subforum_id)->first();

            if(!$checkmod){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
            }
            else{
                $check_candidate = ForumMod::where('user_id', $request->user_id)->where('subforum_id', $request->subforum_id)->first();

                if($check_candidate){
                    $update_mod = ForumMod::where('user_id', $request->user_id)->where('subforum_id', $request->subforum_id)->update(
                        [
                            'role' => $request->role
                        ]
                    );

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Update Mod Success!','data'=> $update_mod]), 200);

                }
                else{
                    $new_mod = ForumMod::create([
                        'user_id' => $request->user_id,
                        'role' => $request->role,
                        'subforum_id' => $request->subforum_id
                    ]);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Assign New Mod Success!','data'=> $new_mod]), 200);
                }
                
            }
        }
    }

}