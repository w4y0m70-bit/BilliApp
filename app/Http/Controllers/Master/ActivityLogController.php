<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        // URLに ?type=Entry などがある場合に絞り込む
        if ($request->filled('type')) {
            $query->where('subject_type', $request->type);
        }

        // 実行者（ユーザーID）で絞り込み
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }
        
        $logs = $query->paginate(20);
        
        // 存在するモデルのリスト（フィルタ用）
        $modelTypes = Activity::groupBy('subject_type')
                    ->pluck('subject_type')
                    ->all(); 

        return view('master.activity_log.index', compact('logs', 'modelTypes'));
    }
}