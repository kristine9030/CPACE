<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Material;
use App\Models\Message;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /** Extensions users are allowed to share in a chat. */
    private const ALLOWED_EXTENSIONS = 'pdf,doc,docx,ppt,pptx,xls,xlsx,csv,txt,rtf,odt,jpg,jpeg,png,gif,webp,zip,rar';

    /**
     * Inbox — every conversation (the default community GC, any other group
     * chats, and DMs) the current user belongs to, newest activity first.
     */
    public function index()
    {
        return $this->render(null);
    }

    /**
     * Open a specific thread. Marks it as read for the current user.
     */
    public function show(Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);

        $conversation->participants()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);

        return $this->render($conversation);
    }

    private function render(?Conversation $active)
    {
        $user = Auth::user();

        $conversations = $user->conversations()
            ->with(['latestMessage.sender', 'participants'])
            ->get()
            ->sortByDesc(fn (Conversation $c) => $c->latestMessage?->created_at ?? $c->created_at)
            ->values();

        $messages = $active
            ? $active->messages()->with('sender')->get()
            : collect();

        if ($active) {
            $active->load(['participants.role']);
        }

        // Anyone active except the current user — used by the "New Message" picker.
        $messageable = User::where('is_active', true)
            ->where('id', '!=', $user->id)
            ->orderBy('first_name')
            ->get();

        // Alumni/chair can start a group chat and pick from students + alumni.
        $groupCandidates = User::whereIn('role_id', [Role::STUDENT, Role::ALUMNI])
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->orderBy('first_name')
            ->get();

        $unreadNotifications = DB::table('notifications')
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->count();

        // Students/alumni not already in the open group — for the "Add members" picker.
        $addableCandidates = ($active && $active->type === 'group')
            ? $groupCandidates->reject(fn (User $u) => $active->participants->contains('id', $u->id))->values()
            : collect();

        return view('messages.index', [
            'conversations' => $conversations,
            'active' => $active,
            'messages' => $messages,
            'messageable' => $messageable,
            'groupCandidates' => $groupCandidates,
            'addableCandidates' => $addableCandidates,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    /**
     * Send a message into a conversation. A message carries text and/or a
     * single shared file/photo — at least one of the two is required.
     */
    public function send(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:3000'],
            'file' => ['nullable', 'file', 'mimes:' . self::ALLOWED_EXTENSIONS, 'max:20480'],
        ]);

        abort_if(empty($data['body']) && ! $request->hasFile('file'), 422, 'A message needs text or a file.');

        $attributes = [
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'body' => $data['body'] ?? '',
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());

            $attributes['file_path'] = $file->store('chat-attachments', 'public');
            $attributes['original_name'] = $file->getClientOriginalName();
            $attributes['file_size'] = $file->getSize();
            $attributes['file_category'] = Material::categoryFor($extension);
        }

        $message = Message::create($attributes);

        $conversation->touch();
        $conversation->participants()->updateExistingPivot(Auth::id(), ['last_read_at' => $message->created_at]);

        if ($request->wantsJson()) {
            return response()->json(['message' => $this->formatMessage($message, Auth::user())]);
        }

        return back();
    }

    /**
     * Stream/redirect to a shared chat file for download.
     */
    public function download(Message $message)
    {
        $this->authorizeParticipant($message->conversation);

        abort_unless($message->file_path && Storage::disk('public')->exists($message->file_path), 404);

        return Storage::disk('public')->download($message->file_path, $message->original_name);
    }

    /**
     * Lightweight polling endpoint — returns messages newer than `after_id`
     * so the open thread can refresh without a full page reload.
     */
    public function poll(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);

        $afterId = (int) $request->query('after_id', 0);

        $messages = $conversation->messages()
            ->with('sender')
            ->where('id', '>', $afterId)
            ->get();

        if ($messages->isNotEmpty()) {
            $conversation->participants()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);
        }

        return response()->json([
            'messages' => $messages->map(fn (Message $m) => $this->formatMessage($m, Auth::user()))->values(),
        ]);
    }

    /**
     * Start (or resume) a 1-on-1 DM with another user.
     */
    public function startDirect(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id', 'different:' . Auth::id()],
        ]);

        $otherId = (int) $data['user_id'];

        $conversation = Conversation::findDirectBetween(Auth::id(), $otherId);

        if (! $conversation) {
            $conversation = Conversation::create(['type' => 'direct', 'created_by' => Auth::id()]);
            $conversation->addParticipant(Auth::user());
            $conversation->addParticipant(User::findOrFail($otherId));
        }

        if ($request->wantsJson()) {
            $conversation->load('participants');
            $messages = $conversation->messages()->with('sender')->get();

            return response()->json([
                'conversation' => [
                    'id' => $conversation->id,
                    'name' => $conversation->displayNameFor(Auth::user()),
                ],
                'messages' => $messages->map(fn (Message $m) => $this->formatMessage($m, Auth::user()))->values(),
            ]);
        }

        return redirect()->route('messages.show', $conversation);
    }

    /**
     * Create a new group chat. Alumni and the chair can start one.
     */
    public function createGroup(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAlumni() || $user->isChair(), 403, 'Only alumni can create a group chat.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $conversation = Conversation::create([
            'type' => 'group',
            'name' => $data['name'],
            'created_by' => $user->id,
        ]);

        $conversation->addParticipant($user);
        foreach (User::whereIn('id', $data['member_ids'])->get() as $member) {
            $conversation->addParticipant($member);
        }

        return redirect()->route('messages.show', $conversation)->with('status', 'Group chat created.');
    }

    /**
     * Add members to an existing group chat. Only the creator or the chair may.
     */
    public function addMembers(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);
        $this->authorizeGroupManager($conversation);

        $data = $request->validate([
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ]);

        foreach (User::whereIn('id', $data['member_ids'])->get() as $member) {
            $conversation->addParticipant($member);
        }

        return back()->with('status', 'Members added.');
    }

    /**
     * Rename a group chat. Only the creator or the chair may.
     */
    public function rename(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);
        $this->authorizeGroupManager($conversation);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $conversation->update(['name' => $data['name']]);

        return back()->with('status', 'Group renamed.');
    }

    /**
     * Leave a group chat. Not allowed on the default community GC.
     */
    public function leave(Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);
        abort_if($conversation->type !== 'group', 400, 'You can only leave a group chat.');
        abort_if($conversation->is_default_group, 400, 'You cannot leave the default community group.');

        $conversation->participants()->detach(Auth::id());

        return redirect()->route('messages.index')->with('status', 'You left the group.');
    }

    private function authorizeGroupManager(Conversation $conversation): void
    {
        $user = Auth::user();
        abort_if($conversation->type !== 'group', 400, 'This only applies to group chats.');
        abort_unless($user->isChair() || $conversation->created_by === $user->id, 403, 'Only the group creator can manage this group.');
    }

    private function authorizeParticipant(Conversation $conversation): void
    {
        abort_unless(
            $conversation->participants()->where('users.id', Auth::id())->exists(),
            403,
            'You are not part of this conversation.'
        );
    }

    private function formatMessage(Message $message, User $viewer): array
    {
        $attachment = null;
        if ($message->file_path) {
            $meta = $message->iconMeta();
            $attachment = [
                'url' => route('messages.attachments.download', $message->id),
                'name' => $message->original_name,
                'size' => $message->humanSize(),
                'is_image' => $message->file_category === 'image',
                'preview_url' => $message->file_category === 'image' ? $message->url() : null,
                'icon' => $meta['icon'],
                'color' => $meta['color'],
            ];
        }

        return [
            'id' => $message->id,
            'body' => $message->body,
            'sender_name' => $message->sender?->name ?? 'User',
            'is_mine' => $message->sender_id === $viewer->id,
            'created_at' => $message->created_at->diffForHumans(),
            'attachment' => $attachment,
        ];
    }
}
