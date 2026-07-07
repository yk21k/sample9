<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogsController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query();

        // 🔍 ユーザー
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 🔍 アクション
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        // 🔍 対象タイプ（Productなど）
        if ($request->filled('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        // 🔍 対象ID
        if ($request->filled('target_id')) {
            $query->where('target_id', $request->target_id);
        }

        // 🔍 日付（from）
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        // 🔍 日付（to）
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->latest()->paginate(20)->appends($request->all());

        return view('admin.activity_logs.index', compact('logs'));
    }

    public function show($id)
    {
        $log = ActivityLog::findOrFail($id);

        return view('admin.activity_logs.show', compact('log'));
    }
}
