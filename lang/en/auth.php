<?php

declare(strict_types=1);

return [

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many attempts. Please try again in :seconds seconds.',
    'account_inactive' => 'This account is not permitted to sign in. Please contact an administrator.',
    'unauthenticated' => 'Authentication is required to access this resource.',
    'forbidden' => 'You do not have permission to perform this action.',

    'signed_in' => 'Welcome back.',
    'signed_out' => 'You have been signed out.',
    'registered' => 'Your account is ready.',
    'verification_sent' => 'A verification link has been sent to your email address.',
    'email_verified' => 'Your email address has been verified.',

    'login' => [
        'title' => 'Sign in',
        'heading' => 'Sign in to your account',
        'subheading' => 'Enter your credentials to continue.',
        'remember' => 'Keep me signed in',
        'forgot' => 'Forgot password?',
        'submit' => 'Sign in',
        'no_account' => 'New here?',
        'create_account' => 'Create an account',
    ],

    'register' => [
        'title' => 'Create account',
        'heading' => 'Create your account',
        'subheading' => 'Get started in less than a minute.',
        'submit' => 'Create account',
        'have_account' => 'Already registered?',
        'sign_in' => 'Sign in instead',
        'terms' => 'I agree to the terms of service and privacy policy.',
    ],

    'forgot_password' => [
        'title' => 'Reset password',
        'heading' => 'Forgot your password?',
        'subheading' => 'Enter your email address and we will send you a reset link.',
        'submit' => 'Email password reset link',
        'back' => 'Back to sign in',
    ],

    'reset_password' => [
        'title' => 'Choose a new password',
        'heading' => 'Choose a new password',
        'subheading' => 'Your new password must differ from previously used passwords.',
        'submit' => 'Reset password',
    ],

    'verify_email' => [
        'title' => 'Verify email',
        'heading' => 'Verify your email address',
        'subheading' => 'We sent a verification link to :email. Follow the link to activate your account.',
        'resend' => 'Resend verification email',
        'logout' => 'Sign out',
        'hint' => 'Check your spam folder if the message has not arrived within a few minutes.',
    ],

    'confirm_password' => [
        'title' => 'Confirm password',
        'heading' => 'Confirm your password',
        'subheading' => 'This is a secure area. Please confirm your password before continuing.',
        'submit' => 'Confirm',
    ],

    'fields' => [
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'email' => 'Email address',
        'phone' => 'Phone number',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
        'terms' => 'terms of service',
    ],

    'password_policy' => [
        'title' => 'Password requirements',
        'min_length' => 'At least :count characters',
        'mixed_case' => 'One uppercase and one lowercase letter',
        'uppercase' => 'At least one uppercase letter',
        'lowercase' => 'At least one lowercase letter',
        'numbers' => 'At least one number',
        'symbols' => 'At least one symbol',
        'uncompromised' => 'Not found in a known data breach',
    ],

    'login_status' => [
        'successful' => 'Successful',
        'failed' => 'Failed',
        'locked' => 'Blocked',
    ],

    'sessions' => [
        'unknown_device' => 'Unknown device',
        'device_summary' => ':browser on :platform',
    ],

    'api' => [
        'registered' => 'Account created successfully.',
        'authenticated' => 'Authenticated successfully.',
        'signed_out' => 'Token revoked successfully.',
        'profile_retrieved' => 'Profile retrieved successfully.',
    ],

];
