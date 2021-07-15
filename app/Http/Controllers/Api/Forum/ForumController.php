<?php

namespace App\Http\Controllers\Api\Forun;

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

    public function createPost(Request $request){

    }

}