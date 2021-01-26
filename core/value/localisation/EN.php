<?php

use uve\core\module\user\service\UserService;

return [
    'siteName' => 'Victor Gonzalez',
    'siteTitle' => '',
    'siteDescription' => 'I am a Web Developer',

    'navAccount' => 'Account',
    'navSignOut' => 'Sign Out',
    'navSignIn' => 'Sign In',
    'navSignUp' => 'Sign Up',
    'navChangePassword' => 'Change Password',
    'navDownloadAccountData' => 'Download Account Data',
    'navDeleteAccount' => 'Delete Account',
    'navAdminDashboard' => 'Dashboard',
    'navAdminUsers' => 'Users',
    'navAdminImages' => 'Images',
    'navAdminArticles' => 'Articles',

    'authenticationActionHomeLink' => 'Go back to log in page',
    'authenticationLoginSubtitle' => 'Welcome back.',
    'authenticationRegisterSubtitle' => 'Welcome',
    'authenticationRegisterTOS' => 'By signing up you agree to our <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>.',
    'authenticationRegisterPasswordHelp' => 'Min length: ' . UserService::USER_PASSWORD_MIN_LENGTH . ' characters.',
    'authenticationRegisterAlreadyLogin' => 'Already signed up?',

    'authenticationForgotPassword' => 'Forgot your password?',
    'authenticationForgotPasswordSubtitle' => "Don't worry, just enter the email address you registered with and we will send you a link to reset your password.",
    'authenticationForgotPasswordAction' => 'Send password reset link',
    'authenticationForgotPasswordActionSuccess' => 'Email sent (<b><span id="register-feedback-email"></span></b>). Check your inbox and follow the instructions.',

    'authenticationPasswordResetSubtitle' => 'Change your password',
    'authenticationPasswordResetActionSuccess' => 'Password changed successfully.',
    'authenticationPasswordResetAlreadyLogin' => 'Do you want to <a href="%s/login">log in to your account</a> without changing your password?',

    'authenticationInviteRequest' => 'Get your invitation',
    'authenticationInviteRequestSubtitle' => 'The site is currently in private beta, we are making sure everything works as expected and looking forward to getting it ready for you. Enter your email address and we\'ll send you an invitation as soon as it\'s ready.<br>Thank you for your patience!',
    'authenticationInviteRequestActionSuccess' => '<h2>Invitation request received</h2><p>Your email: <b><span id="register-feedback-email"></span></b>.</p><p>Thank you!</p>',
    'authenticationInviteRequestHomeLink' => 'Go back to the homepage',
    'authenticationInviteRequestFormAction' => 'Request an invitation',

    'formPlaceholderUserName' => 'Your name',
    'formPlaceholderEmail' => 'Your email address',
    'formPlaceholderPassword' => 'Your password',
    'formPlaceholderCreatePassword' => 'Create a password',
    'formPlaceholderPasswordNew' => 'New password',
    'formPlaceholderPasswordConfirmation' => 'Repeat password',
    'formLoginAction' => 'Log in',
    'formPasswordResetAction' => 'Cambiar contrasinal',

    'dashboardGoTo' => 'Go to...',

    'globalRequired' => 'Required',
    'globalNew' => 'New',
    'globalEdit' => 'Edit',
    'globalTitle' => 'Title',
    'globalSave' => 'Save',
    'globalUpdate' => 'Update',
    'globalSaveAndClose' => 'S&C',
    'globalUpdateAndClose' => 'U&C',
    'globalUpdated' => 'Updated',
    'globalUpdatedAt' => 'Updated At',
    'globalCreated' => 'Created',
    'globalStatus' => 'Status',
    'globalUploadImage' => 'Upload image(s)',
    'globalPreview' => 'Preview',

    'globalArticle' => 'Article',
];
