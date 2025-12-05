<?php
//エントリー処理
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserEntry;
use App\Models\Event;
use App\Services\WaitlistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class UserEntryController extends Controller
{
    protected $waitlistService;

    public function __construct(WaitlistService $waitlistService)
    {
        $this->waitlistService = $waitlistService;
    }

    /**
     * 🟢 エントリー一覧
     */
    public function index()
    {
        $entries = UserEntry::with('event')
            ->where('user_id', Auth::id() ?? 1) // 仮ログイン中のため
            ->orderByDesc('created_at')
            ->latest()
            ->get();

        return view('user.entries.index', compact('entries'));
    }

    public function entry(Request $request, Event $event)
    {
        $userId = Auth::id() ?? 1;

        // すでにキャンセル以外でエントリー済みかチェック
        if (UserEntry::where('user_id', $userId)
            ->where('event_id', $event->id)
            ->where('status', '!=', 'cancelled')
            ->exists()) {
            return back()->with('error', 'すでにこのイベントにエントリーしています。');
        }

        // もしキャンセル済みのレコードがあれば再利用
        $entry = UserEntry::where('user_id', $userId)
            ->where('event_id', $event->id)
            ->where('status', 'cancelled')
            ->first();

        $entryCount = $event->userEntries()->where('status', 'entry')->count();
        $isFull = $entryCount >= $event->max_participants;

        $waitlistUntil = null;
        if ($isFull && $event->allow_waitlist && $request->filled('waitlist_until')) {
            $request->validate([
                'waitlist_until' => 'date|after:now',
            ]);
            $waitlistUntil = $request->input('waitlist_until');
        }

        $status = $isFull ? 'waitlist' : 'entry';

        if ($entry) {
            // キャンセル済みエントリーを復活
            $entry->update([
                'class' => $request->class,
                'gender' => $request->gender,
                'status' => $status,
                'waitlist_until' => $waitlistUntil,
            ]);
        } else {
            // 新規作成
            UserEntry::create([
                'user_id' => $userId,
                'event_id' => $event->id,
                'class' => $request->class,
                'gender' => $request->gender,
                'status' => $status,
                'waitlist_until' => $waitlistUntil,
            ]);
        }

        $message = $status === 'entry'
            ? 'イベントにエントリーしました！'
            : '定員に達しているため、キャンセル待ちに登録されました。';

        return redirect()->route('user.events.show', $event->id)
            ->with('message', $message);
    }


    public function cancel(Event $event, $entryId)
    {
        // エントリー取得（必ずそのイベント内）
        $entry = $event->userEntries()->findOrFail($entryId);

        // モデル側のキャンセルメソッドを呼び出す
        $name = $entry->cancelAndPromoteWaitlist();

        return redirect()->back()->with('success', "$name さんのエントリーをキャンセルしました");
    }

    public function update(Request $request, Event $event, UserEntry $entry)
{
    $currentUser = Auth::user();
    if ($entry->user_id !== $currentUser->id) {
        abort(403);
    }

    $validated = $request->validate([
        'useDeadline' => ['nullable', 'in:on,1,true'],
        'waitlist_until' => ['nullable', 'date_format:Y-m-d\TH:i'],
    ]);

    // チェックボックスがある場合だけ値を更新
    $useDeadline = $request->input('useDeadline') === 'on';
    if ($useDeadline && $request->filled('waitlist_until')) {
        $deadlineInput = $request->input('waitlist_until');
        $entry->waitlist_until = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $deadlineInput);
    } else {
        $entry->waitlist_until = null;
    }

    $entry->save();

    return redirect()->back()->with('success', 'エントリー情報を更新しました。');
}



}