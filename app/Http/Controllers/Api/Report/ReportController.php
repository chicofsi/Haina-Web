<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\ForumCategory;
use App\Models\Subforum;
use App\Models\ForumBan;
use App\Models\ForumBookmark;
use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumFollowers;
use App\Models\ForumLog;
use App\Models\ForumMod;
use App\Models\PersonalAccessToken;

use DateTime;

use App\Http\Resources\ValueMessage;

class ReportController extends Controller
{

}