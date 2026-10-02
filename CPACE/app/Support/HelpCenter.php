<?php

namespace App\Support;

use App\Models\User;

/**
 * Role-specific content for the Help & Support page: a greeting, shortcut
 * cards to the pages that role actually has, and FAQs grouped by topic.
 *
 * Kept in one place (rather than in the Blade view) so each role's copy can be
 * edited without touching markup, and so a route that is renamed breaks here
 * loudly instead of silently rendering a dead link.
 */
class HelpCenter
{
    /**
     * @return array{role: string, intro: string, manual: array<int, string>, quickLinks: array<int, array{icon: string, label: string, hint: string, url: string}>, faqs: array<string, array<int, array{q: string, a: string}>>}
     */
    public static function for(User $user): array
    {
        return match (true) {
            $user->isChair()   => self::chair(),
            $user->isFaculty() => self::faculty(),
            $user->isAlumni()  => self::alumni(),
            default            => self::student(),
        };
    }

    private static function student(): array
    {
        return [
            'role'  => 'Student Reviewer',
            'intro' => 'Guides for practising, taking mock exams, and tracking your CPA board readiness.',
            // Planned chapters, shown as a "coming soon" outline until the manual is written.
            'manual' => ['Getting started and your dashboard', 'Resources and learning materials', 'Practice quizzes and class quizzes', 'Taking a proctored mock exam', 'Performance, calendar and achievements', 'Community, messages and settings'],
            'quickLinks' => [
                ['icon' => 'fa-pen-fancy',     'label' => 'Practice Quizzes', 'hint' => 'Adaptive drills by topic',        'url' => route('adaptive-quizzes')],
                ['icon' => 'fa-file-alt',      'label' => 'Mock Exams',       'hint' => 'Timed, proctored board simulations', 'url' => route('mock-exams')],
                ['icon' => 'fa-chart-bar',     'label' => 'Performance',      'hint' => 'Strengths, weaknesses, readiness', 'url' => route('performance')],
                ['icon' => 'fa-calendar-alt',  'label' => 'Calendar',         'hint' => 'Your spaced-repetition schedule',  'url' => route('calendar')],
                ['icon' => 'fa-cog',           'label' => 'Settings',         'hint' => 'Profile, password, preferences',   'url' => route('settings')],
            ],
            'faqs' => [
                'Getting started' => [
                    ['q' => 'Where do I start reviewing?', 'a' => 'Open Resources to read the learning materials for each subject, then take a Practice Quiz on the same topic. Your results feed the Performance page and the Calendar, so the more you practise, the more tailored your review becomes.'],
                    ['q' => 'What is the difference between Practice Quizzes, Class Quizzes and Mock Exams?', 'a' => 'Practice Quizzes are self-paced and adapt to your weak topics. Class Quizzes are assigned by your instructor to your section. Mock Exams are timed, proctored simulations of the CPA Licensure Exam published by the Program Chair.'],
                ],
                'Mock exams & proctoring' => [
                    ['q' => 'What do I need before starting a mock exam?', 'a' => 'A laptop or desktop with a working camera, a stable connection, and a browser that allows camera and whole-screen sharing (Chrome or Edge work best). The exam opens in fullscreen when you start.'],
                    ['q' => 'My camera or screen share turned off mid-exam. What happens?', 'a' => 'The exam pauses on a lock screen until you share again — your time keeps running, so re-enable it right away. If your browser refused camera access, allow it in the site permissions (the camera icon in the address bar) and try again.'],
                    ['q' => 'I was disconnected during an exam. Did I lose my answers?', 'a' => 'Answers are saved as you go. Re-open the mock exam from the Mock Exams page to continue while time remains. If the exam closed before you could return, file a request below with the exam name and the time it happened.'],
                ],
                'Progress & scores' => [
                    ['q' => 'Why is a topic showing up on my Calendar?', 'a' => 'The Spaced Repetition Calendar schedules a topic for review when your accuracy is under 60% over 5 or more attempts, or when you answer 3 in a row wrong. Reviewing it on the scheduled day helps it stick.'],
                    ['q' => 'I think a question or answer key is wrong.', 'a' => 'Send a request below with the category "Wrong or unclear question content" and include the subject, topic, and the question text. Your instructor or the Program Chair will review it.'],
                ],
                'Account' => [
                    ['q' => 'How do I change my password or profile photo?', 'a' => 'Go to Settings. You can update your profile, avatar and password there. If you forgot your password, sign out and use "Forgot password" on the login page.'],
                    ['q' => 'My name, section or student number is wrong.', 'a' => 'Only the Program Chair can edit enrolment details. File a request below with the category "Account or sign-in problem" and the correct information.'],
                ],
            ],
        ];
    }

