<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Every student and alumnus is automatically dropped into the single
     * default "CPACE Community" group chat as soon as their account exists.
     */
    protected static function booted(): void
    {
        static::created(function (User $user) {
            if ($user->isStudent() || $user->isAlumni()) {
                Conversation::defaultGroup()->addParticipant($user);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'profile_photo',
        'is_active',
        'email_verified',
        'last_login_at',
        'setup_completed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'email_verified' => 'boolean',
            'last_login_at' => 'datetime',
            'setup_completed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The role this user belongs to.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The student's gamification profile (streak, points, exam target).
     */
    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    /**
     * The faculty member's profile (employee number, department).
     */
    public function facultyProfile()
    {
        return $this->hasOne(FacultyProfile::class);
    }

    /**
     * The alumnus's profile (batch year, current job).
     */
    public function alumniProfile()
    {
        return $this->hasOne(AlumniProfile::class);
    }

    /**
     * Posts this alumnus has published to the Alumni Community feed.
     */
    public function communityPosts()
    {
        return $this->hasMany(CommunityPost::class, 'author_id');
    }

    /**
     * Direct messages and group chats this user is a participant of.
     */
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('joined_at', 'last_read_at');
    }

    /**
     * CPALE subjects assigned to this faculty member by the Program Chair.
     */
    public function assignedSubjects()
    {
        return $this->belongsToMany(Subject::class, 'faculty_subjects', 'faculty_id', 'subject_id')
            ->withPivot('assigned_by', 'assigned_at');
    }

    /**
     * Quiz sessions taken by this student.
     */
    public function quizSessions()
    {
        return $this->hasMany(QuizSession::class, 'student_id');
    }

    /**
     * Full name accessor (the schema stores first/last separately).
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    public function roleName(): ?string
    {
        return $this->role?->name;
    }

    public function isFaculty(): bool
    {
        return $this->role_id === Role::FACULTY;
    }

    public function isStudent(): bool
    {
        return $this->role_id === Role::STUDENT;
    }

    public function isAdmin(): bool
    {
        return $this->role_id === Role::ADMIN;
    }

    /**
     * The Program Chair is the Admin role for the BSA program.
     */
    public function isChair(): bool
    {
        return $this->role_id === Role::ADMIN;
    }

    public function isAlumni(): bool
    {
        return $this->role_id === Role::ALUMNI;
    }

    /**
     * A student account the Program Chair has flagged as graduated. Unlike
     * legacy role-based alumni, this keeps role_id = STUDENT so the person
     * still logs into their one and only student account.
     */
    public function isAlumniStudent(): bool
    {
        return $this->isStudent() && (bool) $this->studentProfile?->is_alumni;
    }

    /**
     * True for either kind of alumni: a legacy dedicated alumni account, or
     * a student account flagged as alumni. Use this for any alumni-only
     * feature gate instead of isAlumni() alone.
     */
    public function hasAlumniAccess(): bool
    {
        return $this->isAlumni() || $this->isAlumniStudent();
    }

    /**
     * A student the Program Chair has flagged as having shifted out of the
     * BSA program. Their account is blocked at login (see AuthController).
     */
    public function isShifted(): bool
    {
        return $this->isStudent() && (bool) $this->studentProfile?->is_shifted;
    }

    /**
     * A freshly-imported student must run the first-login Account Setup
     * (change the one-time password, confirm details, build a study plan)
     * before the rest of the app opens up.
     */
    public function needsSetup(): bool
    {
        return $this->isStudent() && $this->setup_completed_at === null;
    }
}
