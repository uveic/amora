<?php

use uve\core\module\user\service\UserService;

return [
    'siteName' => 'Victor Gonzalez',
    'siteTitle' => '',
    'siteDescription' => 'Son un programador web',

    'navAccount' => 'Os teus datos',
    'navSignOut' => 'Saír',
    'navSignIn' => 'Entra',
    'navSignUp' => 'Rexístrate',
    'navChangePassword' => 'Cambiar contrasinal',
    'navDownloadAccountData' => 'Descargar os teus datos',
    'navDeleteAccount' => 'Eliminar conta',
    'navAdminDashboard' => 'Escritorio',
    'navAdminUsers' => 'Usuarios',
    'navAdminImages' => 'Imaxes',
    'navAdminArticles' => 'Artigos',

    'authenticationActionHomeLink' => 'Volver á páxina de entrada',
    'authenticationLoginSubtitle' => 'Benvida/o de volta. Adiante!',
    'authenticationRegisterSubtitle' => 'A un paso.',
    'authenticationRegisterTOS' => 'Ao rexistráreste aceptas os <a href="#">termos de uso</a> e a <a href="#">política de privacidade</a>.',
    'authenticationRegisterPasswordHelp' => 'Lonxitude mínima: ' . UserService::USER_PASSWORD_MIN_LENGTH . ' caracteres. Recomenable: letras, números e símbolos.',
    'authenticationRegisterAlreadyLogin' => 'Xa tes unha conta?',

    'authenticationForgotPassword' => 'Esquecíches o teu contrasinal?',
    'authenticationForgotPasswordSubtitle' => 'Non te preocupes, introduce o correo electrónico co que te rexistraches e enviarémosche unha ligazón para restaurar o teu contrasinal.',
    'authenticationForgotPasswordAction' => 'Enviar ligazón de restauración',
    'authenticationForgotPasswordActionSuccess' => 'Correo enviado. Comproba a bandexa de entrada do teu correo electrónico (<b><span id="register-feedback-email"></span></b>) e segue as instruccións.',

    'authenticationPasswordResetSubtitle' => 'Cambia o contrasinal',
    'authenticationPasswordResetActionSuccess' => 'Cambiouse o contrasinal correctamente.',
    'authenticationPasswordResetAlreadyLogin' => 'Queres <a href="%s/login">entrar na túa conta</a> sen cambiar o contrasinal?',

    'authenticationInviteRequest' => 'Consegue unha invitación',
    'authenticationInviteRequestSubtitle' => 'A web está actualmente nunha beta privada, estamos traballando para asegurarnos de que todo funciona correctamente e con moitas ganas de que estea lista para que a poidas utilizar. Déixanos o teu correo e enviarémosche unha invitación tan pronto como sexa posible. Só utilizaremos o teu correo para enviarche a invitación.<br>Grazas pola túa paciencia!',
    'authenticationInviteRequestActionSuccess' => '<h2>Petición recibida correctamente</h2><p>O teu correo: <b><span id="register-feedback-email"></span></b>.</p><p>Grazas!</p>',
    'authenticationInviteRequestHomeLink' => 'Volver á páxina principal',
    'authenticationInviteRequestFormAction' => 'Pedir invitación',

    'formPlaceholderUserName' => 'O teu nome',
    'formPlaceholderEmail' => 'O teu correo electrónico',
    'formPlaceholderPassword' => 'O teu contrasinal',
    'formPlaceholderCreatePassword' => 'Crea un contrasinal',
    'formPlaceholderPasswordNew' => 'Novo contrasinal',
    'formPlaceholderPasswordConfirmation' => 'Repite o contrasinal',
    'formLoginAction' => 'Entrar',
    'formPasswordResetAction' => 'Cambiar contrasinal',

    'dashboardGoTo' => 'Ir a...',

    'globalRequired' => 'Obrigatorio',
    'globalNew' => 'Novo',
    'globalEdit' => 'Editar',
    'globalTitle' => 'Título',
    'globalSave' => 'Gardar',
    'globalUpdate' => 'Actualizar',
    'globalSaveAndClose' => 'GeC',
    'globalUpdateAndClose' => 'AeC',
    'globalUpdated' => 'Actualizado',
    'globalUpdatedAt' => 'Actualizado',
    'globalCreated' => 'Creado',
    'globalStatus' => 'Estatus',
    'globalUploadImage' => 'Subir imaxe(s)',
    'globalPreview' => 'Previsualizar',

    'globalArticle' => 'artigo',
];
