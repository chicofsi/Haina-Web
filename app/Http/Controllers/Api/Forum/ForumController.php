<?php

namespace App\Http\Controllers\Api\Forum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\User;
use App\Models\ForumCategory;
use App\Models\Subforum;
use App\Models\SubforumFollowers;
use App\Models\ForumBan;
use App\Models\ForumBookmark;
use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumFollowers;
use App\Models\ForumImage;
use App\Models\ForumLog;
use App\Models\ForumVideo;
use App\Models\ForumMod;
use App\Models\ForumUpvote;
use App\Models\PersonalAccessToken;

use App\Http\Controllers\Api\Notification\NotificationController;

use DateTime;
use File;

use App\Http\Resources\ValueMessage;

class ForumController extends Controller
{

    public function showCategory (){
        $category = ForumCategory::all();

        if(!$category || count($category) == 0){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'No category found!','data'=> '']), 404);
        }
        else{
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Category listed successfully!','data'=> $category]), 200);
        }
    }

    public function createSubforum (Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'image' => 'required|image|mimes:png,jpg|max:1024'
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
                    'name' => str_replace('"', "", $request->name),
                    'description' => str_replace('"', "", $request->description),
                    'category_id' => $request->category_id,
                    'subforum_image' => '',
                    'creator_id' => Auth::id()
                ];

                $new_subforum = Subforum::create($subforum);

                $new_mod = ForumMod::create([
                    'user_id' => Auth::id(),
                    'role' => 'mod',
                    'subforum_id' => $new_subforum->id
                ]);

                $files = $request->file('image');
                
                $fileName = str_replace(' ','-', $new_subforum->id.'-'.$subforum['name'].'-'.'picture');
                $guessExtension = $files->guessExtension();
                
                $store = Storage::disk('public')->putFileAs('forum/subforum', $files ,$fileName.'.'.$guessExtension);


                $update_image = Subforum::where('id', $new_subforum->id)->update([
                    'subforum_image' => 'http://hainaservice.com/storage/'.$store
                ]);

                $user = User::where('id', $new_subforum->creator_id)->first();

                $forumlog = ForumLog::create([
                    'subforum_id' => $new_subforum->id,
                    'forum_action' => 'CREATE',
                    'message' => $user['username'].' created "'.$new_subforum->name.'" subforum.'
                ]);

                $autofollow = $new_follow_subforum = SubforumFollowers::create([
                    'subforum_id' => $new_subforum->id,
                    'user_id' => Auth::id()
                ]);

                $modlog = ForumLog::create([
                    'subforum_id' => $new_subforum->id,
                    'forum_action' => 'MOD',
                    'message' => $user['username'].' is the new mod of "'.$new_subforum->name.'" subforum.'
                ]);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Subforum successfully created!','data'=> $new_subforum]), 200);
            }
        }

    }

    public function showMySubforum(){
            $check = Subforum::where('creator_id', Auth::id())->get();

            if(count($check) != 0){
               
                foreach($check as $key => $value){
                    $creator_count = [];

                    $value->post_count = count(ForumPost::where('subforum_id', $value->id)->where('deleted_at', null)->get());

                    $category_name = ForumCategory::where('id', $value->category_id)->first();

                    $value->category = $category_name['name'];
                    $value->category_zh = $category_name['name_zh'];
                    $value->subforum_followers = count(SubforumFollowers::where('subforum_id', $value->id)->get());

                    $post = ForumPost::where('subforum_id', $value->id)->where('deleted_at', null)->get();
                    foreach($post as $keypost => $valuepost){
                        array_push($creator_count, $valuepost->user_id);
                    }

                    $total_poster = array_unique($creator_count);
                    $value->total_poster = count($total_poster);

                    
                }

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Subforum found!','data'=> $check]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No subforum found!','data'=> '']), 404);
            }
    }

    public function showMyPost(){
            $post = ForumPost::where('user_id', Auth::id())->where('deleted_at', null)->with('images', 'videos')->get();

            foreach($post as $key => $value){
                $author = User::where('id', $value->user_id)->first();

                $likes = count(ForumUpvote::where('post_id', $value->id)->get());

                $check_comment = ForumComment::where('post_id', $value->id)->where('deleted_at', null)->orderBy('created_at', 'desc')->first();
    
                $author = User::where('id', $value->user_id)->first();
    
                $check_upvote = ForumUpvote::where('post_id', $value->id)->where('user_id', Auth::id())->first();

                $bookmark_status = ForumBookmark::where('post_id', $value->id)->where('user_id', Auth::id())->first();

                if($bookmark_status){
                    $value->bookmarked = true;
                }
                else{
                    $value->bookmarked = false;
                }
    
                $subforum_data = Subforum::where('id', $value->subforum_id)->first();
                $subforum_following = SubforumFollowers::where('subforum_id', $value->subforum_id)->where('user_id', Auth::id())->first();

                $subforum_creator = User::where('id', $subforum_data['creator_id'])->first();
                $subforum_data['creator_username'] = $subforum_creator['username'];

                $category_name = ForumCategory::where('id', $subforum_data['category_id'])->first();

                $subforum_data['category'] = $category_name['name'];
                $subforum_data['category_zh'] = $category_name['name_zh'];

                $subforum_followers_count = count(SubforumFollowers::where('subforum_id', $value->subforum_id)->get());
                $subforum_post_count = count(ForumPost::where('subforum_id', $value->subforum_id)->where('deleted_at', null)->get());

                $subforum_data['subforum_followers'] = $subforum_followers_count;
                $subforum_data['post_count'] = $subforum_post_count;
    
                if($subforum_following){
                    $follow_subforum = true;
                }
                else{
                    $follow_subforum = false;
                }
                
                $value->author = $author['username'];
                $value->author_photo =  "https://hainaservice.com/storage/".$author['photo'];
                $value->member_since = date("F Y", strtotime($author['created_at']));
                $value->like_count = $likes;
                $value->comment_count = count(ForumComment::where('post_id', $value->id)->where('deleted_at', null)->get());
                $value->subforum_follow = $follow_subforum;
                $value->subforum_data = $subforum_data;
                $value->author_data = $author;
            }

        if(count($post) == 0){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'No post found!','data'=> '']), 404);
        }
        else{
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get My Post Success!','data'=> $post]), 200);
        }
    }

    public function showAllSubforum(Request $request){

            //$check = Subforum::with(['posts' => function($q){
            //    $q->where('forum_post.deleted_at', '=', null);
            //}])->get();
            $check = Subforum::all();

            if(count($check) != 0){
                foreach($check as $key => $value){
                    $creator_count = [];
                    if($request->bearerToken()){
                        $check_followed = SubforumFollowers::where('subforum_id', $value->id)->where('user_id', auth('sanctum')->user()->id)->first();
                        
                        if($check_followed){
                            $value->followed = true;
                        }
                        else{
                            $value->followed = false;
                        }
                    }

                    $value->post_count = count(ForumPost::where('subforum_id', $value->id)->where('deleted_at', null)->get());

                    $category_name = ForumCategory::where('id', $value->category_id)->first();

                    $value->category = $category_name['name'];
                    $value->category_zh = $category_name['name_zh'];

                    $post = ForumPost::where('subforum_id', $value->id)->where('deleted_at', null)->get();
                    foreach($post as $keypost => $valuepost){
                        array_push($creator_count, $valuepost->user_id);
                    }

                    $total_poster = array_unique($creator_count);
                    $value->total_poster = count($total_poster);

                }

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Subforum found!','data'=> $check]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No subforum found!','data'=> '']), 404);
            }
    }

    public function showAllPost(Request $request){
        $validator = Validator::make($request->all(), [
            'sort_by' => 'in:time,likes'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            if($request->subforum_id == null){
                $list_post = ForumPost::where('deleted_at', null)->with(['comments' => function($q){
                    $q->where('forum_comment.deleted_at', '=', null);
                }], 'images', 'videos')->all();
            }
            else{
                $list_post = ForumPost::where('deleted_at', null)->where('subforum_id', $request->subforum_id)->with(['comments' => function($q){
                    $q->where('forum_comment.deleted_at', '=', null);
                }], 'images', 'videos')->get();
            }
            

            $threads = [];

            foreach($list_post as $key => $value){
                $likes = count(ForumUpvote::where('post_id', $value->id)->get());

                $lastpost = null;
                $check_comment = ForumComment::where('post_id', $value->id)->where('deleted_at', null)->orderBy('created_at', 'desc')->first();

                $author = User::where('id', $value->user_id)->first();

                if(!$check_comment){
                    $lastpost = $value->updated_at;
                }
                else{
                    $lastpost = $check_comment['created_at'];
                }


                $subforum_data = Subforum::where('id', $value->subforum_id)->first();
                

                $subforum_creator = User::where('id', $subforum_data['creator_id'])->first();
                $subforum_data['creator_username'] = $subforum_creator['username'];

                $category_name = ForumCategory::where('id', $subforum_data['category_id'])->first();
    
                $subforum_data['category'] = $category_name['name'];
                $subforum_data['category_zh'] = $category_name['name_zh'];

                $subforum_followers_count = count(SubforumFollowers::where('subforum_id', $value->subforum_id)->get());
                $subforum_post_count = count(ForumPost::where('subforum_id', $value->subforum_id)->where('deleted_at', null)->get());

                $subforum_data['subforum_followers'] = $subforum_followers_count;
                $subforum_data['post_count'] = $subforum_post_count;

                $bookmark = false;
                $follow_subforum = false;
                $upvote = false;

                if($request->bearerToken()){
                    $subforum_following = SubforumFollowers::where('subforum_id', $value->subforum_id)->where('user_id', auth('sanctum')->user()->id)->first();
                    $bookmark_status = ForumBookmark::where('post_id', $value->id)->where('user_id', auth('sanctum')->user()->id)->first();

                    if($bookmark_status){
                        $bookmark = true;
                    }
                    else{
                        $bookmark = false;
                    }

                    if($subforum_following){
                        $follow_subforum = true;
                    }
                    else{
                        $follow_subforum = false;
                    }

                    $check_upvote = ForumUpvote::where('post_id', $value->id)->where('user_id', auth('sanctum')->user()->id)->first();

                    if(!$check_upvote){
                        $upvote = false;
                    }
                    else{
                        $upvote = true;
                    }
                }
                

                $prelist = [
                    'id' => $value->id,
                    'title' => $value->title,
                    'author' => $author['username'],
                    'user_id' => $author['id'],
                    'author_photo' => "https://hainaservice.com/storage/".$author['photo'],
                    'member_since' => date("F Y", strtotime($author['created_at'])),
                    'like_count' => $likes,
                    'comment_count' => count(ForumComment::where('post_id', $value->id)->where('deleted_at', null)->get()),
                    'view_count' => $value->view_count,
                    'share_count' => $value->share_count,
                    'created' => $value->created_at,
                    'content' => $value->content,
                    'images' => $value->images,
                    'videos' => $value->videos,
                    //'bookmarked' => $bookmark,
                    //'subforum_follow' => $follow_subforum,
                    'subforum_data' => $subforum_data,
                    'author_data' => $author,
                    'last_update' => $lastpost
                ];
                
                if($request->bearerToken()){
                    $prelist['upvoted'] = $upvote;
                    $prelist['bookmarked'] = $bookmark;
                    $prelist['subforum_follow'] = $follow_subforum;
                }

                $list = (object) $prelist;

                array_push($threads, $list);

               
            }
            if($request->sort_by == "time"){
                $threads = collect($threads)->sortByDesc('last_update')->toArray();
            }
            else{
                $threads = collect($threads)->sortByDesc('like_count')->toArray();
            }

            $total = count($threads);
            $per_page = 10;
            $current_page = $request->page ?? 1;

            $starting_point = ($current_page * $per_page) - $per_page;

            //$result = $threads->offset(($current_page - 1) * $per_page)->limit($per_page)->get();

            $threads = array_slice($threads, $starting_point, $per_page);

            $result = new \stdClass();
            $result->threads = $threads;
            $result->total = $total;
            $result->current_page = (int)$current_page;
            $result->total_page = ceil($total/$per_page);
            

            if(count($threads) == 0){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No threads found!','data'=> '']), 404);
            }
            else{
                //$object = new \stdClass();
                //$threads->followed = SubforumFollower

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Threads displayed successfully!','data'=> $result]), 200);
            }

        }
    }

    public function showComment (Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $checkpost = ForumPost::where('id', $request->post_id)->where('deleted_at', null)->first();

            if(!$checkpost){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Post not found!','data'=> '']), 404);
            }
            else{
                $post_comment = ForumComment::where('post_id', $request->post_id)->where('deleted_at', null)->get();

                foreach($post_comment as $key => $value){
                    $userdata = User::where('id',$value->user_id)->first();

                    $post = ForumPost::where('id', $request->post_id)->first();

                    $checkmod = ForumMod::where('user_id', $value->user_id)->where('subforum_id', $post['subforum_id'])->first();
                    $checkban = ForumBan::where('subforum_id', $post['subforum_id'])->where('user_id', $value->user_id)->first();
                    
                    if($checkban){
                        $value->mod = "banned";
                    }
                    else if($checkmod){
                        $value->mod = $checkmod['role'];
                    }
                    else{
                        $value->mod = "none";
                    }

                    $value->username = $userdata['username'];
                    $value->user_photo = "https://hainaservice.com/storage/".$userdata['photo'];
                    $value->member_since = date("F Y", strtotime($userdata['created_at']));
                }

                if(!$post_comment){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'No comments!','data'=> '']), 404);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Comment displayed successfully!','data'=> $post_comment]), 200);
                }
            }
        }
    }

    public function showPost (Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $post_detail = ForumPost::where('id', $request->post_id)->where('deleted_at', null)->with('images', 'videos')->first();

            if(!$post_detail){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Post not found!','data'=> '']), 404);
            }
            else{
                $add_view = $post_detail['view_count'] + 1;

                $update_view = $post_detail->update([
                    'view_count' => $add_view
                ]);

                if($request->bearerToken()){
                    $bookmark_status = ForumBookmark::where('post_id', $request->post_id)->where('user_id', auth('sanctum')->user()->id)->first();

                    if($bookmark_status){
                        $post_detail['bookmarked'] = true;
                    }
                    else{
                        $post_detail['bookmarked'] = false;
                    }
                }
                

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Post displayed successfully!','data'=> $post_detail]), 200);
            }
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

            $check = ForumPost::where('subforum_id', $request->subforum_id)->where('user_id', Auth::id())->where('title', $request->title)->where('deleted_at', null)->first();
            $check_ban = ForumBan::where('subforum_id', $request->subforum_id)->where('user_id', Auth::id())->first();

            if($check_ban){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'You are banned in this subforum!','data'=> '']), 401);
            }
            else if($check){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'You have created the same topic in the subforum!','data'=> '']), 401);
            }
            else{
                $title = str_replace('"', "", $request->title);
                $content = str_replace('"', "", $request->content);

                $post = [
                    'user_id' => Auth::id(),
                    'subforum_id' => $request->subforum_id,
                    'title' => $title,
                    'content' => $content
                ];

                $new_post = ForumPost::create($post);

                if($request->images){
                    $files = $request->file('images');
                    $this->storeImage($new_post->id, $files);
                }
                if($request->video){
                    $video = $request->file('video');
                    $this->storeVideo($new_post->id, $video);
                }

                $user = User::where('id', $new_post->user_id)->first();
                $subforum = Subforum::where('id', $new_post->subforum_id)->first();

                $forumlog = ForumLog::create([
                    'subforum_id' => $subforum['id'],
                    'forum_action' => 'POST',
                    'message' => $user['username'].' created "'.$new_post->title.'" in '.$subforum['name'].'.'
                ]);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'New Post Successfully Posted!','data'=> $new_post]), 200);

            }
        }

    }

    public function deletePost(Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $checkpost = ForumPost::where('id', $request->post_id)->where('deleted_at', null)->first();
           
            if(!$checkpost){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Not Found!','data'=> '']), 404);
            }
            else{
                $subforum = ForumPost::select('subforum_id')->where('id',$checkpost['id'])->first();
                $checkmod = ForumMod::where('user_id', Auth::id())->where('subforum_id', $subforum['subforum_id'])->first();

                if($checkpost['user_id'] != Auth::id() && !$checkmod){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else{
                    $checkcomment = ForumComment::where('post_id', $request->post_id)->where('deleted_at', null)->get();
                    $checkimage = ForumImage::where('post_id', $request->post_id)->get();
                    $checkvideo = ForumVideo::where('post_id', $request->post_id)->get();
                    $checkupvote = ForumUpvote::where('post_id', $request->post_id)->get();   

                    if($checkcomment){
                        $delete_comment = ForumComment::where('post_id', $request->post_id)->where('deleted_at', null)->update([
                            'deleted_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                    if($checkimage){
                        /*
                        foreach($checkimage as $key => $value){
                            $path = str_replace("http://hainaservice.com/storage", "", $value->path);

                            Storage::disk('public')->delete($path);
                        }
                        */

                        $delete_image = ForumImage::where('post_id', $request->post_id)->where('deleted_at', null)->update([
                            'deleted_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                    if($checkvideo){
                        /*
                        foreach($checkvideo as $key => $value){
                            $path = str_replace("http://hainaservice.com/storage", "", $value->path);

                            Storage::disk('public')->delete($path);
                        }
                        */

                        $delete_video = ForumVideo::where('post_id', $request->post_id)->where('deleted_at', null)->update([
                            'deleted_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                    if($checkupvote){
                        $delete_upvote = ForumUpvote::where('post_id', $request->post_id)->delete();
                    }

                    if($checkmod && $checkmod['user_id'] != $checkpost['user_id']){
                        //hapus by mod
                        $post_owner = ForumPost::where('id', $request->post_id)->first();
                        $subforum = Subforum::where('id', $post_owner['subforum_id'])->first();
                        $token = [];
                        $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $post_owner['user_id'])->get();

                        foreach($usertoken as $key => $value){
                            array_push($token, $value->name); 
                        }

                        $mod = User::where('id', $checkmod['user_id'])->first();

                        $forumlog = ForumLog::create([
                            //moddel
                            'subforum_id' => $post_owner['subforum_id'],
                            'forum_action' => 'MOD',
                            'message' => $mod['username'].' deleted "'.$post_owner['title'].'" from '.$subforum['name'].'.'
                        ]);

                        NotificationController::createNotif($post_owner['user_id'], "Your post is removed", "Your post ".$post_owner['title']." is removed by a moderator.", 6, 3);
                        foreach ($token as $key => $value) {
                            NotificationController::sendPush($post_owner['user_id'], $value, "Your post is removed", "Your post ".$post_owner['title']." is removed by a moderator.", "Forum", "delete");
                        }
                    }
                    $delete_post = ForumPost::where('id', $request->post_id)->where('deleted_at', null)->update([
                        'deleted_at' => date('Y-m-d H:i:s')
                    ]);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Post deleted successfully','data'=> $checkpost]), 200);
                    
                }

            }
        }
        
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
            $post = ForumPost::where('id', $request->post_id)->first();
            $subforum = Subforum::where('id', $post['subforum_id'])->first();
            $check_ban = ForumBan::where('subforum_id', $subforum['id'])->where('user_id', Auth::id())->first();

            if($check_ban){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'You are banned in this subforum!','data'=> '']), 401);
            }
            else{
                $comment = [
                    'user_id' => Auth::id(),
                    'post_id' => $request->post_id,
                    'content' => $request->content
                ];
    
                $new_comment = ForumComment::create($comment);
    
                $user = User::where('id', $new_comment->user_id)->first();
                $post = ForumPost::where('id', $new_comment->post_id)->first();
    
                $forumlog = ForumLog::create([
                    'subforum_id' => $post['subforum_id'],
                    'forum_action' => 'COMMENT',
                    'message' => $user['username'].' commented in '.$post['title'].' thread.'
                ]);
    
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Post Comment Success!','data'=> $new_comment]), 200);
            }
            
        }
    }

    public function deleteComment(Request $request){
        $validator = Validator::make($request->all(), [
            'comment_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check = ForumComment::where('id', $request->comment_id)->where('deleted_at', null)->first();
            

            if(!$check){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Comment Not Found!','data'=> '']), 404);
            }
            else{
                $subforum = ForumPost::select('subforum_id')->where('id',$check['post_id'])->first();
                $checkmod = ForumMod::where('user_id', Auth::id())->where('subforum_id', $subforum['subforum_id'])->first();

                if($check['user_id'] != Auth::id() && !$checkmod){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else{
                    if($checkmod && $checkmod['user_id'] != $check['user_id']){
                        //hapus by mod
                        $comment_owner = ForumComment::where('id', $request->comment_id)->first();
                        $post_name = ForumPost::where('id', $comment_owner['post_id'])->first();
                        $token = [];
                        $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $comment_owner['user_id'])->get();
    
                        foreach($usertoken as $key => $value){
                            array_push($token, $value->name); 
                        }
    
                        $mod = User::where('id', $checkmod['user_id'])->first();
    
                        $forumlog = ForumLog::create([
                            //moddel
                            'subforum_id' => $post_name['subforum_id'],
                            'forum_action' => 'MOD',
                            'message' => $mod['username'].' deleted "'.$comment_owner['content'].'" from '.$post_name['title'].' in '.$subforum['name'].'.'
                        ]);
    
                        NotificationController::createNotif($comment_owner['user_id'], "Your comment is removed", "Your comment at ".$post_name['title']." is removed by a moderator.", 6, 3);
                        foreach ($token as $key => $value) {
                            NotificationController::sendPush($comment_owner['user_id'], $value, "Your comment is removed", "Your comment at ".$post_name['title']." is removed by a moderator.", "Forum", "delete");
                        }
                    }
    
                    //$delete_comment = ForumComment::where('id', $request->comment_id)->delete();

                    $delete_time = date('Y-m-d H:i:s');

                    $delete_comment = ForumComment::where('id', $request->comment_id)->update([
                        'deleted_at' => date('Y-m-d H:i:s')
                    ]);

                    $check->deleted_at = $delete_time;
    
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Comment deleted successfully!','data'=> $check]), 200);
                }
            } 
        }

    }

    public function search(Request $request){
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|min:2'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $result = new \stdClass();

            $subforum = Subforum::where('name', 'like', '%'.$request->keyword.'%')->get();

            foreach($subforum as $keysub => $valuesub){
                $creator_count = [];
                

                $valuesub->post_count = count(ForumPost::where('subforum_id', $valuesub->id)->where('deleted_at', null)->get());

                $category_name = ForumCategory::where('id', $valuesub->category_id)->first();
                $subforum_creator = User::where('id', $valuesub->creator_id)->first();
                $valuesub->creator_username = $subforum_creator['username'];

                $valuesub->category = $category_name['name'];
                $valuesub->category_zh = $category_name['name_zh'];

                $post = ForumPost::where('subforum_id', $valuesub->id)->where('deleted_at', null)->get();

                foreach($post as $keypost => $valuepost){
                    array_push($creator_count, $valuepost->user_id);
                }

                $total_poster = array_unique($creator_count);
                $valuesub->total_poster = count($total_poster);
                
                if($request->bearerToken()){
                    $check_followed = SubforumFollowers::where('subforum_id', $valuesub->id)->where('user_id', auth('sanctum')->user()->id)->first();
                    if($check_followed){
                        $valuesub->followed = true;
                    }
                    else{
                        $valuesub->followed = false;
                    }
                }
                

            }

            $thread = ForumPost::where('title', 'like', '%'.$request->keyword.'%')->where('deleted_at', null)->get();

            foreach($thread as $keythread => $valuethread){
                $author = User::where('id', $valuethread->user_id)->first();

                $likes = count(ForumUpvote::where('post_id', $valuethread->id)->get());

                $check_comment = ForumComment::where('post_id', $valuethread->id)->where('deleted_at', null)->orderBy('created_at', 'desc')->first();
    
                $author = User::where('id', $valuethread->user_id)->first();
    
                $subforum_data = Subforum::where('id', $valuethread->subforum_id)->first();


                $subforum_creator = User::where('id', $subforum_data['creator_id'])->first();
                $subforum_data['creator_username'] = $subforum_creator['username'];

                $category_name = ForumCategory::where('id', $subforum_data['category_id'])->first();

                $subforum_data['category'] = $category_name['name'];
                $subforum_data['category_zh'] = $category_name['name_zh'];

                $subforum_followers_count = count(SubforumFollowers::where('subforum_id', $valuethread->subforum_id)->get());
                $subforum_post_count = count(ForumPost::where('subforum_id', $valuethread->subforum_id)->where('deleted_at', null)->get());

                $subforum_data['subforum_followers'] = $subforum_followers_count;
                $subforum_data['post_count'] = $subforum_post_count;

                $images = ForumImage::where('post_id', $valuethread->id)->get();
                $videos = ForumVideo::where('post_id', $valuethread->id)->get();
                //$upvoted = ForumUpvote::where('post_id', $valuethread->id)->where('user_id', Auth::id())->first();

                //tanda
                if($images){
                    $valuethread->images = $images;
                }
                if($videos){
                    $valuethread->videos = $videos;
                }

                if($request->bearerToken()){
                    $check_upvote = ForumUpvote::where('post_id', $valuethread->id)->where('user_id', Auth::id())->first();
                    $subforum_following = SubforumFollowers::where('subforum_id', $valuethread->subforum_id)->where('user_id', Auth::id())->first();

                    if($subforum_following){
                        $follow_subforum = true;
                    }
                    else{
                        $follow_subforum = false;
                    }
                    $valuethread->subforum_follow = $follow_subforum;

                    if(!$check_upvote){
                        $upvote = false;
                    }
                    else{
                        $upvote = true;
                    }
                    $valuethread->upvoted = $upvote;
                    

                    $bookmark_status = ForumBookmark::where('post_id', $valuethread->id)->where('user_id', Auth::id())->first();

                    if($bookmark_status){
                        $valuethread->bookmarked = true;
                    }
                    else{
                        $valuethread->bookmarked = false;
                    }
                }
                

                $valuethread->author = $author['username'];
                $valuethread->author_photo =  "https://hainaservice.com/storage/".$author['photo'];
                $valuethread->member_since = date("F Y", strtotime($author['created_at']));
                $valuethread->like_count = $likes;
                $valuethread->comment_count = count(ForumComment::where('post_id', $valuethread->id)->where('deleted_at', null)->get());
                $valuethread->subforum_data = $subforum_data;
                $valuethread->author_data = $author;
            }

            if(count($subforum) == 0 && count($thread) == 0){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Keyword Not Found!','data'=> '']), 404);
            }

            $result->subforum = $subforum;
            $result->post = $thread;

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Search Result Completed!','data'=> $result]), 200);
        }
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

        if(!$post){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Not Found!','data'=> '']), 404);
        }
        else{
            $fileName = str_replace(' ','-', 'video-'.$post['title']);
            $guessExtension = $video->guessExtension();
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
            $check = ForumPost::where('id', $request->post_id)->where('deleted_at', null)->first();
            

            if(!$check){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Not Found!','data'=> '']), 404);
            }
            else{
                $post = ForumPost::where('id', $request->post_id)->first();
                $subforum = Subforum::where('id', $post['subforum_id'])->first();
                $check_ban = ForumBan::where('subforum_id', $subforum['id'])->where('user_id', Auth::id())->first();

                if($check_ban){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'You are banned in this subforum!','data'=> '']), 401);
                }
                else{
                    $check_duplicate = ForumUpvote::where('post_id', $request->post_id)->where('user_id', Auth::id())->first();

                    if($check_duplicate){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Cannot Upvote More Than Once!','data'=> '']), 401);
                    }
                    else{
                        $new_upvote = ForumUpvote::create([
                            'user_id' => Auth::id(),
                            'post_id' => $request->post_id
                        ]);

                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Upvote Success!','data'=> $new_upvote]), 200);
                    }
                }

            }
        }
    }

    public function cancelUpvote(Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check = ForumPost::where('id', $request->post_id)->where('deleted_at', null)->first();
            
            if(!$check){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Not Found!','data'=> '']), 404);
            }
            else{
                $check_upvote = ForumUpvote::where('post_id', $request->post_id)->where('user_id', Auth::id())->first();

                if(!$check_upvote){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'You did not upvote this post!','data'=> '']), 401);
                }
                else{
                    $delete_upvote = ForumUpvote::where('post_id', $request->post_id)->where('user_id', Auth::id())->delete();

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Upvote removed!','data'=> $check_upvote]), 200);
                }
            }
        }
    }

    public function sharePost(Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check = ForumPost::where('id', $request->post_id)->where('deleted_at', null)->first();
            
            if(!$check){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Not Found!','data'=> '']), 404);
            }
            else{
                $share = $check['share_count'] + 1;

                $update_share = ForumPost::where('id', $request->post_id)->update([
                    'share_count' => $share
                ]);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Share post success!','data'=> $update_share]), 200);
            }
        }
    }

    public function followSubforum(Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check = SubforumFollowers::where('user_id', Auth::id())->where('subforum_id', $request->subforum_id)->first();

            if($check){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'You already followed this subforum!','data'=> '']), 404);
            }
            else{
                $new_follow_subforum = SubforumFollowers::create([
                    'subforum_id' => $request->subforum_id,
                    'user_id' => Auth::id()
                ]);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Follow subforum success!','data'=> $new_follow_subforum]), 200);
            }
        }
    }

    public function unfollowSubforum(Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check = SubforumFollowers::where('user_id', Auth::id())->where('subforum_id', $request->subforum_id)->first();

            if(!$check){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'You do not follow this subforum!','data'=> '']), 404);
            }
            else{
                $delete_follow = SubforumFollowers::where('user_id', Auth::id())->where('subforum_id', $request->subforum_id)->delete();

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Unfollow subforum success!','data'=> $check]), 200);
            }
        }
    }

    public function myFollowingSubforum(){
        $following = SubforumFollowers::where('user_id', Auth::id())->get();

        
        if($following){

            $list_follow = [];
            foreach($following as $key => $value){
                $creator_count = [];

                $subforum = Subforum::where('id',$value->subforum_id)->first();

                $category_name = ForumCategory::where('id', $subforum['category_id'])->first();

                $subforum['category'] = $category_name['name'];
                $subforum['category_zh'] = $category_name['name_zh'];
                $subforum['role'] = "mod";
                $subforum['post_count'] = count(ForumPost::where('subforum_id', $subforum['id'])->where('deleted_at', null)->get());
                $subforum['subforum_followers'] = count(SubforumFollowers::where('subforum_id', $subforum['id'])->get());

                $post = ForumPost::where('subforum_id', $subforum['id'])->where('deleted_at', null)->get();
                foreach($post as $keypost => $valuepost){
                    array_push($creator_count, $valuepost->user_id);
                }

                $check_followed = SubforumFollowers::where('subforum_id', $subforum['id'])->where('user_id', Auth::id())->first();
                if($check_followed){
                    $subforum['followed'] = true;
                }
                else{
                    $subforum['followed'] = false;
                }

                $total_poster = array_unique($creator_count);
                $subforum['total_poster'] = count($total_poster);

                
                array_push($list_follow, $subforum);
            }
            
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Following subforum list success!','data'=> $list_follow]), 200);
            

        }
        else if(!$following || count($following) == 0){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'You have not followed any subforum yet!','data'=> '']), 404);
        }
    }

    public function userFollowingSubforum(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $following = SubforumFollowers::where('user_id', $request->user_id)->get();

            if($following){

                $list_follow = [];

                foreach($following as $key => $value){
                    $subforum = Subforum::where('id',$value->subforum_id)->first();

                    $creator_count = [];

                    $category_name = ForumCategory::where('id', $subforum['category_id'])->first();

                    $subforum['category'] = $category_name['name'];
                    $subforum['category_zh'] = $category_name['name_zh'];
                    $subforum['role'] = "mod";
                    $subforum['subforum_followers'] = count(SubforumFollowers::where('subforum_id', $subforum['id'])->get());

                    $post = ForumPost::where('subforum_id', $subforum['id'])->where('deleted_at', null)->get();
                    foreach($post as $keypost => $valuepost){
                        array_push($creator_count, $valuepost->user_id);
                    }

                    $check_followed = SubforumFollowers::where('subforum_id', $subforum['id'])->where('user_id', Auth::id())->first();
                    if($check_followed){
                        $subforum['followed'] = true;
                    }
                    else{
                        $subforum['followed'] = false;
                    }

                    $total_poster = array_unique($creator_count);
                    $subforum['total_poster'] = count($total_poster);


                    array_push($list_follow, $subforum);
                }

                if(count($list_follow) == 0){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'You have not followed any subforum!','data'=> '']), 404);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Following subforum list success!','data'=> $list_follow]), 200);
                }
                
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Not found!','data'=> '']), 404);
            }
        }
    }

    public function showModList(Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $checkmod = ForumMod::where('subforum_id', $request->subforum_id)->get();

            foreach($checkmod as $key => $value){
                $username = User::where('id', $value->user_id)->first();

                $value->username = $username['username'];
                $value->photo = "https://hainaservice.com/storage/".$username['photo'];
                $value->member_since = date("F Y", strtotime($username['created_at']));
            }

            if($checkmod && count($checkmod) != 0){
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Mod List Success!','data'=> $checkmod]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Error getting mod list!','data'=> '']), 404);
            }
        }
    }

    public function banUser(Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required',
            'user_id' => 'required',
            'reason' => 'required',
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
                $check_user = User::where('id', $request->user_id)->first();

                if($check_user){

                    $check_ban = ForumBan::where('user_id', $request->user_id)->where('subforum_id', $request->subforum_id)->first();
                    if($check_ban){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'User already banned in this subforum!','data'=> '']), 401);
                    }
                    else{
                        $banned = ForumBan::create([
                            'user_id' => $request->user_id,
                            'subforum_id' => $request->subforum_id,
                            'mod_id' => $checkmod['id'],
                            'reason' => $request->reason
                        ]);
                        $subforum = Subforum::where('id', $request->subforum_id)->first();
                        $user = User::where('id', $banned->user_id)->first();
                        $mod = User::where('id', Auth::id())->first();
    
                        $forumlog = ForumLog::create([
                            'subforum_id' => $banned->subforum_id,
                            'forum_action' => 'MOD',
                            'message' => $mod['username'].' banned '.$user['username'].' in '.$subforum['name'].' for '.$banned->reason.'.'
                        ]);
    
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'User Ban Success!','data'=> $banned]), 200);
                    }

                    
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'User not found!','data'=> '']), 404);
                }
            }
        }
    }

    public function checkModLog(Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $checksubforum = Subforum::where('id', $request->subforum_id)->first();

            if($checksubforum){
                $checkmod = ForumMod::where('user_id', Auth::id())->where('subforum_id', $request->subforum_id)->first();

                if(!$checkmod){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else{
                    $log = ForumLog::where('subforum_id', $request->subforum_id)->where('forum_action', 'MOD')->orderBy('created_at', 'DESC')->get();

                    return response()->json(new ValueMessage(['value'=>1, 'message'=>'Show Subforum Log Success!', 'data'=>$log]));
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Subforum not found!','data'=> '']), 404);
            }  

        }
    }

    public function showAllThreads(Request $request){

        $list_post = ForumPost::where('deleted_at', null)->with('comments', 'images', 'videos')->get();
        $hot_threads = [];
        $threads = [];

        foreach($list_post as $key => $value){
            $likes = count(ForumUpvote::where('post_id', $value->id)->get());

            $check_comment = ForumComment::where('post_id', $value->id)->where('deleted_at', null)->orderBy('created_at', 'desc')->first();

            $author = User::where('id', $value->user_id)->first();

            $bookmark = false;
            $follow_subforum = false;
            $upvote = false;

            if($request->bearerToken()){
                $subforum_following = SubforumFollowers::where('subforum_id', $value->subforum_id)->where('user_id', auth('sanctum')->user()->id)->first();
                $bookmark_status = ForumBookmark::where('post_id', $value->id)->where('user_id', auth('sanctum')->user()->id)->first();

                if($bookmark_status){
                    $bookmark = true;
                }
                else{
                    $bookmark = false;
                }

                if($subforum_following){
                    $follow_subforum = true;
                }
                else{
                    $follow_subforum = false;
                }

                $check_upvote = ForumUpvote::where('post_id', $value->id)->where('user_id', auth('sanctum')->user()->id)->first();

                if(!$check_upvote){
                    $upvote = false;
                }
                else{
                    $upvote = true;
                }
            }
            
            
            $subforum_data = Subforum::where('id', $value->subforum_id)->first();
            

            $category_name = ForumCategory::where('id', $subforum_data['category_id'])->first();

            $subforum_creator = User::where('id', $subforum_data['creator_id'])->first();
            $subforum_data['creator_username'] = $subforum_creator['username'];

            $subforum_data['category'] = $category_name['name'];
            $subforum_data['category_zh'] = $category_name['name_zh'];

            $subforum_followers_count = count(SubforumFollowers::where('subforum_id', $value->subforum_id)->get());
            $subforum_post_count = count(ForumPost::where('subforum_id', $value->subforum_id)->where('deleted_at', null)->get());

            $subforum_data['subforum_followers'] = $subforum_followers_count;
            $subforum_data['post_count'] = $subforum_post_count;

            $prelist = [
                'id' => $value->id,
                'title' => $value->title,
                'author' => $author['username'],
                'user_id' => $author['id'],
                'author_photo' => "https://hainaservice.com/storage/".$author['photo'],
                'member_since' => date("F Y", strtotime($author['created_at'])),
                'like_count' => $likes,
                'comment_count' => count(ForumComment::where('post_id', $value->id)->where('deleted_at', null)->get()),
                'view_count' => $value->view_count,
                'share_count' => $value->share_count,
                'created' => $value->created_at,
                'content' => $value->content,
                'images' => $value->images,
                'videos' => $value->videos,
                //'bookmarked' => $value->bookmarked,
                //'subforum_follow' => $follow_subforum,
                'subforum_data' => $subforum_data,
                'author_data' => $author
            ];

            if($request->bearerToken()){
                $prelist['upvoted'] = $upvote;
                $prelist['bookmarked'] = $bookmark;
                $prelist['subforum_follow'] = $follow_subforum;
            }

            $list = (object) $prelist;

            array_push($threads, $list);

        }

        $created = array_column($threads, 'created');
        $title = array_column($threads, 'title');

        array_multisort($created, SORT_DESC, $title, SORT_DESC, $threads);
        //dd($threads);

        $total = count($threads);
        $per_page = 10;
        $current_page = $request->page ?? 1;

        $starting_point = ($current_page * $per_page) - $per_page;

        //$result = $threads->offset(($current_page - 1) * $per_page)->limit($per_page)->get();

        $threads = array_slice($threads, $starting_point, $per_page);

        $result = new \stdClass();
        $result->threads = $threads;
        $result->total = $total;
        $result->current_page = (int)$current_page;
        $result->total_page = ceil($total/$per_page);
        
        /*
        //custom length
        //$threads = array_slice($threads, $starting_point, $per_page, true);

        $all_threads = new LengthAwarePaginator($threads, $total, $per_page, $current_page, [
            'path' => 'http://testgit.hainaservice.com/api/forum/all_post',
            'query' => ''
        ]);
        */
        

        /*
        //length aware
        $current_page = LengthAwarePaginator::resolveCurrentPage();
        $current_page_threads = array_slice($threads, ($current_page - 1) * 10, 10);

        $threads_to_show = new LengthAwarePaginator($current_page_threads, count(collect($threads)), 10);
        */
        if(count($threads) > 0){

            return response()->json(new ValueMessage(['value'=>1,'message'=>'All threads succesfully displayed!','data'=> $result]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'No posts found!','data'=> '']), 404);
        }
    }

    public function showHotThreads(Request $request){
        $date =  date("Y:m:d H:i:s");
        $datebefore = mktime(0, 0, 0, date("m")-1, date("d"),  date("Y"));
        //$datebefore = date_add($date, date_interval_create_from_date_string('-90 days'));

        //dd($datebefore);

        $list_post = ForumPost::where('deleted_at', null)->with(['comments' => function($q){
            $q->where('forum_comment.deleted_at', '=', null);
        }], 'images', 'videos')->whereDate('created_at', '>', $datebefore)->whereDate('created_at', '<=', $date)->get();
        $hot_threads = [];
        $threads = [];

        foreach($list_post as $key => $value){
            $likes = count(ForumUpvote::where('post_id', $value->id)->get());

            $check_comment = ForumComment::where('post_id', $value->id)->where('deleted_at', null)->orderBy('created_at', 'desc')->first();

            $author = User::where('id', $value->user_id)->first();

            $bookmark = false;
            $follow_subforum = false;
            $upvote = false;

            if($request->bearerToken()){
                $subforum_following = SubforumFollowers::where('subforum_id', $value->subforum_id)->where('user_id', auth('sanctum')->user()->id)->first();
                $bookmark_status = ForumBookmark::where('post_id', $value->id)->where('user_id', auth('sanctum')->user()->id)->first();

                if($bookmark_status){
                    $bookmark = true;
                }
                else{
                    $bookmark = false;
                }

                if($subforum_following){
                    $follow_subforum = true;
                }
                else{
                    $follow_subforum = false;
                }

                $check_upvote = ForumUpvote::where('post_id', $value->id)->where('user_id', auth('sanctum')->user()->id)->first();

                if(!$check_upvote){
                    $upvote = false;
                }
                else{
                    $upvote = true;
                }
            }

            $subforum_data = Subforum::where('id', $value->subforum_id)->first();
            //$subforum_following = SubforumFollowers::where('subforum_id', $value->subforum_id)->where('user_id', Auth::id())->first();
            
            $category_name = ForumCategory::where('id', $subforum_data['category_id'])->first();

            $subforum_followers_count = count(SubforumFollowers::where('subforum_id', $value->subforum_id)->get());
            $subforum_post_count = count(ForumPost::where('subforum_id', $value->subforum_id)->where('deleted_at', null)->get());

            $subforum_data['subforum_followers'] = $subforum_followers_count;
            $subforum_data['post_count'] = $subforum_post_count;

            $subforum_creator = User::where('id', $subforum_data['creator_id'])->first();
            $subforum_data['creator_username'] = $subforum_creator['username'];

            $subforum_data['category'] = $category_name['name'];
            $subforum_data['category_zh'] = $category_name['name_zh'];

            

            $prelist = [
                'id' => $value->id,
                'title' => $value->title,
                'author' => $author['username'],
                'user_id' => $author['id'],
                'author_photo' => "https://hainaservice.com/storage/".$author['photo'],
                'member_since' => date("F Y", strtotime($author['created_at'])),
                'like_count' => $likes,
                'comment_count' => count(ForumComment::where('post_id', $value->id)->where('deleted_at', null)->get()),
                'view_count' => $value->view_count,
                'share_count' => $value->share_count,
                'engagement_count' => $likes + count(ForumComment::where('post_id', $value->id)->where('deleted_at', null)->get()),
                'created' => $value->created_at,
                'content' => $value->content,
                'images' => $value->images,
                'videos' => $value->videos,
                //'bookmarked' => $value->bookmarked,
                //'subforum_follow' => $follow_subforum,
                'subforum_data' => $subforum_data,
                'author_data' => $author
            ];

            if($request->bearerToken()){
                $prelist['upvoted'] = $upvote;
                $prelist['bookmarked'] = $bookmark;
                $prelist['subforum_follow'] = $follow_subforum;
            }

            $list = (object) $prelist;

            array_push($threads, $list);

        }

        $engage = array_column($threads, 'engagement_count');
        $views = array_column($threads, 'view_count');

        array_multisort($engage, SORT_DESC, $views, SORT_DESC, $threads);
        $hot_threads = array_slice($threads, 0, 10);

        $total = count($hot_threads);
        $per_page = 10;
        $current_page = 1;

        $result = new \stdClass();
        $result->threads = $threads;
        $result->total = $total;
        $result->current_page = (int)$current_page;
        $result->total_page = 1;

        if(count($hot_threads) > 0){

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Hot threads succesfully displayed!','data'=> $hot_threads]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'No posts found!','data'=> '']), 404);
        }
    }

    public function showHomeThreads(Request $request){

        if ($request->bearerToken()) {
                //dd(auth('sanctum')->user());
            $subforum_followed = SubforumFollowers::where('user_id', auth('sanctum')->user()->id)->get();

            if(count($subforum_followed) > 0){
                $id_followed = [];
                foreach($subforum_followed as $key => $value){
                    array_push($id_followed, $value->subforum_id);
                }

                $list_post = ForumPost::where('deleted_at', null)->whereIn('subforum_id', $id_followed)->orderBy('created_at', 'desc')->with('comments', 'images', 'videos')->get();
                $home_threads = [];
                $threads = [];

                foreach($list_post as $keypost => $valuepost){
                    $author = User::where('id', $valuepost->user_id)->first();

                    $likes = count(ForumUpvote::where('post_id', $valuepost->id)->get());

                    $check_comment = ForumComment::where('post_id', $valuepost->id)->where('deleted_at', null)->orderBy('created_at', 'desc')->first();
        
                    $author = User::where('id', $valuepost->user_id)->first();
        
                    $check_upvote = ForumUpvote::where('post_id', $valuepost->id)->where('user_id', auth('sanctum')->user()->id)->first();

                    $bookmark_status = ForumBookmark::where('post_id', $valuepost->id)->where('user_id', auth('sanctum')->user()->id)->first();

                    if($bookmark_status){
                        $valuepost->bookmarked = true;
                    }
                    else{
                        $valuepost->bookmarked = false;
                    }

                    $subforum_data = Subforum::where('id', $valuepost->subforum_id)->first();
                    $subforum_following = SubforumFollowers::where('subforum_id', $valuepost->subforum_id)->where('user_id', auth('sanctum')->user()->id)->first();
        
                    $subforum_creator = User::where('id', $subforum_data['creator_id'])->first();
                    $subforum_data['creator_username'] = $subforum_creator['username'];

                    $category_name = ForumCategory::where('id', $subforum_data['category_id'])->first();

                    $subforum_data['category'] = $category_name['name'];
                    $subforum_data['category_zh'] = $category_name['name_zh'];

                    $subforum_followers_count = count(SubforumFollowers::where('subforum_id', $valuepost->subforum_id)->get());
                    $subforum_post_count = count(ForumPost::where('subforum_id', $valuepost->subforum_id)->where('deleted_at', null)->get());

                    $subforum_data['subforum_followers'] = $subforum_followers_count;
                    $subforum_data['post_count'] = $subforum_post_count;

                    $images = ForumImage::where('post_id', $valuepost->id)->get();
                    $videos = ForumVideo::where('post_id', $valuepost->id)->get();
                    //$upvoted = ForumUpvote::where('post_id', $valuepost->id)->where('user_id', Auth::id())->first();


                    if($subforum_following){
                        $follow_subforum = true;
                    }
                    else{
                        $follow_subforum = false;
                    }
                    if(!$check_upvote){
                        $upvote = false;
                    }
                    else{
                        $upvote = true;
                    }
                    
                    $valuepost->upvoted = $upvote;
                    

                    $valuepost->author = $author['username'];
                    $valuepost->author_photo =  "https://hainaservice.com/storage/".$author['photo'];
                    $valuepost->member_since = date("F Y", strtotime($author['created_at']));
                    $valuepost->like_count = $likes;
                    $valuepost->comment_count = count(ForumComment::where('post_id', $valuepost->id)->where('deleted_at', null)->get());
                    $valuepost->subforum_follow = $follow_subforum;
                    $valuepost->subforum_data = $subforum_data;
                    $valuepost->author_data = $author;
        
                    array_push($threads, $list_post[$keypost]);
                }

                $total = count($threads);
                $per_page = 10;
                $current_page = $request->page ?? 1;
        
                $starting_point = ($current_page * $per_page) - $per_page;
        
                //$result = $threads->offset(($current_page - 1) * $per_page)->limit($per_page)->get();
        
                $threads = array_slice($threads, $starting_point, $per_page);

                $result = new \stdClass();
                $result->threads = $threads;
                $result->total = $total;
                $result->current_page = (int)$current_page;
                $result->total_page = ceil($total/$per_page);

                if(count($threads) > 0){

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Home/following threads succesfully displayed!','data'=> $result]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'No posts found!','data'=> '']), 404);
                }
                
            }
            else{
                //return $this->showAllThreads($request);
                return $this->showHotThreads($request);
            }
        }
        else{
            //return $this->showAllThreads($request);
            return $this->showHotThreads($request);
        }
        
    }

    public function showSubforumData(Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{

            $check_subforum = Subforum::where('id', $request->subforum_id)->first();

            if($check_subforum){
                $result = new \stdClass();
                $followers_count = count(SubforumFollowers::where('subforum_id', $request->subforum_id)->get());

                $post = ForumPost::where('subforum_id', $request->subforum_id)->where('deleted_at', null)->get();

                $post_count = count($post);

                if($post_count == 0){
                    $likes = 0;
                    $views = 0;
                }
                else{
                    $likes = 0;
                    $views = 0;

                    foreach($post as $key => $value){
                        $views += $value->view_count;

                        $likes += count(ForumUpvote::where('post_id', $value->id)->get());
                    }
                }

                

                //dd(Auth::id());

                if ($request->bearerToken()) {
                    //dd(auth('sanctum')->user());
                    $check_followed = SubforumFollowers::where('subforum_id', $check_subforum['id'])->where('user_id', auth('sanctum')->user()->id)->first();
                    if($check_followed){
                        $result->following = true;
                    }
                    else{
                        $result->following = false;
                    }

                    $checkmod = ForumMod::where('user_id', auth('sanctum')->user()->id)->where('subforum_id', $request->subforum_id)->first();
                    $checkban = ForumBan::where('subforum_id', $request->subforum_id)->where('user_id', auth('sanctum')->user()->id)->first();
                    
                    if($checkban){
                        $result->role = "banned";
                    }
                    else if($checkmod){
                        $result->role = $checkmod['role'];
                    }
                    else{
                        $result->role = "none";
                    }
                }
            
                
                $result->subforum_id = $check_subforum['id'];
                $result->subforum_name = $check_subforum['name'];
                $result->description = $check_subforum['description'];
                $result->image = $check_subforum['subforum_image'];
                $result->followers_count = $followers_count;
                $result->post_count = $post_count;
                $result->likes = $likes;
                $result->views = $views;

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Subforum data found!','data'=> $result]), 200);

            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Subforum not found!','data'=> '']), 404);
            }
            
            
        }
    }

    public function showProfile(Request $request){
        $validator = Validator::make($request->all(), [
            //'subforum_id' => 'required',
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check_user = User::where('id', $request->user_id)->first();

            if($check_user){
                $post_count = count(ForumPost::where('user_id', $request->user_id)->where('deleted_at', null)->get());
                //$following = count(ForumFollowers::where('follower_id', $request->user_id)->get());
                //$followers = count(ForumFollowers::where('user_id', $request->user_id)->get());
                //$check_followed = ForumFollowers::where('user_id', $request->user_id)->where('follower_id', Auth::id())->first();

                /*
                if($check_followed){
                    $followed = true;
                }
                else{
                    $followed = false;
                }
                */

                $profile = (object)[
                    'user_id' => $check_user['id'],
                    'username' => $check_user['username'],
                    'member_since' => date("F Y", strtotime($check_user['created_at'])),
                    'photo' => "https://hainaservice.com/storage/".$check_user['photo'],
                    'post_count' => $post_count
                    //'following' => $following,
                    //'followers' => $followers,
                    //'followed' => $followed
                ];

                return response()->json(new ValueMessage(['value'=>1,'message'=>'User Profile Found!','data'=> $profile]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'User not found!','data'=> '']), 404);
            }
        }
    }

    public function removeBan(Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required',
            'user_id' => 'required',
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
                $check_user = ForumBan::where('user_id', $request->user_id)->where('subforum_id', $request->subforum_id)->first();

                if($check_user){
                    $remove_ban = ForumBan::where('user_id', $request->user_id)->where('subforum_id', $request->subforum_id)->delete();
                    $subforum = Subforum::where('id', $request->subforum_id)->first();

                    $user = User::where('id', $check_user['user_id'])->first();
                    $mod = User::where('id', Auth::id())->first();

                    $forumlog = ForumLog::create([
                        'subforum_id' => $request->subforum_id,
                        'forum_action' => 'MOD',
                        'message' => $mod['username'].' unbanned '.$user['username'].' from '.$subforum['name'].'.'
                    ]);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Remove Ban Success!','data'=> $check_user]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'User not found!','data'=> '']), 404);
                }
            }
        }
    }

    public function myRoles(){
        $checkmod = ForumMod::where('user_id', Auth::id())->where('role', 'mod')->get();

        $checksubmod = ForumMod::where('user_id', Auth::id())->where('role', 'submod')->get();

        $role = new \stdClass();
        $modlist = [];
        $submodlist = [];

        foreach($checkmod as $key => $value){
            //rolegw
            $subforums = Subforum::where('id', $value->subforum_id)->first();

            if($subforums){
                $creator_count = [];

                $subforums['post_count'] = count(ForumPost::where('subforum_id', $subforums['id'])->where('deleted_at', null)->get());

                $category_name = ForumCategory::where('id', $subforums['category_id'])->first();

                $subforums['category'] = $category_name['name'];
                $subforums['category_zh'] = $category_name['name_zh'];
                $subforums['role'] = "mod";

                $subforum_creator = User::where('id', $subforums['creator_id'])->first();
                $subforums['creator_username'] = $subforum_creator['username'];

                $post = ForumPost::where('subforum_id', $subforums['id'])->where('deleted_at', null)->get();
                foreach($post as $keypost => $valuepost){
                    array_push($creator_count, $valuepost->user_id);
                }

                $check_followed = SubforumFollowers::where('subforum_id', $subforums['id'])->where('user_id', Auth::id())->first();
                if($check_followed){
                    $subforums['followed'] = true;
                }
                else{
                    $subforums['followed'] = false;
                }

                $total_poster = array_unique($creator_count);
                $subforums['total_poster'] = count($total_poster);


                array_push($modlist, $subforums);
            }
            
        }
        
        foreach($checksubmod as $keys => $values){
            $subforums_submod = Subforum::where('id', $values->subforum_id)->first();


            if($subforums_submod){

                $creator_count = [];

                $subforums_submod['post_count'] = count(ForumPost::where('subforum_id', $subforums_submod['id'])->where('deleted_at', null)->get());

                $category_name = ForumCategory::where('id', $subforums_submod['category_id'])->first();

                $subforums_submod['category'] = $category_name['name'];
                $subforums_submod['category_zh'] = $category_name['name_zh'];
                $subforums_submod['role'] = "submod";

                $subforum_creator = User::where('id', $subforums_submod['creator_id'])->first();
                $subforums_submod['creator_username'] = $subforum_creator['username'];

                $post = ForumPost::where('subforum_id', $subforums_submod['id'])->where('deleted_at', null)->get();
                foreach($post as $keypost => $valuepost){
                    array_push($creator_count, $valuepost->user_id);
                }

                $check_followed = SubforumFollowers::where('subforum_id', $subforums_submod['id'])->where('user_id', Auth::id())->first();
                if($check_followed){
                    $subforum['followed'] = true;
                }
                else{
                    $subforum['followed'] = false;
                }
                

                $total_poster = array_unique($creator_count);
                $subforums_submod['total_poster'] = count($total_poster);


                array_push($submodlist, $subforums_submod);

            }
        }

        
        $role->mod_list = $modlist;
        $role->submod_list = $submodlist;

        if(count($modlist) == 0 &&  count($submodlist) == 0){
            return(response()->json(new ValueMessage(['value'=>0,'message'=>'No Mod Role!','data'=> '']), 404));
        }
        else{
            return(response()->json(new ValueMessage(['value'=>1,'message'=>'Get Mod Role Success!','data'=> $role]), 200));
        }

    }

    public function myBans(){
        $checkban = ForumBan::where('user_id', Auth::id())->get();

        $banlist = [];

        if($checkban){
            foreach($checkban as $key => $value){
                $subforum = Subforum::where('id', $value->subforum_id)->first();

                $creator_count = [];
                $check_followed = SubforumFollowers::where('subforum_id', $subforum['id'])->where('user_id', Auth::id())->first();

                $value->post_count = count(ForumPost::where('subforum_id', $subforum['id'])->where('deleted_at', null)->get());

                $category_name = ForumCategory::where('id', $subforum['category_id'])->first();

                $subforum['category'] = $category_name['name'];
                $subforum['category_zh'] = $category_name['name_zh'];

                $subforum_creator = User::where('id', $subforum['creator_id'])->first();
                $subforum['creator_username'] = $subforum_creator['username'];

                $post = ForumPost::where('subforum_id', $subforum['id'])->where('deleted_at', null)->get();
                foreach($post as $keypost => $valuepost){
                    array_push($creator_count, $valuepost->user_id);
                }

                $total_poster = array_unique($creator_count);
                $subforum['total_poster'] = count($total_poster);

                $subforum['ban_reason'] = $value->reason;

                if($check_followed){
                    $subforum['followed'] = true;
                }
                else{
                    $subforum['followed'] = false;
                }


                array_push($banlist, $subforum);
            }

            return(response()->json(new ValueMessage(['value'=>1,'message'=>'Get Ban List Success!','data'=> $banlist]), 200));
        }
        else{
            return(response()->json(new ValueMessage(['value'=>0,'message'=>'No Bans!','data'=> '']), 404));
        }
    }

    public function assignMod(Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required',
            'user_id' => 'required',
            'role' => 'in:mod,submod'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $checkmod = ForumMod::where('user_id', Auth::id())->where('subforum_id', $request->subforum_id)->first();

            if(!$checkmod){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
            }
            else if($checkmod['role'] == $request->role && $request->user_id == Auth::id()){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'You are already in this role!','data'=> '']), 403);
            }
            else{
                $check_candidate = ForumMod::where('user_id', $request->user_id)->where('subforum_id', $request->subforum_id)->first();

                if($check_candidate){
                    $update_mod = ForumMod::where('user_id', $request->user_id)->where('subforum_id', $request->subforum_id)->update(
                        [
                            'role' => $request->role
                        ]
                    );

                    $user = User::where('id', $check_candidate['user_id'])->first();
                    $mod = User::where('id', Auth::id())->first();
                    $subforum = Subforum::where('id', $request->subforum_id)->first();

                    $forumlog = ForumLog::create([
                        'subforum_id' => $request->subforum_id,
                        'forum_action' => 'MOD',
                        'message' => $mod['username'].' changed '.$user['username'].' mod role to '.$request->role.' in .'.$subforum['name'].'.'
                    ]);

                    $check = SubforumFollowers::where('user_id', $request->user_id)->where('subforum_id', $request->subforum_id)->first();

                    if(!$check){
                        $new_follow_subforum = SubforumFollowers::create([
                            'subforum_id' => $request->subforum_id,
                            'user_id' => $request->user_id
                        ]);

                    }

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Update Mod Success!','data'=> $forumlog]), 200);

                }
                else{
                    $new_mod = ForumMod::create([
                        'user_id' => $request->user_id,
                        'role' => $request->role,
                        'subforum_id' => $request->subforum_id
                    ]);

                    $user = User::where('id', $request->user_id)->first();
                    $mod = User::where('id', Auth::id())->first();
                    $subforum = Subforum::where('id', $request->subforum_id)->first();

                    $forumlog = ForumLog::create([
                        'subforum_id' => $request->subforum_id,
                        'forum_action' => 'MOD',
                        'message' => $mod['username'].' assigned '.$user['username'].' with a mod role as '.$request->role.' in '.$subforum['name'].'.'
                    ]);

                    $check = SubforumFollowers::where('user_id', $request->user_id)->where('subforum_id', $request->subforum_id)->first();

                    if(!$check){
                        $new_follow_subforum = SubforumFollowers::create([
                            'subforum_id' => $request->subforum_id,
                            'user_id' => $request->user_id
                        ]);

                    }

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Assign New Mod Success!','data'=> $new_mod]), 200);
                }
                
            }
        }
    }

    public function removeMod (Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $checkmod = ForumMod::where('user_id', Auth::id())->where('subforum_id', $request->subforum_id)->where('role', 'mod')->first();

            if(!$checkmod){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
            }
            else{
                $check_candidate = ForumMod::where('user_id', $request->user_id)->where('subforum_id', $request->subforum_id)->where('role', 'submod')->first();

                if($check_candidate){
                    $delete_mod = ForumMod::where('user_id', $request->user_id)->where('subforum_id', $request->subforum_id)->where('role', 'submod')->delete();

                    $user = User::where('id', $check_candidate['user_id'])->first();
                    $mod = User::where('id', Auth::id())->first();
                    $subforum = Subforum::where('id', $request->subforum_id)->first();

                    $forumlog = ForumLog::create([
                        'subforum_id' => $request->subforum_id,
                        'forum_action' => 'MOD',
                        'message' => $mod['username'].' removed '.$user['username'].' mod role in '.$subforum['name'].'.'
                    ]);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Delete Mod Success!','data'=> $forumlog]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'User is not a mod!','data'=> '']), 404);
                }
            }
        }
    }

    public function showBanList(Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check_subforum = Subforum::where('id', $request->subforum_id)->first();

            if($check_subforum != null){
                $checkmod = ForumMod::where('user_id', Auth::id())->where('subforum_id', $request->subforum_id)->first();

                if(!$checkmod){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else{
                    $banlist = ForumBan::where('subforum_id', $request->subforum_id)->get();

                    foreach($banlist as $key => $value){
                        $username = User::where('id', $value->user_id)->first();

                        $user_data = [
                            'name' => $username['fullname'],
                            'username' => $username['username'],
                            'photo' => "https://hainaservice.com/storage/".$username['photo']
                        ];

                        $mod = ForumMod::where('id', $value->mod_id)->first();
                        $mod_username = User::where('id', $mod['user_id'])->first();

                        $mod_data = [
                            'role' => $mod['role'],
                            'subforum_id' => $mod['subforum_id'],
                            'username' => $mod_username['username'],
                            'photo' => "https://hainaservice.com/storage/".$mod_username['photo']
                        ];

                        $value->user = $user_data;
                        $value->mod = $mod_data;

                    }

                    if(count($banlist) > 0){
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Banlist Success!','data'=> $banlist]), 200);
                    }
                    else{
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Banlist empty!','data'=> '']), 404);
                    }
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Subforum Not Found!','data'=> '']), 404);
            }
        }
    }

    public function searchForumFollowers(Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required',
            'keyword' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $keyword = "";
            if($request->keyword){
                $keyword = str_replace(['%','\\'],'',$request->keyword);
            }
            
            $result = [];

            $get_followers = SubforumFollowers::where('subforum_id', $request->subforum_id)->get();

            foreach($get_followers as $key => $value){
                $check_user_id = User::where('id', $value->user_id)->first();

                if($check_user_id != null){
                    if(($request->keyword != null && strstr($check_user_id['username'], $keyword) != false) || $request->keyword == null){
                        $value->username = $check_user_id['username'];
                        $value->photo = "https://hainaservice.com/storage/".$check_user_id['photo'];
                        $value->member_since = date("F Y", strtotime($check_user_id['created_at']));
                        array_push($result, $get_followers[$key]);
                    }
                    
                }
            }

            if(count($result) > 0){
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Search Subforum Followers Success!','data'=> $result]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Subforum Followers Not Found!','data'=> '']), 404);
            }
        }
    }

    public function addPostBookmark(Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_post = ForumPost::where('id', $request->post_id)-> where('deleted_at', null)->first();

            if(!$check_post){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Not Found!','data'=> '']), 404);
            }
            else{
                $check_bookmark = ForumBookmark::where('post_id', $request->post_id)->where('user_id', Auth::id())->first();

                if($check_bookmark != null){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Post is already bookmarked!','data'=> '']), 404);
                }
                else{
                    $check_post->forum_bookmark()->attach(Auth::id());

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Bookmark post success!','data'=> ['user_id' => Auth::id(), 'post_id' => $request->post_id, 'post_title' => $check_post['title']]]), 200);
                }
            }
        }
    }

    public function removePostBookmark(Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_post = ForumPost::where('id', $request->post_id)-> where('deleted_at', null)->first();

            if(!$check_post){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Not Found!','data'=> '']), 404);
            }
            else{
                $bookmark_status = ForumBookmark::where('post_id', $request->post_id)->where('user_id', Auth::id())->first();

                if($bookmark_status != null){
                    $check_post->forum_bookmark()->detach(Auth::id());

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Remove bookmark post success!','data'=> ['user_id' => Auth::id(), 'post_id' => $request->post_id, 'post_title' => $check_post['title']]]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'You do not bookmark this post!','data'=> '']), 404);
                }
            }

        }
    }

    public function showPostBookmark(){
        $check_bookmark = ForumBookmark::where('user_id', Auth::id())->get();
        $result = [];
        if(count($check_bookmark) > 0){
            foreach($check_bookmark as $key => $value){
                $post = ForumPost::where('id', $value->post_id)->with('images', 'videos')->first();
    
                $author = User::where('id', $post['user_id'])->first();
                $likes = count(ForumUpvote::where('post_id', $post['id'])->get());
                $comments = count(ForumComment::where('post_id', $post['id'])->where('deleted_at', null)->get());
    
                $check_upvote = ForumUpvote::where('post_id', $post['id'])->where('user_id', Auth::id())->first();
                $subforum = Subforum::where('id', $post['subforum_id'])->first();
    
                $post['author'] = $author['username'];
                $post['likes'] = $likes;
                $post['comments'] = $comments;
                $post['subforum_data'] = $subforum;
                
                if($check_upvote){
                    $post['upvoted'] = true;
                }
                else{
                    $post['upvoted'] = false;
                }

                array_push($result, $post);
            }

            $ordered_result = collect($result)->sortByDesc('created_at')->toArray();

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Show Bookmarked Post Success!','data'=>$ordered_result]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'No Bookmarked Posts Found!','data'=>'']), 404);
        }
        
    }

    public function updateSubforumData (Request $request){
        $validator = Validator::make($request->all(), [
            'subforum_id' => 'required',
            'image' => 'image|mimes:png,jpg|max:1024|nullable',
            'name' => 'min:3|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_subforum = Subforum::where('id', $request->subforum_id)->first();

            if($check_subforum){
                $checkmod = ForumMod::where('user_id',Auth::id())->where('subforum_id', $request->subforum_id)->where('role', 'mod')->first();

                if($checkmod){
                    if($request->image != null){
                        //$path=public_path().str_replace('http://hainaservice.com/storage/','/',$check_subforum['subforum_image']);
                        //unlink($path);

                        if (File::exists(public_path(str_replace('http://hainaservice.com/storage/','',$check_subforum['subforum_image'])))) {
                            File::delete(public_path(str_replace('http://hainaservice.com/storage/','',$check_subforum['subforum_image'])));
                        }

                        $files = $request->file('image');
                        
                        $fileName = str_replace(' ','-', $check_subforum['id'].'-'.($request->name ?? $check_subforum['name']).'-'.'picture'.'-'.date('Ymd'));
                        $guessExtension = $files->guessExtension();
                        
                        $store = Storage::disk('public')->putFileAs('forum/subforum', $files ,$fileName.'.'.$guessExtension);


                        $update_image = Subforum::where('id', $check_subforum['id'])->update([
                            'name' => $request->name ?? $check_subforum['name'],
                            'description' => $request->description ?? $check_subforum['description'],
                            'subforum_image' => 'http://hainaservice.com/storage/'.$store
                        ]);

                        $user = User::where('id', Auth::id())->first();

                        $forumlog = ForumLog::create([
                            'subforum_id' => $check_subforum['id'],
                            'forum_action' => 'MOD',
                            'message' => $user['username'].' updated "'.$request->name ?? $check_subforum['name'].'" and the subforum image.'
                        ]);
                    }
                    else{
                        $update_image = Subforum::where('id', $check_subforum['id'])->update([
                            'name' => $request->name ?? $check_subforum['name'],
                            'description' => $request->description ?? $check_subforum['description']
                        ]);

                        $user = User::where('id', Auth::id())->first();

                        $forumlog = ForumLog::create([
                            'subforum_id' => $check_subforum['id'],
                            'forum_action' => 'MOD',
                            'message' => $user['username'].' updated "'.$request->name ?? $check_subforum['name'].'" subforum.'
                        ]);
                    }

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Update subforum data success!','data'=>$forumlog]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized: You are not a mod of this subforum.','data'=> '']), 401);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Subforum not found!','data'=> '']), 404);
            }
        }
    }

}
