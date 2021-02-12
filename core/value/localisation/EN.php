<?php

use uve\core\module\user\service\UserService;

return [
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
    'authenticationForgotPasswordActionSuccess' => 'Email sent (<b><span id="register-feedback-email"></span></b>). Check your inbox and follow the instructions.',
    'authenticationEmailVerified' => 'Email address verified successfully.',
    'authenticationEmailVerifiedError' => 'The link to verify your email address is not valid.',
    'authenticationPasswordResetLinkError' => 'The link to reset your password is not valid. Please start the process again.',
    'authenticationEmailAndOrPassNotValid' => 'Email address and/or password not valid.',
    'authenticationEmailNotValid' => 'Email address not valid.',
    'authenticationUserRegistrationDisabled' => 'The user registration functionality is not enabled. Please, contact the site administrator.',
    'authenticationPasswordTooShort' => 'The password is too short. Please, fix it and try it again.',
    'authenticationPasswordsDoNotMatch' => 'The passwords do not match. Please, fix it and try it again.',
    'authenticationRegistrationErrorExistingEmail' => 'There is another account with the same email address. Please, log in to your account <a href="%s">here</a>.',

    'authenticationPasswordResetSubtitle' => 'Change your password',
    'authenticationPasswordResetActionSuccess' => 'Password changed successfully.',
    'authenticationPasswordResetAlreadyLogin' => 'Do you want to <a href="%s">log in to your account</a> without changing your password?',

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
    'formArticleUri' => 'Article URI',

    'dashboardGoTo' => 'Go to...',

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
    'globalLanguage' => 'Language',
    'globalRole' => 'Role',
    'globalTimezone' => 'Timezone',
    'globalBio' => 'Bio',
    'globalSave' => 'Save',
    'globalUpdate' => 'Update',
    'globalSaveAndClose' => 'S&C',
    'globalUpdateAndClose' => 'U&C',
    'globalUpdated' => 'Updated',
    'globalUpdatedAt' => 'Updated At',
    'globalPublishOn' => 'Publish On',
    'globalCreated' => 'Created',
    'globalStatus' => 'Status',
    'globalPreview' => 'Preview',
    'globalArticle' => 'Article',
    'globalCategory' => 'Type',
    'globalTags' => 'Tags',
    'globalDateFormat' => 'dd/mm/yyyy',
    'globalActivated' => 'Enabled',
    'globalDeactivated' => 'Disabled',
    'globalUser' => 'User',
    'globalUserAccount' => 'User Account',
    'globalUserAccountSettings' => 'User Account Settings',

    'globalUploadImage' => 'Upload image(s)',
    'globalAddImage' => 'Add image(s)',
    'globalAddParagraph' => 'Add text',
    'globalAddTextTitle' => 'Add title',
    'globalAddTextSubtitle' => 'Add subtitle',
    'globalAddVideo' => 'Add video',
    'globalAddHtml' => 'Add HTML',

    'globalGenericError' => 'Something went wrong, please try again',
    'globalGenericNotFound' => 'Not found',

    'articleStatusDraft' => 'Draft',
    'articleStatusPublished' => 'Published',
    'articleStatusDeleted' => 'Deleted',
    'articleTypeHome' => 'Homepage',
    'articleTypeArchived' => 'Archived',

    'sectionRemove' => 'Remove section',
    'sectionMoveUp' => 'Move Up',
    'sectionMoveDown' => 'Move Down',
];
