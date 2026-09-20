{{-- AJAX response: all three swappable regions. The page script extracts
     #perfStats, #perfAnalytics and #perfBody and replaces them in place,
     leaving the static filter bar (and its focused search box) untouched. --}}
@include('faculty.partials.performance-stats')
@include('faculty.partials.performance-analytics')
@include('faculty.partials.performance-body')
