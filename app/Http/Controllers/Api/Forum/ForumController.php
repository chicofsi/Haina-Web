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
use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumFollowers;
use App\Models\ForumImage;
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

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Subforum successfully created!','data'=> $new_subforum]), 200);
            }
        }

    }

    public function showMySubforum(){
            $check = Subforum::where('creator_id', Auth::id())->with('posts')->get();

            if(count($check) != 0){
                foreach($check as $key => $value){
                    $value->total_post = count(ForumPost::where('subforum_id', $value->id)->get());
                    
                }

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Subforum found!','data'=> $check]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No subforum found!','data'=> '']), 404);
            }
    }

    public function showMyPost(){
        $check = Subforum::where('creator_id', '<>', Auth::id())->get();
        $mypost = [];

        if(count($check) != 0){
            foreach($check as $key => $value){
                $post = ForumPost::where('subforum_id', $value->id)->with('images', 'videos')->get();

                if(count($post) > 0){
                    array_push($mypost, $post);
                }
                
            }

            if(count($mypost) == 0){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No post found!','data'=> '']), 404);
            }
            else{
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get My Post Success!','data'=> $mypost]), 200);
            }
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'No post found!','data'=> '']), 404);
        }
}

    public function showAllSubforum(Request $request){

        $validator = Validator::make($request->all(), [
            'category_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check = Subforum::where('category_id', $request->category_id)->get();

            if(count($check) != 0){
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Subforum found!','data'=> $check]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No subforum found!','data'=> '']), 404);
            }
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

                $list = (object) [
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
                    $video = $request->file('video');
                    $this->storeVideo($new_post->id, $video);
                }

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

    public function followUser(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check = ForumFollowers::where('user_id', $request->user_id)->where('follower_id', Auth::id())->first();

            if($check){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'You already followed this user!','data'=> '']), 404);
            }
            else{
                $new_follow = ForumFollowers::create([
                    'user_id' => $request->user_id,
                    'follower_id' => Auth::id()
                ]);
            }

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Follow user success!','data'=> $new_follow]), 200);
        }
    }

    public function unfollowUser(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check = ForumFollowers::where('user_id', $request->user_id)->where('follower_id', Auth::id())->first();

            if(!$check){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'You do not follow this user!','data'=> '']), 404);
            }
            else{
                $delete_follow = ForumFollowers::where('user_id', $request->user_id)->where('follower_id', Auth::id())->delete();

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Unfollow user success!','data'=> $check]), 200);
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

            $list = (object) [
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
            ];

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
                $following = count(ForumFollowers::where('follower_id', $request->user_id)->get());
                $followers = count(ForumFollowers::where('user_id', $request->user_id)->get());
                $check_followed = ForumFollowers::where('user_id', $request->user_id)->where('follower_id', Auth::id())->first();

                if($check_followed){
                    $followed = true;
                }
                else{
                    $followed = false;
                }

                $profile = (object)[
                    'user_id' => $check_user['id'],
                    'username' => $check_user['username'],
                    'member_since' => date("F Y", strtotime($check_user['created_at'])),
                    'photo' => "https://hainaservice.com/storage/".$check_user['photo'],
                    'post_count' => $post_count,
                    'following' => $following,
                    'followers' => $followers,
                    'followed' => $followed
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

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Delete Mod Success!','data'=> $check_candidate]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'User is not a mod!','data'=> '']), 404);
                }
            }
        }
    }

}