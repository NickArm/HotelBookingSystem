<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function StoreComment(Request $request)
    {

        Comment::insert([
            'user_id' => $request->user_id,
            'post_id' => $request->post_id,
            'message' => $request->message,
            'created_at' => Carbon::now(),
        ]);

        $notification = [
            'message' => 'Comment Added Successfully Admin will approved',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);

    }

    public function AllComment()
    {

        $allcomment = Comment::latest()->get();

        return view('backend.comment.all_comment', compact('allcomment'));

    }

    public function UpdateCommentStatus(Request $request)
    {

        $commentId = $request->input('comment_id');
        $isChecked = $request->input('is_checked', 0);

        $comment = Comment::find($commentId);
        if ($comment) {
            $comment->status = $isChecked;
            $comment->save();
        }

        return response()->json(['message' => 'Comment Status Updated Successfully']);

    }// End Method
}
