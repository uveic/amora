<?php

use Amora\Core\Module\User\Service\UserService;

return [
    'navDashboard' => 'Dashboard',
    'navAdministrator' => 'Admin',
    'navAdminDashboard' => 'Dashboard',
    'navAccount' => 'Account',
    'navSignOut' => 'Sign Out',
    'navSignIn' => 'Sign In',
    'navSignUp' => 'Sign Up',
    'navChangePassword' => 'Change Password',
    'navCreatePassword' => 'Create Password',
    'navDownloadAccountData' => 'Download Account Data',
    'navDeleteAccount' => 'Delete Account',
    'navAdminUsers' => 'Users',
    'navAdminAnalytics' => 'Analytics',
    'navAdminEmails' => 'Emails',
    'navAdminContent' => 'Content',
    'navAdminAlbums' => 'Albums',
    'navAdminImages' => 'Images',
    'navAdminMedia' => 'Files',
    'navAdminArticles' => 'Pages',
    'navAdminBlogPosts' => 'Blog Posts',
    'navAdminArticleOptions' => 'Settings',
    'navAdminPageContentEdit' => 'Copy',

    'editorTitlePlaceholder' => 'Title...',
    'editorSubtitlePlaceholder' => 'Subtitle...',
    'editorDisableControls' => 'Disable controls',
    'editorEnableControls' => 'Enable controls',
    'editorMainImage' => 'Main image',
    'editorMainImageActionTitle' => 'Select main image',

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
    'authenticationInviteRequestSubtitle' => 'The site is currently in private beta, we are making sure everything works as expected and looking forward to getting it ready for you. Enter your email address and we\'ll send you an invitation as soon as it\'s ready. We respect your privacy, your email address will only be used to send you an invitation. Thank you for your patience!',
    'authenticationInviteRequestActionSuccess' => '<h2>Invitation request received</h2><p>We will send your invitation as soon as everything is ready. Thank you for your patience!</p><p>Your email: <b><span id="register-feedback-email"></span></b>.</p>',
    'authenticationInviteRequestHomeLink' => 'Go back to homepage',
    'authenticationInviteRequestFormAction' => 'Request invitation',
    'authenticationVerifyEmailBannerTitle' => 'Verify your email address',
    'authenticationVerifyEmailBannerContent' => 'Please verify your account following the instructions sent to your email address at <b>%s</b>. If you have not received it, please check your spam folder or <a class="verified-link" data-user-id="%d" href="#">click here</a> and we will send you another one. If necessary you can <a href="/en/account">update your email address here</a>.',

    'formYourAccount' => 'Your Account',
    'formPlaceholderUserName' => 'Name',
    'formPlaceholderUserHelp' => 'Min three characters',
    'formEmail' => 'Your Email Address',
    'formEmailNewUserHelp' => 'An email to create a new password will be sent to the user once created.',
    'formPlaceholderEmail' => 'name@example.com',
    'formEmailUpdateWarning' => 'Please, verify your new email address (%s) following the indications sent by email.',
    'formPlaceholderPassword' => 'Your password',
    'formPlaceholderCreatePassword' => 'Create a password',
    'formPlaceholderPasswordNew' => 'New password',
    'formPlaceholderPasswordConfirmation' => 'Repeat password',
    'formLoginAction' => 'Log in',
    'formPasswordResetAction' => 'Change password',
    'formPasswordCreateAction' => 'Create password',
    'formArticlePath' => 'Article path',
    'formArticlePreviousPaths' => 'Previous paths',
    'formTimezone' => 'Timezone',

    'dashboardGoTo' => 'Go to...',
    'dashboardHomepage' => 'Homepage',
    'dashboardShortcuts' => 'Shortcuts',
    'dashboardNewBlogPost' => 'Create new blog post',

    'globalYes' => 'Yes',
    'globalNo' => 'No',
    'globalBy' => 'by',
    'globalOf' => 'of',
    'globalSettings' => 'Settings',
    'globalClose' => 'Close',
    'globalRemove' => 'Remove',
    'globalFormat' => 'Format',
    'globalRequired' => 'Required',
    'globalNew' => 'New',
    'globalAdd' => 'Add',
    'globalEdit' => 'Edit',
    'globalModify' => 'Modify',
    'globalTitle' => 'Title',
    'globalSubtitle' => 'Subtitle',
    'globalContent' => 'Content',
    'globalName' => 'Name',
    'globalEmail' => 'Email',
    'globalPassword' => 'Password',
    'globalLanguage' => 'Language',
    'globalRole' => 'Role',
    'globalTimezone' => 'Timezone',
    'globalBio' => 'Bio',
    'globalCancel' => 'Cancel',
    'globalSave' => 'Save',
    'globalSend' => 'Send',
    'globalUpdate' => 'Update',
    'globalUpdated' => 'Updated',
    'globalUpdatedAt' => 'Updated At',
    'globalSubmittedAt' => 'Submitted At',
    'globalPublishOn' => 'Publish On',
    'globalCreated' => 'Created',
    'globalCreatedAt' => 'Created at',
    'globalStatus' => 'Status',
    'globalPreview' => 'Preview',
    'globalArticle' => 'Page',
    'globalBlogPost' => 'Post',
    'globalTags' => 'Tags',
    'globalDateFormat' => 'dd/mm/yyyy',
    'globalActivated' => 'Enabled',
    'globalDeactivated' => 'Disabled',
    'globalUser' => 'User',
    'globalUserAccount' => 'User Account',
    'globalUserAccountSettings' => 'User Account Settings',
    'globalPrevious' => 'Previous',
    'globalNext' => 'Next',
    'globalNoTitle' => 'No Title',
    'globalComingSoon' => 'Coming soon...',
    'globalLoading' => 'Loading...',
    'globalSaving' => 'Saving...',
    'globalSending' => 'Sending...',
    'globalMore' => 'More...',
    'globalSequence' => 'Sequence',

    'globalUploadImage' => 'Upload image(s)',
    'globalUploadMedia' => 'Upload file(s)',
    'globalAddImage' => 'Add image(s)',
    'globalRemoveImage' => 'Remove image',
    'globalSelectImage' => 'Select image',
    'globalImages' => 'images',
    'globalAddParagraph' => 'Add text',
    'globalAddTextTitle' => 'Add title',
    'globalAddTextSubtitle' => 'Add subtitle',
    'globalAddVideo' => 'Add video',
    'globalAddHtml' => 'Add HTML',

    'globalGenericError' => 'Something went wrong, please try again.',
    'globalPageNotFoundTitle' => 'Page Not Found :(',
    'globalPageNotFoundContent' => 'The page you are looking for does not exist.',
    'globalPageDeactivatedTitle' => 'Temporarily deactivated',
    'globalPageDeactivatedContent' => 'The page you are looking for is temporarily deactivated. You can get in touch at <a href="mailto:contacto@contame.es">contacto@contame.es</a>. Sorry for the inconvenience.',

    'articleStatusDraft' => 'Draft',
    'articleStatusPublished' => 'Published',
    'articleStatusDeleted' => 'Deleted',
    'articleStatusPrivate' => 'Private',
    'articleStatusUnlisted' => 'Unlisted',
    'articleTypeBlog' => 'Blog',
    'articleTypePage' => 'Page',
    'paragraphPlaceholder' => 'Type here...',
    'formFilterTitle' => 'Filter',
    'formFilterButton' => 'Filter',
    'formFilterClean' => 'Reset filter',
    'formFilterArticleTypeTitle' => 'Type',

    'pageContentEditTitle' => 'Edit content',
    'pageContentEditTitleHomepage' => 'Edit homepage content',
    'pageContentEditTitleBlogBottom' => 'Edit the content that follows a blog post',

    'mediaUploadedBy' => 'Uploaded by %s on %s',
    'mediaSelectImageForArticle' => 'Select image to add to article',

    'userRoleAdmin' => 'Admin',
    'userRoleUser' => 'User',
    'userStatusEnabled' => 'Enabled',
    'userStatusDisabled' => 'Disabled',
    'userJourneyRegistration' => 'Registration Complete',
    'userJourneyPendingPasswordCreation' => 'Pending Password Creation',

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

    'analyticsToday' => 'Today',
    'analyticsSource' => 'Source',
    'analyticsPage' => 'Page',
    'analyticsBrowser' => 'Browser',
    'analyticsDevice' => 'Device',
    'analyticsCountry' => 'Country',
    'analyticsLanguage' => 'Language',
    'analyticsPageViews' => 'Page Views',
    'analyticsVisitors' => 'Visitors',
    'analyticsEventTypeAll' => 'All',
    'analyticsEventTypeVisitor' => 'Visitors',
    'analyticsEventTypeUser' => 'Registered Users',
    'analyticsEventTypeBot' => 'Bots',
    'analyticsEventTypeProbablyBot' => 'Probably a Bot',
    'analyticsEventTypeApi' => 'API',
    'analyticsEventTypeCrawler' => 'Crawler',

    'mailerListTitle' => 'Sent Emails Log',
    'mailerListNoError' => 'Sent',
    'mailerListError' => 'Error',
    'mailerTemplateAccountVerification' => 'Account Verification',
    'mailerTemplatePasswordCreation' => 'Password Creation',
    'mailerTemplatePasswordReset' => 'Password Reset',

    'albumFormContent' => 'Content',
    'albumFormNew' => 'New album',
    'albumFormMainImageTitle' => 'Main image',
    'albumFormPublicLinkTitle' => 'Public address',
    'albumFormTemplateTitle' => 'Album template',
    'albumPublicReadMore' => 'Read more',
    'albumPublicMorePictures' => 'More Pictures',
    'albumAddSection' => 'Add section',
];
