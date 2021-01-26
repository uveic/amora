<?php

use uve\core\module\user\service\UserService;

return [
    'siteName' => 'Victor Gonzalez',
    'siteTitle' => '',
    'siteDescription' => 'Soy un programador web.',

    'navAccount' => 'Tus datos',
    'navSignOut' => 'Salir',
    'navSignIn' => 'Entra',
    'navSignUp' => 'Regístrate',
    'navChangePassword' => 'Cambiar contraseña',
    'navDownloadAccountData' => 'Descargar tus datos',
    'navDeleteAccount' => 'Eliminar cuenta',
    'navAdminDashboard' => 'Escritorio',
    'navAdminUsers' => 'Usuarios',
    'navAdminImages' => 'Imágenes',
    'navAdminArticles' => 'Artículos',

    'authenticationActionHomeLink' => 'Volver a la página de entrada',
    'authenticationLoginSubtitle' => '¡Bienvenido/a de nuevo, adelante!',
    'authenticationRegisterSubtitle' => 'A un paso.',
    'authenticationRegisterTOS' => 'Al registrarte aceptas los <a href="#">términos de uso</a> y la <a href="#">política de privacidad</a>.',
    'authenticationRegisterPasswordHelp' => 'Longitud mínima: ' . UserService::USER_PASSWORD_MIN_LENGTH . ' caracteres. Recomenable: letras, números y símbolos.',
    'authenticationRegisterAlreadyLogin' => '¿Ya tienes una cuenta?',

    'authenticationForgotPassword' => '¿Olvidaste tu contraseña?',
    'authenticationForgotPasswordSubtitle' => 'No te preocupes, introduce el correo electrónico que utilizaste para registrarte y te enviaremos un enlace para restaurar tu contraseña.',
    'authenticationForgotPasswordAction' => 'Enviar enlace de restauración',
    'authenticationForgotPasswordActionSuccess' => 'Correo enviado. Comprueba la bandeja de entrada de tu correo electrónico (<b><span id="register-feedback-email"></span></b>) y sigue las instruciones.',

    'authenticationPasswordResetSubtitle' => 'Cambia tu contraseña',
    'authenticationPasswordResetActionSuccess' => 'Se ha cambiado la contraseña correctamente.',
    'authenticationPasswordResetAlreadyLogin' => '¿Quieres <a href="%s/login">entrar en tu cuenta</a> sin cambiar tu contraseña?',

    'authenticationInviteRequest' => 'Consigue una invitación',
    'authenticationInviteRequestSubtitle' => 'La web está actualmente en beta privada, estamos trajando para asegurarnos de que todo funciona correctamente. Tenemos muchas ganas de que esté lista para que la puedas utilizar. Déjanos tu correo y te enviaremos una invitación tan pronto como sea posible. Solo utilizaremos tu correo para enviarte la invitación. <br>¡Gracias por tu paciencia!',
    'authenticationInviteRequestActionSuccess' => '<h2>Petición recibida correctamente</h2><p>Tu correo: <b><span id="register-feedback-email"></span></b>.</p><p>¡Gracias!</p>',
    'authenticationInviteRequestHomeLink' => 'Volver a la página principal',
    'authenticationInviteRequestFormAction' => 'Pedir una invitación',

    'formPlaceholderUserName' => 'Tu nombre',
    'formPlaceholderEmail' => 'Tu correo electrónico',
    'formPlaceholderPassword' => 'Tu contraseña',
    'formPlaceholderCreatePassword' => 'Crea una contraseña',
    'formPlaceholderPasswordNew' => 'Nueva contraseña',
    'formPlaceholderPasswordConfirmation' => 'Repite la contraseña',
    'formLoginAction' => 'Entrar',
    'formPasswordResetAction' => 'Cambiar contraseña',

    'dashboardGoTo' => 'Ir a...',

    'globalRequired' => 'Obligatorio',
    'globalNew' => 'Nuevo',
    'globalEdit' => 'Editar',
    'globalTitle' => 'Título',
    'globalSave' => 'Guardar',
    'globalUpdate' => 'Actualizar',
    'globalSaveAndClose' => 'GyC',
    'globalUpdateAndClose' => 'AyC',
    'globalUpdated' => 'Actualizado',
    'globalUpdatedAt' => 'Actualizado',
    'globalCreated' => 'Creado',
    'globalStatus' => 'Estatus',
    'globalUploadImage' => 'Subir imagen(es)',
    'globalPreview' => 'Previsualizar',

    'globalArticle' => 'artículo',
];
