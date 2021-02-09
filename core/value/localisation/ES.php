<?php

use uve\core\module\user\service\UserService;

return [
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
    'navAdminArticleOptions' => 'Opciones artículo',

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
    'authenticationEmailVerified' => 'Correo electrónico verificado correctamente.',
    'authenticationEmailVerifiedError' => 'El enlace para verificar tu correo electrónico no es válido.',
    'authenticationPasswordResetLinkError' => 'El enlace para cambiar tu contraseña no es válido. Inicia el proceso de cambio de contraseña de nuevo.',
    'authenticationEmailAndOrPassNotValid' => 'Correo electrónico y/o contresña no válidos.',
    'authenticationEmailNotValid' => 'Correo electrónico no válido.',
    'authenticationUserRegistrationDisabled' => 'El registro de usuarios no está habilitado. Ponte en contacto con el administrador de la web.',
    'authenticationPasswordTooShort' => 'La contraseña es demasiado corta. Cámbiala e inténtalo de nuevo',
    'authenticationPasswordsDoNotMatch' => 'Las contraseñas no coinciden. Corrígelo e inténtalo de nuevo.',
    'authenticationRegistrationErrorExistingEmail' => 'Ya existe otra cuenta con el mismo correo electrónico. Por favor, identifícate <a href="%s">aquí</a>.',

    'authenticationPasswordResetSubtitle' => 'Cambia tu contraseña',
    'authenticationPasswordResetActionSuccess' => 'Se ha cambiado la contraseña correctamente.',
    'authenticationPasswordResetAlreadyLogin' => '¿Quieres <a href="%s">entrar en tu cuenta</a> sin cambiar tu contraseña?',

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
    'formArticleUri' => 'URI del artículo',

    'dashboardGoTo' => 'Ir a...',

    'globalSettings' => 'Configuración',
    'globalClose' => 'Cerrar',
    'globalFormat' => 'Formato',
    'globalRequired' => 'Obligatorio',
    'globalNew' => 'Nuevo',
    'globalEdit' => 'Editar',
    'globalTitle' => 'Título',
    'globalName' => 'Nombre',
    'globalEmail' => 'Correo electrónico',
    'globalLanguage' => 'Idioma',
    'globalRole' => 'Rol',
    'globalTimezone' => 'Zona horaria',
    'globalBio' => 'Breve biografía',
    'globalSave' => 'Guardar',
    'globalUpdate' => 'Actualizar',
    'globalSaveAndClose' => 'GyC',
    'globalUpdateAndClose' => 'AyC',
    'globalUpdated' => 'Actualizado',
    'globalUpdatedAt' => 'Actualizado',
    'globalPublishOn' => 'Publicar el',
    'globalCreated' => 'Creado',
    'globalStatus' => 'Estatus',
    'globalPreview' => 'Previsualizar',
    'globalArticle' => 'artículo',
    'globalCategory' => 'Categoría',
    'globalTags' => 'Etiquetas',
    'globalDateFormat' => 'dd/mm/aaaa',
    'globalActivated' => 'Activado',
    'globalDeactivated' => 'Desactivado',
    'globalUser' => 'Ususario',
    'globalUserAccount' => 'Cuenta de usuario',
    'globalUserAccountSettings' => 'Configuración de cuenta de usuario',

    'globalUploadImage' => 'Subir imagen(es)',
    'globalAddImage' => 'Añadir imagen(es)',
    'globalAddParagraph' => 'Añadir párrafo',
    'globalAddTextTitle' => 'Añadir título',
    'globalAddTextSubtitle' => 'Añadir subtítulo',
    'globalAddVideo' => 'Añadir vídeo',
    'globalAddHtml' => 'Añadir HTML',

    'globalGenericError' => 'Ha ocurrido un error inesperado, por favor inténtalo de nuevo',
    'globalGenericNotFound' => 'No encontrado',

    'articleStatusDraft' => 'Borrador',
    'articleStatusPublished' => 'Publicado',
    'articleStatusDeleted' => 'Eliminado',

    'sectionRemove' => 'Eliminar sección',
    'sectionMoveUp' => 'Mover para arriba',
    'sectionMoveDown' => 'Mover para abajo',
];
