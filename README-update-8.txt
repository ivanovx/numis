UPDATE 8 - fixes the failing GitHub build (composer ci:check = Pint + PHPStan + tests)
=====================================================================================

1) Extract this archive over the project root (it only contains changed files, TODO.md is NOT touched).

2) DELETE these obsolete starter-kit tests - they test features that no longer exist in the app
   (registration, password reset, e-mail verification, 2FA, profile/security settings):

     rm -f  tests/Feature/Auth/EmailVerificationTest.php \
            tests/Feature/Auth/PasswordConfirmationTest.php \
            tests/Feature/Auth/PasswordResetTest.php \
            tests/Feature/Auth/RegistrationTest.php \
            tests/Feature/Auth/TwoFactorChallengeTest.php
     rm -rf tests/Feature/Settings

   (tests/Feature/Auth/AuthenticationTest.php and tests/Feature/DashboardTest.php are replaced by
    versions that test the real login and the admin dashboard.)

3) Run the same three checks CI runs:   composer ci:check
