<?php

use Amora\Core\Module\User\Service\UserService;

return [
    'navDashboard' => 'Dashboard',
    'navAccount' => 'Account',
    'navSignOut' => 'Sign Out',
    'navSignIn' => 'Sign In',
    'navSignUp' => 'Sign Up',
    'navChangePassword' => 'Change Password',
    'navCreatePassword' => 'Create Password',
    'navDownloadAccountData' => 'Download Account Data',
    'navDeleteAccount' => 'Delete Account',
    'navAdministrator' => 'Admin',
    'navAdminDashboard' => 'Dashboard',
    'navAdminUsers' => 'Users',
    'navAdminContent' => 'Content',
    'navAdminImages' => 'Images',
    'navAdminArticles' => 'Articles',
    'navAdminArticleOptions' => 'Article Options',

    'authenticationActionHomeLink' => 'Go back to log in page',
    'authenticationLoginSubtitle' => 'Welcome back.',
    'authenticationRegisterSubtitle' => 'Welcome',
    'authenticationRegisterTOS' => 'By signing up you agree to our <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>.',
    'authenticationRegisterPasswordHelp' => 'Min length: ' . UserService::USER_PASSWORD_MIN_LENGTH . ' characters.',
    'authenticationRegisterAlreadyLogin' => 'Already signed up?',

    'authenticationForgotPassword' => 'Forgot your password?',
    'authenticationForgotPasswordSubtitle' => "Don't worry, just enter the email address you registered with and we will send you a link to reset your password.",
    'authenticationForgotPasswordAction' => 'Send password reset link',
    'authenticationForgotPasswordActionSuccess' => 'A password restoration email has been sent to you (<b><span id="register-feedback-email"></span></b>) if you have an account with us. Check your inbox and follow the instructions.',
    'authenticationEmailVerified' => 'Email address verified successfully.',
    'authenticationEmailVerifiedError' => 'The link to verify your email address is not valid.',
    'authenticationEmailVerifiedExpired' => 'The link to verify your email address has expired. Please, start the process again.',
    'authenticationPasswordResetLinkError' => 'The link to reset your password is not valid. Please start the process again.',
    'authenticationPasswordCreationLinkError' => 'The link to create a password is not valid. Please, reset your password or contact the site admin.',
    'authenticationEmailAndOrPassNotValid' => 'Email address and/or password not valid.',
    'authenticationEmailNotValid' => 'Email address not valid.',
    'authenticationUserRegistrationDisabled' => 'The user registration functionality is not enabled. Please, contact the site administrator.',
    'authenticationPasswordTooShort' => 'The password is too short. Please, fix it and try it again.',
    'authenticationPasswordsDoNotMatch' => 'The passwords do not match. Please, fix it and try it again.',
    'authenticationRegistrationErrorExistingEmail' => 'There is another account with the same email address. Please, log in to your account <a href="%s">here</a>.',
    'authenticationPassNotValid' => 'Current password not valid.',

    'authenticationPasswordResetSubtitle' => 'Change your password',
    'authenticationPasswordResetActionSuccess' => 'Password changed successfully.',
    'authenticationPasswordResetAlreadyLogin' => 'Do you want to <a href="%s">log in to your account</a> without changing your password?',
    'authenticationPasswordCreateSubtitle' => 'Create password',
    'authenticationPasswordCreationActionSuccess' => 'Password created successfully.',

    'authenticationInviteRequest' => 'Get your invitation',
    'authenticationInviteRequestSubtitle' => 'The site is currently in private beta, we are making sure everything works as expected and looking forward to getting it ready for you. Enter your email address and we\'ll send you an invitation as soon as it\'s ready.<br>Thank you for your patience!',
    'authenticationInviteRequestActionSuccess' => '<h2>Invitation request received</h2><p>We will send your invitation as soon as everything is ready. Thank you for your patience!</p><p>Your email: <b><span id="register-feedback-email"></span></b>.</p>',
    'authenticationInviteRequestHomeLink' => 'Go back to homepage',
    'authenticationInviteRequestFormAction' => 'Request an invitation',
    'authenticationVerifyEmailBanner' => 'Please verify your account following the instructions sent to your email address at <b>%s</b>. If you have not received it, please check your spam folder or <a class="verified-link" data-user-id="%d" href="#">click here</a> and we will send you another one. If necessary you can <a href="/en/account">update your email address here</a>.',

    'formYourAccount' => 'Your Account',
    'formPlaceholderUserName' => 'Name',
    'formPlaceholderUserHelp' => 'Min three characters',
    'formEmail' => 'Your Email Address',
    'formEmailNewUserHelp' => 'An email to create a new password will be sent to the user once created.',
    'formPlaceholderEmail' => 'name@example.com',
    'formEmailUpdateWarning' => 'Please, verify your new email address (%s) to get it changed.',
    'formPlaceholderPassword' => 'Your password',
    'formPlaceholderCreatePassword' => 'Create a password',
    'formPlaceholderPasswordNew' => 'New password',
    'formPlaceholderPasswordConfirmation' => 'Repeat password',
    'formLoginAction' => 'Log in',
    'formPasswordResetAction' => 'Change password',
    'formPasswordCreateAction' => 'Create password',
    'formArticleUri' => 'Article URI',
    'formTimezone' => 'Timezone',

    'dashboardGoTo' => 'Go to...',
    'dashboardHomepage' => 'Homepage',

    'globalYes' => 'Yes',
    'globalNo' => 'No',
    'globalBy' => 'by',
    'globalSettings' => 'Settings',
    'globalClose' => 'Close',
    'globalRemove' => 'Remove',
    'globalFormat' => 'Format',
    'globalRequired' => 'Required',
    'globalNew' => 'New',
    'globalEdit' => 'Edit',
    'globalTitle' => 'Title',
    'globalName' => 'Name',
    'globalEmail' => 'Email',
    'globalPassword' => 'Password',
    'globalLanguage' => 'Language',
    'globalRole' => 'Role',
    'globalTimezone' => 'Timezone',
    'globalBio' => 'Bio',
    'globalSave' => 'Save',
    'globalUpdate' => 'Update',
    'globalUpdated' => 'Updated',
    'globalUpdatedAt' => 'Updated At',
    'globalSubmittedAt' => 'Submitted At',
    'globalPublishOn' => 'Publish On',
    'globalCreated' => 'Created',
    'globalStatus' => 'Status',
    'globalPreview' => 'Preview',
    'globalArticle' => 'Article',
    'globalTags' => 'Tags',
    'globalDateFormat' => 'dd/mm/yyyy',
    'globalActivated' => 'Enabled',
    'globalDeactivated' => 'Disabled',
    'globalUser' => 'User',
    'globalUserAccount' => 'User Account',
    'globalUserAccountSettings' => 'User Account Settings',
    'globalNext' => 'Next',
    'globalNoTitle' => 'No Title',
    'globalComingSoon' => 'Coming soon...',

    'globalUploadImage' => 'Upload image(s)',
    'globalAddImage' => 'Add image(s)',
    'globalAddParagraph' => 'Add text',
    'globalAddTextTitle' => 'Add title',
    'globalAddTextSubtitle' => 'Add subtitle',
    'globalAddVideo' => 'Add video',
    'globalAddHtml' => 'Add HTML',

    'globalGenericError' => 'Something went wrong, please try again.',
    'globalPageNotFoundTitle' => 'Page Not Found :(',
    'globalPageNotFoundContent' => 'The page you are looking for does not exist.',

    'articleEditHomepageTitle' => 'Edit Homepage Content',
    'articleStatusDraft' => 'Draft',
    'articleStatusPublished' => 'Published',
    'articleStatusDeleted' => 'Deleted',
    'articleTypeHome' => 'Homepage',
    'articleTypeArchived' => 'Archived',
    'articleTypeBlog' => 'Blog',
    'articleTypeArticle' => 'Article',
    'paragraphPlaceholder' => 'Type here...',

    'userRoleAdmin' => 'Admin',
    'userRoleUser' => 'User',

    'sectionRemove' => 'Remove section',
    'sectionMoveUp' => 'Move Up',
    'sectionMoveDown' => 'Move Down',

    'emailConfirmationSubject' => 'Welcome to %s! Confirm Your Email',
    'emailConfirmationContent' => '<p>Welcome!</p>' .
        '<p>By clicking on the following link, you are confirming your email address.</p>' .
        '<p><a href="%s">Confirm Your Email</a></p>' .
        '<p>%s</p>',
    'emailUpdateVerificationSubject' => 'Verify Your Email',
    'emailUpdateVerificationContent' => '<p>Hi,</p>' .
        '<p>We received a request to change you email address for your %s account.</p>' .
        '<p>If you did not make this request, just ignore this email. Otherwise, please click the link below to change you email:</p>' .
        '<p><a href="%s">Confirm Your Email</a></p>' .
        '<p>%s</p>',
    'emailPasswordChangeSubject' => '%s Password Reset',
    'emailPasswordChangeContent' => '<p>Hi there,</p>' .
        '<p>We received a request to change the password for your %s account.</p>' .
        '<p>If you did not make this request, just ignore this email. Otherwise, please click the link below to reset your password:</p>' .
        '<p><a href="%s">Change Password</a></p>' .
        '<p>%s</p>',
    'emailPasswordCreationSubject' => '%s: Your new account',
    'emailPasswordCreationContent' => '<p>Hi %s,</p>' .
        '<p>Click the link below to create your password for your new %s account.</p>' .
        '<p>User: %s</p>' .
        '<p><a href="%s">Create password</a></p>' .
        '<p>%s</p>',
];
