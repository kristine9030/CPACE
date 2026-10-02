{{-- AJAX response: every swappable region, in page order. The page script finds each one by id
     (#perfStats, #perfInsights, #perfAttention, #perfLeaderboard, #perfStudents, #perfBody,
     #perfAnalytics) and replaces it in place, leaving the static filter bar (and its focused
     search box) untouched. --}}
@include('faculty.partials.performance-stats')
@include('faculty.partials.performance-insights')
@include('faculty.partials.performance-attention')
@include('faculty.partials.performance-leaderboard')
@include('faculty.partials.performance-students')
@include('faculty.partials.performance-body')
@include('faculty.partials.performance-analytics')