    private static function faculty(): array
    {
        return [
            'role'  => 'Faculty',
            'intro' => 'Guides for building your test bank, running class quizzes and mock exams, and monitoring students.',
            // Planned chapters, shown as a "coming soon" outline until the manual is written.
            'manual' => ['Getting started and your dashboard', 'Building and importing the test bank', 'Uploading learning materials', 'Creating class quizzes', 'Building a mock exam for review', 'Student performance and reports'],
            'quickLinks' => [
                ['icon' => 'fa-database',       'label' => 'Test Bank',           'hint' => 'Write, import and review questions', 'url' => route('faculty.test-bank')],
                ['icon' => 'fa-folder-open',    'label' => 'Learning Materials',  'hint' => 'Upload handouts for your subjects',   'url' => route('faculty.materials')],
                ['icon' => 'fa-clipboard-list', 'label' => 'Class Quizzes',       'hint' => 'Assign quizzes to your sections',     'url' => route('faculty.quizzes')],
                ['icon' => 'fa-file-pen',       'label' => 'Mock Exams',          'hint' => 'Build exams for Chair review',        'url' => route('faculty.mock-exams')],
                ['icon' => 'fa-users',          'label' => 'Student Performance', 'hint' => 'Monitor and remind students',         'url' => route('faculty.performance')],
            ],
            'faqs' => [
                'Test bank' => [
                    ['q' => 'How do I add many questions at once?', 'a' => 'In Test Bank, choose Import and download the template for your question type. Fill it in, upload it, and review the parsed questions before committing — nothing is saved until you confirm.'],
                    ['q' => 'What are AI substitute questions?', 'a' => 'When a topic has too few questions, CPAce can draft substitutes with AI. They stay out of quizzes and exams until a faculty member or the Program Chair approves them in the AI review queue.'],
                    ['q' => 'Why can I only see some subjects?', 'a' => 'You see the subjects the Program Chair assigned to you. If one is missing, ask the Chair to update your subject assignment, or file a request below.'],
                ],
                'Quizzes & mock exams' => [
                    ['q' => 'Who can see a class quiz I create?', 'a' => 'Only the students in the sections you assign it to, and only while it is open. Check the quiz schedule before publishing.'],
                    ['q' => 'Why isn’t my mock exam visible to students?', 'a' => 'Mock exams go to the Program Chair for review first. The Chair can publish it, set its audience, or return it to you for revision with notes.'],
                ],
                'Students' => [
                    ['q' => 'How do I nudge students who are falling behind?', 'a' => 'On Student Performance, use "Send Report" for one student or "Send Reminder to All". Students get an in-app notification and an email.'],
                    ['q' => 'A student can’t sign in.', 'a' => 'Student accounts are managed by the Program Chair, who can reissue a one-time password. Point the student to "Forgot password" first, then to the Chair.'],
                ],
                'Account' => [
                    ['q' => 'How do I change my password or profile?', 'a' => 'Open Settings from the sidebar. You can update your profile photo, avatar colour and password there.'],
                ],
            ],
        ];
    }

