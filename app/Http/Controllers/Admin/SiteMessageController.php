<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteMessage;
use Illuminate\Http\Request;

class SiteMessageController extends Controller
{
    public function edit()
    {
        // 最初の1件を取得（なければ作成）
        $message = SiteMessage::firstOrCreate(['id' => 1]);
        return view('master.site_message.edit', compact('message'));
    }

    public function update(Request $request)
    {
        $message = SiteMessage::findOrFail(1);
        $message->update([
            'content' => $request->content,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('status', 'メッセージを更新しました！');
    }
}