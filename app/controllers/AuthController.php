<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Mailer;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;

/**
 * Controller for Authentication & User Account Security
 * Realises FR-AUTH-01, FR-AUTH-02, FR-AUTH-05
 */
class AuthController
{
    // FR-AUTH-01: Secure Authentication
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirectByRole();
            return;
        }

        View::render('auth/login', [
            'title' => 'Sign In',
        ], 'layouts/auth');
    }

    // FR-AUTH-01 / FR-AUTH-02: Process Login & Role Redirection
    public function login(): void
    {
        Csrf::checkOrAbort();

        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        // Basic throttle check using session
        $attempts = (int) Session::get('login_attempts', 0);
        $lastAttempt = (int) Session::get('last_login_attempt', 0);
        if ($attempts >= 5 && (time() - $lastAttempt) < 60) {
            Flash::danger('Too many failed login attempts. Please wait 1 minute before trying again.');
            View::render('auth/login', ['title' => 'Sign In', 'email' => $email], 'layouts/auth');
            return;
        }

        if (empty($email) || empty($password)) {
            Flash::danger('Please enter both email and password.');
            View::render('auth/login', ['title' => 'Sign In', 'email' => $email], 'layouts/auth');
            return;
        }

        $user = Database::fetchOne(
            "SELECT * FROM users WHERE email = :email LIMIT 1",
            ['email' => $email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Session::set('login_attempts', $attempts + 1);
            Session::set('last_login_attempt', time());
            Flash::danger('Invalid email address or password.');
            View::render('auth/login', ['title' => 'Sign In', 'email' => $email], 'layouts/auth');
            return;
        }

        if ($user['status'] === 'suspended') {
            Flash::danger('Your account has been suspended. Please contact the coordinator.');
            header('Location: ' . url('login'));
            exit;
        }

        if ($user['status'] === 'inactive') {
            Flash::danger('Your account is inactive. Please contact the coordinator.');
            header('Location: ' . url('login'));
            exit;
        }

        // Reset throttle on successful authentication
        Session::remove('login_attempts');
        Session::remove('last_login_attempt');

        Auth::login($user);

        // Check if forced password change on first login (FR-AUTH-05)
        if (!empty($user['must_change_password'])) {
            Flash::info('Welcome to InternFlow. Please change your temporary password to continue.');
            header('Location: ' . url('force-change-password'));
            exit;
        }

        Flash::success('Signed in successfully.');
        $this->redirectByRole();
    }

    // FR-AUTH-02: Role Redirection
    private function redirectByRole(): void
    {
        header('Location: ' . url('dashboard'));
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        Flash::info('You have been signed out.');
        header('Location: ' . url('login'));
        exit;
    }

    // FR-AUTH-05: Password Recovery Request Form
    public function showForgotPassword(): void
    {
        View::render('auth/forgot_password', [
            'title' => 'Forgot Password',
        ], 'layouts/auth');
    }

    // FR-AUTH-05: Process Password Recovery Token Generation
    public function forgotPassword(): void
    {
        Csrf::checkOrAbort();

        $email = trim($_POST['email'] ?? '');
        $user = Database::fetchOne("SELECT id, name, email FROM users WHERE email = :email", ['email' => $email]);

        // Always show friendly message to prevent user enumeration
        if ($user) {
            $plainToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $plainToken);
            $expiresAt = date('Y-m-d H:i:s', time() + (2 * 3600)); // 2 hours

            Database::execute(
                "INSERT INTO password_resets (user_id, token_hash, expires_at, created_at)
                 VALUES (:user_id, :token_hash, :expires_at, NOW())",
                [
                    'user_id' => $user['id'],
                    'token_hash' => $tokenHash,
                    'expires_at' => $expiresAt
                ]
            );

            $resetUrl = url('reset-password', ['token' => $plainToken]);
            $subject = "InternFlow Password Reset Request";
            $body = "<h3>Password Reset</h3>
                     <p>Dear {$user['name']},</p>
                     <p>We received a request to reset your InternFlow password. Click the link below to set a new password:</p>
                     <p><a href='{$resetUrl}'>{$resetUrl}</a></p>
                     <p>This link is valid for 2 hours. If you did not make this request, you can ignore this email.</p>";

            Mailer::send($user['email'], $subject, $body);
        }

        Flash::success('If that email is registered in our system, a password reset link has been dispatched.');
        header('Location: ' . url('login'));
        exit;
    }

    // FR-AUTH-05: Show Reset Password Form
    public function showResetPassword(): void
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            Flash::danger('Missing password reset token.');
            header('Location: ' . url('login'));
            exit;
        }

        $tokenHash = hash('sha256', $token);
        $reset = Database::fetchOne(
            "SELECT * FROM password_resets
             WHERE token_hash = :hash AND used_at IS NULL AND expires_at > NOW()
             ORDER BY id DESC LIMIT 1",
            ['hash' => $tokenHash]
        );

        if (!$reset) {
            Flash::danger('Password reset token is invalid or has expired.');
            header('Location: ' . url('login'));
            exit;
        }

        View::render('auth/reset_password', [
            'title' => 'Reset Password',
            'token' => $token,
        ], 'layouts/auth');
    }

    // FR-AUTH-05: Process Password Reset
    public function resetPassword(): void
    {
        Csrf::checkOrAbort();

        $token = (string) ($_POST['token'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirmation'] ?? '');

        if (strlen($password) < 8) {
            Flash::danger('Password must be at least 8 characters long.');
            header('Location: ' . url('reset-password', ['token' => $token]));
            exit;
        }

        if ($password !== $confirm) {
            Flash::danger('Password confirmation does not match.');
            header('Location: ' . url('reset-password', ['token' => $token]));
            exit;
        }

        $tokenHash = hash('sha256', $token);
        $reset = Database::fetchOne(
            "SELECT * FROM password_resets
             WHERE token_hash = :hash AND used_at IS NULL AND expires_at > NOW()
             ORDER BY id DESC LIMIT 1",
            ['hash' => $tokenHash]
        );

        if (!$reset) {
            Flash::danger('Password reset link is invalid or has expired.');
            header('Location: ' . url('login'));
            exit;
        }

        $newHash = password_hash($password, PASSWORD_BCRYPT);
        Database::transaction(function() use ($reset, $newHash) {
            Database::execute(
                "UPDATE users SET password_hash = :hash, must_change_password = 0 WHERE id = :id",
                ['hash' => $newHash, 'id' => $reset['user_id']]
            );
            Database::execute(
                "UPDATE password_resets SET used_at = NOW() WHERE id = :id",
                ['id' => $reset['id']]
            );
        });

        Flash::success('Your password has been successfully updated. You may now sign in.');
        header('Location: ' . url('login'));
        exit;
    }

    // FR-AUTH-05: Show Forced Password Change on First Login
    public function showForceChangePassword(): void
    {
        Auth::requireAuth();
        $user = Auth::user();

        if (empty($user['must_change_password'])) {
            header('Location: ' . url('dashboard'));
            exit;
        }

        View::render('auth/force_change_password', [
            'title' => 'First-Time Password Change',
        ], 'layouts/auth');
    }

    // FR-AUTH-05: Process Forced Password Change
    public function forceChangePassword(): void
    {
        Auth::requireAuth();
        Csrf::checkOrAbort();

        $user = Auth::user();
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['new_password_confirmation'] ?? '');

        if (!password_verify($current, $user['password_hash'])) {
            Flash::danger('The temporary password you entered is incorrect.');
            header('Location: ' . url('force-change-password'));
            exit;
        }

        if (strlen($new) < 8) {
            Flash::danger('New password must be at least 8 characters in length.');
            header('Location: ' . url('force-change-password'));
            exit;
        }

        if ($new !== $confirm) {
            Flash::danger('New password confirmation does not match.');
            header('Location: ' . url('force-change-password'));
            exit;
        }

        $newHash = password_hash($new, PASSWORD_BCRYPT);
        Database::execute(
            "UPDATE users SET password_hash = :hash, must_change_password = 0 WHERE id = :id",
            ['hash' => $newHash, 'id' => $user['id']]
        );

        Flash::success('Password updated successfully. Welcome to your InternFlow dashboard.');
        header('Location: ' . url('dashboard'));
        exit;
    }
}
