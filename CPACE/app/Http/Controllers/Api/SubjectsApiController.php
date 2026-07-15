<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubjectsApiController extends Controller
{
    public function index()
    {
        $studentId = Auth::id();

        $subjects = Subject::where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(function ($subject) use ($studentId) {
                $topicIds = $subject->topics()->where('is_active', true)->pluck('id');

                $questionCount = DB::table('questions')
                    ->where('is_active', true)
                    ->whereIn('topic_id', $topicIds)
                    ->count();

                $perf = DB::table('performance_records')
                    ->where('student_id', $studentId)
                    ->whereIn('topic_id', $topicIds)
                    ->selectRaw('COALESCE(SUM(correct_count),0) c, COALESCE(SUM(total_attempts),0) t')
                    ->first();

                $mastery = ($perf && $perf->t > 0) ? (int) round($perf->c / $perf->t * 100) : 0;

                return [
                    'id'             => $subject->id,
                    'code'           => $subject->code,
                    'name'           => $subject->name,
                    'description'    => $subject->description,
                    'color'          => $subject->color,
                    'icon'           => $subject->icon,
                    'question_count' => $questionCount,
                    'mastery'        => $mastery,
                    'passing_threshold' => $subject->passing_threshold,
                    'is_passing'     => $perf && $perf->t > 0 && $mastery >= $subject->passing_threshold,
                ];
            });

        return response()->json(['subjects' => $subjects]);
    }
}