    private static function chair(): array
    {
        return [
            'role'  => 'Program Chair',
            'intro' => 'Administration guides, plus the Support Inbox where every request from students, faculty and alumni lands.',
            // Planned chapters, shown as a "coming soon" outline until the manual is written.
            'manual' => ['Getting started and your dashboard', 'Student and faculty accounts', 'Sections and subject assignments', 'Reviewing, publishing and monitoring mock exams', 'Communications and analytics', 'Handling the Support Inbox'],
            'quickLinks' => [
                ['icon' => 'fa-life-ring',        'label' => 'Support Inbox',       'hint' => 'Answer and resolve requests',     'url' => route('chair.support.index')],
                ['icon' => 'fa-user-graduate',    'label' => 'Students',            'hint' => 'Enrol, import and manage',        'url' => route('chair.students')],
                ['icon' => 'fa-chalkboard-user',  'label' => 'Faculty Accounts',    'hint' => 'Provision and assign faculty',    'url' => route('chair.faculty')],
                ['icon' => 'fa-file-pen',         'label' => 'Mock Exams',          'hint' => 'Review, publish and monitor',     'url' => route('chair.mock-exams')],
                ['icon' => 'fa-bullhorn',         'label' => 'Announcements',       'hint' => 'Announce to students or faculty', 'url' => route('messages.index', ['view' => 'announcements'])],
            ],
            'faqs' => [
                'Accounts' => [
                    ['q' => 'How do I add a batch of students?', 'a' => 'Go to Students → Import, download the template, fill it in and upload it. Each new account receives an email with a one-time password; you never see it.'],
                    ['q' => 'A user lost their one-time password.', 'a' => 'Open the user under Students or Faculty Accounts and reissue a one-time password. The old one stops working and the new one is emailed to the account owner.'],
                    ['q' => 'How do I move graduates to the alumni community?', 'a' => 'On Students, select the graduates and use the bulk "Mark as alumni" action. They keep access to the community feed and resource library.'],
                ],
                'Mock exams' => [
                    ['q' => 'What is my role in a mock exam?', 'a' => 'Faculty build the exam and submit it. You review it, set the audience, publish it, monitor attempts live (including proctoring and similarity flags), then close it and read the results.'],
                ],
                'Support Inbox' => [
                    ['q' => 'Where do support requests come from?', 'a' => 'From the Help & Support page of every signed-in user and from "Report an issue" on the landing page (which also works for guests). New requests notify you in-app and email the CPAce support inbox.'],
                    ['q' => 'What does the requester see when I reply?', 'a' => 'They get an in-app notification and an email with your reply, and can answer back in the same thread. Marking a request Resolved notifies them too; if they reply afterwards it reopens as In progress.'],
                ],
            ],
        ];
    }

    private static function alumni(): array
    {
        return [
            'role'  => 'Alumni',
            'intro' => 'Guides for the alumni community, the resource library and your profile.',
            // Planned chapters, shown as a "coming soon" outline until the manual is written.
            'manual' => ['Getting started as alumni', 'Posting on the community feed', 'Sharing in the resource library', 'Messages and group chats', 'Your alumni profile'],
            'quickLinks' => [
                ['icon' => 'fa-people-group', 'label' => 'Community Feed',   'hint' => 'Post tips and encouragement',   'url' => route('community.index')],
                ['icon' => 'fa-book',         'label' => 'Resource Library', 'hint' => 'Share review materials',        'url' => route('community.resources.index')],
                ['icon' => 'fa-comment-dots', 'label' => 'Messages',         'hint' => 'Chats and group conversations', 'url' => route('messages.index')],
                ['icon' => 'fa-id-card',      'label' => 'My Profile',       'hint' => 'Your alumni details',           'url' => route('alumni.profile')],
            ],
            'faqs' => [
                'Community' => [
                    ['q' => 'Who can see what I post?', 'a' => 'Posts on the community feed are visible to students and alumni in CPAce. Keep it helpful and avoid sharing personal or exam-confidential information.'],
                    ['q' => 'How do I share a reviewer or handout?', 'a' => 'Upload it in the Resource Library. You can remove your own uploads at any time.'],
                    ['q' => 'How do I start a group chat?', 'a' => 'Open Messages and create a group, then add members. You can rename or leave a group from its menu.'],
                ],
                'Account' => [
                    ['q' => 'How do I update my profile?', 'a' => 'Open My Profile from the sidebar to edit your details and photo.'],
                    ['q' => 'Someone posted something inappropriate.', 'a' => 'File a request below with the category "Something else", and include a link to the post. The Program Chair will review it.'],
                ],
            ],
        ];
    }
}
