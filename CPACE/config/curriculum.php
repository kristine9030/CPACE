<?php

return [

    /*
    | AI substitute questions (curriculum:fill-gaps). A topic with a TOS item
    | count that stays short of it gets a first notice, a final warning
    | `gap_fill_warning_days` before the deadline, and after `gap_fill_grace_days`
    | the missing items are drafted by AI for the faculty or chair to review.
    */

    'gap_fill_grace_days' => (int) env('CURRICULUM_GAP_FILL_GRACE_DAYS', 14),

    'gap_fill_warning_days' => (int) env('CURRICULUM_GAP_FILL_WARNING_DAYS', 3),

    // Upper bound on AI drafts per topic per run, to keep API cost and run time in check.
    'gap_fill_max_per_topic' => (int) env('CURRICULUM_GAP_FILL_MAX_PER_TOPIC', 5),

    // Upper bound on AI drafts across all topics in one run; the rest wait for the next night.
    'gap_fill_max_per_run' => (int) env('CURRICULUM_GAP_FILL_MAX_PER_RUN', 40),

];
