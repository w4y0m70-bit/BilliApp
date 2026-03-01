<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');

        // 検索文字が短い場合は空配列を返す
        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('id', '!=', auth()->id())
            ->where(function($q) use ($query) {
                $q->where('account_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('first_name', 'like', "%{$query}%");
                
                // 数値の場合はID検索も追加
                if (is_numeric($query)) {
                    $q->orWhere('id', $query);
                }
            })
            ->select(['id', 'account_name', 'last_name', 'first_name'])
            ->limit(10)
            ->get();

        // JS（Alpine.js）側で使っているキー名に合わせて整形して返す
        $results = $users->map(function($user) {
            return [
                'id'           => $user->id,
                'account_name' => $user->account_name,
                'full_name'    => "{$user->last_name} {$user->first_name}", // JSの x-text="user.full_name" と一致させる
            ];
        });

        return response()->json($results); // 配列全体をJSONで返す
    }
}