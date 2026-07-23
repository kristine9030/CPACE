# CPAce

**CPAce** is a web and mobile-based **CPALE (Certified Public Accountant Licensure Examination) review platform**. It provides review materials, practice examinations, progress tracking, and personalized learning insights to help aspiring CPAs improve their performance and exam readiness.

---

## Overview

CPAce brings students, faculty, program chairs, and alumni together in a single review ecosystem. Students study with curated materials and practice quizzes, the system detects their weak areas and schedules review using spaced repetition, and faculty/chairs monitor progress and keep learners on track.

## Key Features

- **Practice Examinations** — timed quiz sessions with question banks, variants, and multiple-choice items organized by subject and topic.
- **Personalized Learning Insights** — automatic detection of weak areas from quiz performance to guide what to review next.
- **Spaced-Repetition Calendar** — an SM-2 driven review schedule that surfaces the right topics at the right time.
- **Progress Tracking & Achievements** — dashboards and a leaderboard so students can measure their exam readiness.
- **Review Materials** — subject/topic-based learning resources and review notes.
- **Alumni Community** — community posts, replies, likes, shared resources, and messaging/chat.
- **Faculty & Chair Tools** — student monitoring, review notes, and automated student reminders sent via email (SMTP).
- **Role-Based Access** — distinct experiences for Students, Faculty, Chairs, and Alumni.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Vite + Vue 3 + Tailwind CSS 4 |
| Database | MySQL |
| API | RESTful API (`routes/api.php`) with token-based auth |

## Repository Structure

```
CPACE/          The Laravel application (all source code lives here)
documents/      Project documentation (API list, work plan)
README.md       This file
```

> **Note:** All application code is inside the `CPACE/` folder. Run all commands (`composer`, `npm`, `php artisan`) from within `CPACE/`.

## Getting Started

```bash
cd CPACE

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Set up the database (update DB credentials in .env first)
php artisan migrate       # or import the provided SQL dump

# Run the app
composer run dev          # starts the Laravel server + Vite dev server
```

The app is designed to run on a local XAMPP stack. Ensure Apache and MySQL are running.

## Roles

| Role | Description |
|------|-------------|
| **Student** | Takes practice exams, follows the spaced-repetition calendar, tracks progress. |
| **Faculty** | Manages materials, monitors students, sends reminders. |
| **Chair** | Program-level oversight and management. |
| **Alumni** | Participates in the community and mentorship. |

---

*CPAce — helping future CPAs prepare, practice, and pass.*
