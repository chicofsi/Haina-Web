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
use App\Models\ForumCategory;
use App\Models\Subforum;
use App\Models\SubforumFollowers;
use App\Models\ForumBan;
use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumFollowers;
use App\Models\ForumImage;
use App\Models\ForumLog;
use App\Models\ForumVideo;
use App\Models\ForumMod;
use App\Models\ForumUpvote;

use App\Http\Controllers\Api\Notification\NotificationController;

use DateTime;

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
                    'name' => $request->name,
                    'description' => $request->description,
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
                    'message' => $user['username'].' created '.$new_subforum->name.' subforum.'
                ]);

                $modlog = ForumLog::create([
                    'subforum_id' => $new_subforum->id,
                    'forum_action' => 'MOD',
                    'message' => $user['username'].' is the new mod of '.$new_subforum->name.' subforum.'
                ]);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Subforum successfully created!','data'=> $new_subforum]), 200);
            }
        }

    }

    public function showMySubforum(){
            $check = Subforum::where('creator_id', Auth::id())->with('posts')->get();

            if(count($check) != 0){
               
                foreach($check as $key => $value){
                    $creator_count = [];

                    $value->total_post = count(ForumPost::where('subforum_id', $value->id)->get());

                    $category_name = ForumCategory::where('id', $value->category_id)->first();

                    $value->category = $category_name['name'];
                    $value->category_zh = $category_name['name_zh'];

                    $post = ForumPost::where('subforum_id', $value->id)->get();
                    foreach($post as $keypost => $valuepost){
                        array_push($creator_count, $valuepost->user_id);
                    }

                    $total_poster = array_unique($creator_count);
                    $value->total_poster = count($total_poster);

                    foreach($value->posts as $keypost => $valuepost){
                        $author = User::where('id', $valuepost->user_id)->first();

                        $likes = count(ForumUpvote::where('post_id', $valuepost->id)->get());

                        $check_comment = ForumComment::where('post_id', $valuepost->id)->orderBy('created_at', 'desc')->first();
            
                        $author = User::where('id', $valuepost->user_id)->first();
            
                        $check_upvote = ForumUpvote::where('post_id', $valuepost->id)->where('user_id', Auth::id())->first();
            
                        $subforum_data = Subforum::where('id', $valuepost->subforum_id)->first();
                        $subforum_following = SubforumFollowers::where('user_id', $valuepost->user_id)->where('subforum_id', $valuepost->subforum_id)->first();
            
                        if($subforum_following){
                            $follow_subforum = true;
                        }
                        else{
                            $follow_subforum = false;
                        }
                        $valuepost->author = $author['username'];
                        $valuepost->author_photo =  "https://hainaservice.com/storage/".$author['photo'];
                        $valuepost->member_since = date("F Y", strtotime($author['created_at']));
                        $valuepost->likes = $likes;
                        $valuepost->comment_count = count(ForumComment::where('post_id', $valuepost->id)->get());
                        $valuepost->subforum_follow = $follow_subforum;
                        $valuepost->subforum_data = $subforum_data;
                        $valuepost->author_data = $author;
                    }
                }

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Subforum found!','data'=> $check]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No subforum found!','data'=> '']), 404);
            }
    }

    public function showMyPost(){
        $check = Subforum::select('id')->where('creator_id', '<>', Auth::id())->get();
        //dd($check);
        //$mypost = [];

        if(count($check) != 0){
                $post = ForumPost::whereNotIn('subforum_id', $check)->where('user_id', Auth::id())->with('images', 'videos')->get();

                foreach($post as $key => $value){
                    $author = User::where('id', $value->user_id)->first();

                    $likes = count(ForumUpvote::where('post_id', $value->id)->get());

                    $check_comment = ForumComment::where('post_id', $value->id)->orderBy('created_at', 'desc')->first();
        
                    $author = User::where('id', $value->user_id)->first();
        
                    $check_upvote = ForumUpvote::where('post_id', $value->id)->where('user_id', Auth::id())->first();
        
                    $subforum_data = Subforum::where('id', $value->subforum_id)->first();
                    $subforum_following = SubforumFollowers::where('user_id', $value->user_id)->where('subforum_id', $value->subforum_id)->first();
        
                    if($subforum_following){
                        $follow_subforum = true;
                    }
                    else{
                        $follow_subforum = false;
                    }
                    $value->author = $author['username'];
                    $value->author_photo =  "https://hainaservice.com/storage/".$author['photo'];
                    $value->member_since = date("F Y", strtotime($author['created_at']));
                    $value->likes = $likes;
                    $value->comment_count = count(ForumComment::where('post_id', $value->id)->get());
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
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'No post found!','data'=> '']), 404);
        }
    }

    public function showAllSubforum(){

            $check = Subforum::all();

            if(count($check) != 0){
                foreach($check as $key => $value){
                    $check_followed = SubforumFollowers::where('subforum_id', $value->id)->where('user_id', Auth::id())->first();

                    if($check_followed){
                        $value->followed = true;
                    }
                    else{
                        $value->followed = false;
                    }
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
                $list_post = ForumPost::with('comments', 'images', 'videos')->all();
            }
            else{
                $list_post = ForumPost::where('subforum_id', $request->subforum_id)->with('comments', 'images', 'videos')->get();
            }
            

            $threads = [];

            foreach($list_post as $key => $value){
                $likes = count(ForumUpvote::where('post_id', $value->id)->get());

                $lastpost = null;
                $check_comment = ForumComment::where('post_id', $value->id)->orderBy('created_at', 'desc')->first();

                $author = User::where('id', $value->user_id)->first();

                if(!$check_comment){
                    $lastpost = $value->updated_at;
                }
                else{
                    $lastpost = $check_comment['created_at'];
                }

                $check_upvote = ForumUpvote::where('post_id', $value->id)->where('user_id', Auth::id())->first();

                if(!$check_upvote){
                    $upvote = false;
                }
                else{
                    $upvote = true;
                }

                $prelist = [
                    'id' => $value->id,
                    'title' => $value->title,
                    'author' => $author['username'],
                    'user_id' => $author['id'],
                    'author_photo' => "https://hainaservice.com/storage/".$author['photo'],
                    'member_since' => date("F Y", strtotime($author['created_at'])),
                    'like_count' => $likes,
                    'comment_count' => count(ForumComment::where('post_id', $value->id)->get()),
                    'view_count' => $value->view_count,
                    'share_count' => $value->share_count,
                    'created' => $value->created_at,
                    'upvoted' => $upvote,
                    'content' => $value->content,
                    'images' => $value->images,
                    'videos' => $value->videos,
                    'last_update' => $lastpost
                ];

                if($prelist->user_id != Auth::id()){
                    array_push($prelist, ['upvoted' => $upvote]);
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
            

            if(count($threads) == 0){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No threads found!','data'=> '']), 404);
            }
            else{
                //$object = new stdClass;
                //$threads->followed = SubforumFollower

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Threads displayed successfully!','data'=> $threads]), 200);
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
            $post_comment = ForumComment::where('post_id', $request->post_id)->get();

            foreach($post_comment as $key => $value){
                $userdata = User::where('id',$value->user_id)->first();

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

    public function showPost (Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $post_detail = ForumPost::where('id', $request->post_id)->with('images', 'videos')->first();

            if(!$post_detail){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Post not found!','data'=> '']), 404);
            }
            else{
                $add_view = $post_detail['view_count'] + 1;

                $update_view = $post_detail->update([
                    'view_count' => $add_view
                ]);

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

            $check = ForumPost::where('subforum_id', $request->subforum_id)->where('user_id', Auth::id())->where('title', $request->title)->first();
            $check_ban = ForumBan::where('subforum_id', $request->subforum_id)->where('user_id', Auth::id())->first();

            if($check_ban){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'You are banned in this subforum!','data'=> '']), 401);
            }
            else if($check){
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
                    $video = $request->file('video');
                    $this->storeVideo($new_post->id, $video);
                }

                $user = User::where('id', $new_post->user_id)->first();
                $subforum = Subforum::where('id', $new_post->subforum_id)->first();

                $forumlog = ForumLog::create([
                    'subforum_id' => $subforum['id'],
                    'forum_action' => 'POST',
                    'message' => $user['username'].' created '.$new_post->title.' in '.$subforum['name'].'.'
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
            $checkpost = ForumPost::where('id', $request->post_id)->first();
            $checkcomment = ForumComment::where('post_id', $request->post_id)->get();
            $checkimage = ForumImage::where('post_id', $request->post_id)->get();
            $checkvideo = ForumVideo::where('post_id', $request->post_id)->get();
            $checkupvote = ForumUpvote::where('post_id', $request->post_id)->get();

            $subforum = ForumPost::select('subforum_id')->where('id',$checkpost['id'])->first();
            $checkmod = ForumMod::where('user_id', Auth::id())->where('subforum_id', $subforum['subforum_id'])->first();

            if(!$checkpost){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Not Found!','data'=> '']), 404);
            }
            else if($checkpost['user_id'] != Auth::id() && !$checkmod){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
            }
            else{
                if($checkcomment){
                    $delete_comment = ForumComment::where('post_id', $request->post_id)->delete();
                }
                if($checkimage){
                    foreach($checkimage as $key => $value){
                        $path = str_replace("http://hainaservice.com/storage", "", $value->path);

                        Storage::disk('public')->delete($path);
                    }

                    $delete_image = ForumImage::where('post_id', $request->post_id)->delete();
                }
                if($checkvideo){
                    foreach($checkvideo as $key => $value){
                        $path = str_replace("http://hainaservice.com/storage", "", $value->path);

                        Storage::disk('public')->delete($path);
                    }

                    $delete_video = ForumVideo::where('post_id', $request->post_id)->delete();
                }
                if($checkupvote){
                    $delete_upvote = ForumUpvote::where('post_id', $request->post_id)->delete();
                }

                if($checkmod){
                    //hapus by mod
                    $post_owner = ForumPost::where('id', $request->post_id)->first();
                    $token = [];
                    $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $post_owner['user_id'])->get();

                    foreach($usertoken as $key => $value){
                        array_push($token, $value); 
                    }

                    NotificationController::sendPush($token, "Your post is removed", "Your post ".$post_owner['title']."is removed by a moderator.", "Forum", "delete");
                }
                $delete_post = ForumPost::where('id', $request->post_id)->delete();

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Post deleted successfully','data'=> $checkpost]), 200);
            
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
                    'message' => $user['username'].' commented in '.$post['title'].'.'
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
            $check = ForumComment::where('id', $request->comment_id)->first();
            $subforum = ForumPost::select('subforum_id')->where('id',$check['post_id'])->first();
            $checkmod = ForumMod::where('user_id', Auth::id())->where('subforum_id', $subforum['subforum_id'])->first();

            if(!$check){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Comment Not Found!','data'=> '']), 404);
            }
            else if($check['user_id'] != Auth::id() && !$checkmod){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
            }
            else{
                if($checkmod){
                    //hapus by mod
                    $comment_owner = ForumComment::where('id', $request->comment_id)->first();
                    $post_name = ForumPost::where('id', $comment_owner['post_id'])->first();
                    $token = [];
                    $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $comment_owner['user_id'])->get();

                    foreach($usertoken as $key => $value){
                        array_push($token, $value); 
                    }

                    NotificationController::sendPush($token, "Your comment is removed", "Your comment at".$post_name['title']."is removed by a moderator.", "Forum", "delete");
                }

                $delete_comment = ForumComment::where('id', $request->comment_id)->delete();

                return response()->json(new ValueMessage(['value'=>0,'message'=>'Comment deleted successfully!','data'=> $check]), 200);
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

            $thread = ForumPost::where('title', 'like', '%'.$request->keyword.'%')->get();

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
            $check = ForumPost::where('id', $request->post_id)->first();
            

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

                    if($check['user_id'] == Auth::id()){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Cannot Upvote Own Post!','data'=> '']), 401);
                    }
                    else if($check_duplicate){
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
            $check = ForumPost::where('id', $request->post_id)->first();
            
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
            $check = ForumPost::where('id', $request->post_id)->first();
            
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
                $subforum = Subforum::where('id',$value->subforum_id)->first();

                $user_list = [
                    'subforum_id' => $subforum['id'],
                    'name' => $subforum['name'],
                    'description' => $subforum['description'],
                    'image' => $subforum['subforum_image'],
                    'creator_id' => $subforum['creator_id']
                ];

                array_push($list_follow, $user_list);
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

                    $check_followed = SubforumFollowers::where('subforum_id', $value->id)->where('user_id', Auth::id)->first();
                    if($check_followed){
                        $followed = true;
                    }
                    else{
                        $followed = false;
                    }

                    $user_list = [
                        'subforum_id' => $subforum['id'],
                        'name' => $subforum['name'],
                        'description' => $subforum['description'],
                        'image' => $subforum['subforum_image'],
                        'creator_id' => $subforum['creator_id'],
                        'followed_by_me' => $followed
                    ];

                    array_push($list_follow, $user_list);
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
                $username = User::select('username')->where('id', $value->user_id)->first();

                $value->username = $username['username'];
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

                    $banned = ForumBan::create([
                        'user_id' => $request->user_id,
                        'subforum_id' => $request->subforum_id,
                        'mod_id' => $check_mod['id'],
                        'reason' => $request->reason
                    ]);

                    $user = User::where('id', $banned->user_id)->first();
                    $mod = User::where('id', $banned->mod_id)->first();

                    $forumlog = ForumLog::create([
                        'subforum_id' => $banned->subforum_id,
                        'forum_action' => 'MOD',
                        'message' => $mod['username'].' banned '.$user['username'].'for '.$banned->reason.'.'
                    ]);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'User Ban Success!','data'=> $banned]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'User not found!','data'=> '']), 404);
                }
            }
        }
    }

    public function showHotThreads(Request $request){

        $list_post = ForumPost::with('comments', 'images', 'videos')->get();
        $hot_threads = [];
        $threads = [];

        foreach($list_post as $key => $value){
            $likes = count(ForumUpvote::where('post_id', $value->id)->get());

            $check_comment = ForumComment::where('post_id', $value->id)->orderBy('created_at', 'desc')->first();

            $author = User::where('id', $value->user_id)->first();

            $check_upvote = ForumUpvote::where('post_id', $value->id)->where('user_id', Auth::id())->first();

            if($check_upvote){
                $upvote = false;
            }
            else{
                $upvote = true;
            }

            /*
            $author_following = ForumFollowers::where('user_id', $author['id'])->where('follower_id', Auth::id())->first();
            if($author_following){
                $follow_author = true;
            }
            else if($author['id'] == Auth::id()){

            }
            else{
                $follow_author = false;
            }*/


            $subforum_data = Subforum::where('id', $value->subforum_id)->first();
            $subforum_following = SubforumFollowers::where('user_id', $request->user_id)->where('subforum_id', $value->subforum_id)->first();

            if($subforum_following){
                $follow_subforum = true;
            }
            else{
                $follow_subforum = false;
            }

            $prelist = [
                'id' => $value->id,
                'title' => $value->title,
                'author' => $author['username'],
                'user_id' => $author['id'],
                'author_photo' => "https://hainaservice.com/storage/".$author['photo'],
                'member_since' => date("F Y", strtotime($author['created_at'])),
                'like_count' => $likes,
                'comment_count' => count(ForumComment::where('post_id', $value->id)->get()),
                'view_count' => $value->view_count,
                'share_count' => $value->share_count,
                'created' => $value->created_at,
                'content' => $value->content,
                'images' => $value->images,
                'videos' => $value->videos,
                'subforum_follow' => $follow_subforum,
                'subforum_data' => $subforum_data,
                'author_data' => $author
            ];

            if($prelist->user_id != Auth::id()){
                array_push($prelist, ['upvoted' => $upvote]);
            }

            $list = (object) $prelist;

            array_push($threads, $list);

        }

        $like = array_column($threads, 'like_count');
        $comment = array_column($threads, 'comment_count');

        array_multisort($like, SORT_DESC, $comment, SORT_DESC, $threads);
        //dd($threads);
        $hot_threads = array_slice($threads, 0, 5);

        if(count($hot_threads) > 0){

            return response()->json(new ValueMessage(['value'=>1,'message'=>'User not found!','data'=> $hot_threads]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'No posts found!','data'=> '']), 404);
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
                $post_count = count(ForumPost::where('user_id', $request->user_id)->get());
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

                    $user = User::where('id', $check_user['id'])->first();
                    $mod = User::where('id', Auth::id())->first();

                    $forumlog = ForumLog::create([
                        'subforum_id' => $request->subforum_id,
                        'forum_action' => 'MOD',
                        'message' => $mod['username'].' unbanned '.$user['username'].'.'
                    ]);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Remove Ban Success!','data'=> $check_user]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'User not found!','data'=> '']), 404);
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

                    $user = User::where('id', $check_candidate['id'])->first();
                    $mod = User::where('id', Auth::id())->first();

                    $forumlog = ForumLog::create([
                        'subforum_id' => $request->subforum_id,
                        'forum_action' => 'MOD',
                        'message' => $mod['username'].' changed '.$user['username'].' mod role to '.$request->role.'.'
                    ]);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Update Mod Success!','data'=> $update_mod]), 200);

                }
                else{
                    $new_mod = ForumMod::create([
                        'user_id' => $request->user_id,
                        'role' => $request->role,
                        'subforum_id' => $request->subforum_id
                    ]);

                    $user = User::where('id', $check_candidate['id'])->first();
                    $mod = User::where('id', Auth::id())->first();

                    $forumlog = ForumLog::create([
                        'subforum_id' => $request->subforum_id,
                        'forum_action' => 'MOD',
                        'message' => $mod['username'].' assigned '.$user['username'].' with a mod role as '.$request->role.'.'
                    ]);

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

                    $user = User::where('id', $check_candidate['id'])->first();
                    $mod = User::where('id', Auth::id())->first();

                    $forumlog = ForumLog::create([
                        'subforum_id' => $request->subforum_id,
                        'forum_action' => 'MOD',
                        'message' => $mod['username'].' removed '.$user['username'].' mod role.'
                    ]);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Delete Mod Success!','data'=> $check_candidate]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'User is not a mod!','data'=> '']), 404);
                }
            }
        }
    }

}