<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\CommunityPostAttachment;
use App\Models\CommunityPostLike;
use App\Models\CommunityReply;
use App\Models\Material;
use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CommunityController extends Controller
{
    /** Extensions alumni are allowed to attach to a post. */
    private const ALLOWED_EXTENSIONS = 'pdf,doc,docx,ppt,pptx,xls,xlsx,csv,txt,rtf,odt,jpg,jpeg,png,gif,webp,zip,rar';

    /**
     * The Alumni Community feed — shared "group page" viewed by alumni and
     * students alike. Alumni can post updates/quotes/materials; everyone can
     * like and comment.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $posts = CommunityPost::with(['author.alumniProfile', 'subject', 'attachments', 'likes', 'replies.author'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(10);

        $unreadNotifications = DB::table('notifications')
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('community.index', [
            'posts' => $posts,
            'subjects' => Subject::where('is_active', true)->orderBy('code')->get(),
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    /**
     * Publish a new post/quote, optionally with one file attachment.
     * Only alumni (and the chair, for moderation/announcements) may post.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAlumni() || $user->isChair(), 403, 'Only alumni can post to the community.');

        $data = $request->validate([
            'post_type'  => ['required', 'in:discussion,tip'],
            'body'       => ['required', 'string', 'max:3000'],
            'title'      => ['nullable', 'string', 'max:200'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'file'       => ['nullable', 'file', 'mimes:' . self::ALLOWED_EXTENSIONS, 'max:20480'],
        ]);

        $post = CommunityPost::create([
            'author_id'  => $user->id,
            'subject_id' => $data['subject_id'] ?? null,
            'title'      => $data['title'] ?? null,
            'body'       => $data['body'],
            'post_type'  => $data['post_type'],
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());

            CommunityPostAttachment::create([
                'community_post_id' => $post->id,
                'file_path'         => $file->store('community-posts', 'public'),
                'original_name'     => $file->getClientOriginalName(),
                'file_size'         => $file->getSize(),
                'file_category'     => Material::categoryFor($extension),
            ]);
        }

        $this->notifyStudentsOfNewPost($post);

        return back()->with('status', $data['post_type'] === 'tip' ? 'Quote shared to the community.' : 'Post published.');
    }

    /**
     * Delete a post (author or chair only) and any attached file.
     */
    public function destroy(CommunityPost $post)
    {
        $user = Auth::user();
        abort_unless($user->isChair() || $post->author_id === $user->id, 403);

        foreach ($post->attachments as $attachment) {
            if ($attachment->file_path) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        }

        $post->delete();

        return back()->with('status', 'Post deleted.');
    }

    /**
     * Toggle a like on a post for the current user.
     */
    public function toggleLike(CommunityPost $post)
    {
        $user = Auth::user();

        $like = CommunityPostLike::where('community_post_id', $post->id)->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
        } else {
            CommunityPostLike::create(['community_post_id' => $post->id, 'user_id' => $user->id]);
        }

        return back();
    }

    /**
     * Add a comment to a post. Anyone in the community (alumni/student/chair) can comment.
     */
    public function storeComment(Request $request, CommunityPost $post)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        CommunityReply::create([
            'post_id'   => $post->id,
            'author_id' => Auth::id(),
            'body'      => $data['body'],
        ]);

        return back()->with('status', 'Comment posted.');
    }

    /**
     * Delete a comment (author or chair only).
     */
    public function destroyComment(CommunityReply $comment)
    {
        abort_unless($comment->author_id === Auth::id(), 403);

        $comment->delete();

        return back()->with('status', 'Comment deleted.');
    }

    /**
     * Stream/redirect to a post attachment for download.
     */
    public function download(CommunityPostAttachment $attachment)
    {
        abort_unless($attachment->file_path && Storage::disk('public')->exists($attachment->file_path), 404);

        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }

    /**
     * Let every active student know an alumnus posted, mirroring the
     * chair→student broadcast fan-out pattern used for communications.
     */
    private function notifyStudentsOfNewPost(CommunityPost $post): void
    {
        $author = $post->author?->name ?? 'An alumnus';
        $title = $post->post_type === 'tip' ? "{$author} shared a quote" : "{$author} posted in the Alumni Community";

        $recipients = User::where('role_id', Role::STUDENT)->where('is_active', true)->pluck('id');

        if ($recipients->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $recipients->map(fn ($id) => [
            'recipient_id' => $id,
            'sender_id'    => $post->author_id,
            'type'         => 'alumni_post',
            'title'        => $title,
            'message'      => str($post->body)->limit(120)->toString(),
            'link'         => '/community',
            'is_read'      => false,
            'created_at'   => $now,
            'updated_at'   => $now,
        ])->all();

        DB::table('notifications')->insert($rows);
    }
}
