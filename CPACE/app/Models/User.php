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
}
