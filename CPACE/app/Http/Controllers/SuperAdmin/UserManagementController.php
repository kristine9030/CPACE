<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Concerns\GeneratesOneTimePassword;
use App\Http\Controllers\Controller;
use App\Mail\AccountCredentialsMail;
use App\Models\FacultyProfile;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use App\Support\Auditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * Super Admin's own account-provisioning tool — separate from the Program
 * Chair's faculty/student provisioning (Chair\ProgramChairController /
 * Chair\StudentManagementController) because a Super Admin can create ANY
 * role, including other Super Admins and Program Chairs, which the Chair
 * portal deliberately cannot do.
 */
class UserManagementController extends Controller
{
    use GeneratesOneTimePassword;

    private const PER_PAGE = 20;

    private const ROLE_LABELS = [
        Role::SUPER_ADMIN => 'Super Admin',
        Role::ADMIN => 'Program Chair',
        Role::FACULTY => 'Faculty',
        Role::STUDENT => 'Student',
        Role::ALUMNI => 'Alumni',
    ];

    public function index(Request $request)
    {
        $query = User::query()->with('role')->orderByDesc('created_at');

        if ($role = $request->input('role')) {
            $query->where('role_id', $role);
        }
        if ($status = $request->input('status')) {
            $query->where('is_active', $status === 'active');
        }
        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(self::PER_PAGE)->withQueryString();

        $totalsByRole = User::select('role_id', DB::raw('COUNT(*) as total'))
            ->groupBy('role_id')->pluck('total', 'role_id');

        return view('superadmin.users.index', [
            'users' => $users,
            'roleLabels' => self::ROLE_LABELS,
            'totalsByRole' => $totalsByRole,
            'filters' => $request->only('role', 'status', 'search'),
        ]);
    }

    public function create()
    {
        return view('superadmin.users.create', ['roleLabels' => self::ROLE_LABELS]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:60',
            'last_name'  => 'required|string|max:60',
            'email'      => 'required|email|max:120|unique:users,email',
            'role_id'    => ['required', Rule::in(array_keys(self::ROLE_LABELS))],
            'employee_number' => 'nullable|string|max:20',
            'department'      => 'nullable|string|max:100',
        ]);

        $tempPassword = $this->generateOneTimePassword();
        $roleId = (int) $data['role_id'];

        $user = DB::transaction(function () use ($data, $tempPassword, $roleId) {
            $user = User::create([
                'role_id'            => $roleId,
                'first_name'         => $data['first_name'],
                'last_name'          => $data['last_name'],
                'email'              => $data['email'],
                'password'           => Hash::make($tempPassword),
                'is_active'          => true,
                'email_verified'     => true,
                'setup_completed_at' => in_array($roleId, [Role::STUDENT, Role::FACULTY], true) ? null : now(),
                'temp_password'      => $tempPassword,
            ]);

            if ($roleId === Role::FACULTY) {
                FacultyProfile::create([
                    'user_id' => $user->id,
                    'employee_number' => $data['employee_number'] ?? null,
                    'department' => $data['department'] ?? 'College of Accountancy',
                ]);
            } elseif ($roleId === Role::STUDENT) {
                StudentProfile::create(['user_id' => $user->id]);
            }

            return $user;
        });

        $roleLabel = self::ROLE_LABELS[$roleId];
        $mailed = $this->mailCredentials($user, $tempPassword, $roleLabel);

        Auditor::log(Auth::user(), 'user_created', "Created a {$roleLabel} account for {$user->email}.", 'User', $user->id);

        return redirect()->route('superadmin.users')->with(
            'status',
            $mailed
                ? "{$roleLabel} account created. The one-time password was emailed to {$user->email}."
                : "{$roleLabel} account created, but the credentials email couldn't be sent. Use \"Resend OTP\" to try again."
        );
    }

    public function toggleActive(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('warning', 'You cannot deactivate your own account.');
        }

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        $verb = $user->is_active ? 'Activated' : 'Deactivated';
        Auditor::log(Auth::user(), $user->is_active ? 'user_activated' : 'user_deactivated', "{$verb} {$user->email}.", 'User', $user->id);

        return back()->with('status', $user->is_active ? 'Account activated.' : 'Account deactivated.');
    }

    public function resendOtp(int $id)
    {
        $user = User::findOrFail($id);
        $tempPassword = $this->generateOneTimePassword();

        $user->forceFill(['password' => Hash::make($tempPassword), 'temp_password' => $tempPassword])->save();

        $roleLabel = self::ROLE_LABELS[$user->role_id] ?? 'account';
        $mailed = $this->mailCredentials($user, $tempPassword, $roleLabel, reissue: true);

        Auditor::log(Auth::user(), 'otp_reissued', "Reissued the one-time password for {$user->email}.", 'User', $user->id);

        return back()->with('status', $mailed
            ? "A new one-time password was emailed to {$user->email}."
            : "Couldn't send the email — please try again.");
    }

    private function mailCredentials(User $user, string $tempPassword, string $roleLabel, bool $reissue = false): bool
    {
        try {
            Mail::to($user->email)->send(new AccountCredentialsMail($user, $tempPassword, $roleLabel, $reissue));

            return true;
        } catch (\Throwable $exception) {
            Log::error('Failed to email account credentials.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
