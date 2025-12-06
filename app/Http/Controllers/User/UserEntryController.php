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

        // 既存エントリー取得（キャンセルも含めて）
        $existing = UserEntry::where('user_id', $userId)
            ->where('event_id', $event->id)
            ->first();

        // 既存があって、かつステータスが cancelled 以外 → 新規エントリー不可
        if ($existing && $existing->status !== 'cancelled') {
            return back()->with('error', 'すでにエントリー済みです。');
        }

        // 現在の参加数
        $entryCount = $event->userEntries()->where('status', 'entry')->count();
        $isFull = $entryCount >= $event->max_participants;

        // 満員かつキャンセル待ち不可
        if ($isFull && !$event->allow_waitlist) {
            return back()->with('error', '定員に達しているためエントリーできません。');
        }

        // ステータス
        $status = $isFull ? 'waitlist' : 'entry';

        // waitlist_until
        $waitlistUntil = null;
        if ($status === 'waitlist') {

            $input = $request->input('waitlist_until');

            if ($input) {
                $waitlistUntil = Carbon::createFromFormat('Y-m-d\TH:i', $input);

                if ($waitlistUntil > $event->event_date) {
                    $waitlistUntil = $event->event_date;
                }
            }
        }

        // 入力値
        $class = $request->input('class', '未設定');
        $gender = $request->input('gender', '未設定');

        if ($existing) {
            // 既存がキャンセル済み → 復活させる
            $existing->fill([
                'status' => $status,
                'waitlist_until' => $waitlistUntil,
                'class' => $class,
                'gender' => $gender,
            ])->save();

            $message = $status === 'entry'
                ? 'エントリーを再開しました！'
                : 'キャンセル待ちに再登録されました。';

            return redirect()->route('user.events.show', $event->id)
                ->with('message', $message);
        }

        // ここだけ新規作成（キャンセルも存在しない場合のみ）
        $entry = new UserEntry();
        $entry->fill([
            'user_id' => $userId,
            'event_id' => $event->id,
            'status' => $status,
            'waitlist_until' => $waitlistUntil,
            'class' => $class,
            'gender' => $gender,
        ])->save();

        $message = $status === 'entry'
            ? 'イベントにエントリーしました！'
            : 'キャンセル待ちに登録されました。';

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
        // 自分のエントリーであることを保証
        if ($entry->user_id !== Auth::id()) {
            abort(403);
        }

        // waitlist の場合のみ期限処理
        if ($entry->status === 'waitlist') {

            // 「期限をクリア」ボタンが押された場合
            if ($request->has('clear') && $request->input('clear') == 1) {
                $entry->waitlist_until = null;
                $entry->save();

                return back()->with('message', 'キャンセル待ち期限をクリアしました。');
            }

            // 通常の保存（空欄は null とする）
            $input = $request->input('waitlist_until');

            if (!empty($input)) {
                $waitlistUntil = Carbon::createFromFormat('Y-m-d\TH:i', $input);

                // イベント日以降を禁止
                if ($waitlistUntil > $event->event_date) {
                    $waitlistUntil = $event->event_date;
                }

                $entry->waitlist_until = $waitlistUntil;
            } else {
                // 入力なし → null
                $entry->waitlist_until = null;
            }

            $entry->save();
        }

        return back()->with('message', 'キャンセル待ち期限を更新しました。');
    }


    // public function update(Request $request, Event $event, UserEntry $entry)
    // {
    //     if ($entry->event_id !== $event->id) {
    //         abort(403, 'Invalid entry.');
    //     }

    //     if ($entry->status !== 'waitlist') {
    //         return redirect()->back()->with('error', 'キャンセル待ちではないエントリーは更新できません。');
    //     }

    //     $request->validate([
    //         'waitlist_until' => ['required','date_format:Y-m-d\TH:i'],
    //     ]);
        
    //     $input = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->input('waitlist_until'));
    //     if ($input > $event->event_date) {
    //         $input = $event->event_date;
    //     }

    //     $entry->waitlist_until = $input;
    //     // $entry->waitlist_until = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->input('waitlist_until'));
    //     $entry->save();

    //     return redirect()->back()->with('success', 'キャンセル待ち期限を更新しました。');
    // }


}