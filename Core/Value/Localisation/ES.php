<?php

use Amora\Core\Module\User\Service\UserService;

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
    'authenticationLoginSubtitle' => '¡Bienvenido/a de nuevo!',
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
    'authenticationInviteRequestActionSuccess' => '<h2>Petición recibida correctamente</h2><p>Te enviaremos tu invitación en cuanto tengamos todo listo. ¡Gracias por la espera!</p><p>Tu correo: <b><span id="register-feedback-email"></span></b>.</p>',
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

    'globalBy' => 'por',
    'globalSettings' => 'Configuración',
    'globalClose' => 'Cerrar',
    'globalRemove' => 'Eliminar',
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
    'globalNext' => 'Siguiente',

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
    'articleTypeHome' => 'Portada',
    'articleTypeArchived' => 'Archivado',
    'paragraphPlaceholder' => 'Escribe aquí...',

    'sectionRemove' => 'Eliminar sección',
    'sectionMoveUp' => 'Mover para arriba',
    'sectionMoveDown' => 'Mover para abajo',

    'emailVerificationSubject' => 'Bienvenido/a a %s! Confirma tu correo electrónico',
    'emailVerificationContent' => '<p>¡Bienvenido/a!</p>' .
        '<p>Por favor, confirma tu correo electrónico haciendo clic en el siguiente enlace.</p>' .
        '<p><a href="%s">Confirmar correo electrónico</a></p>' .
        '<p>Gracias,<br>%s</p>',
    'emailPasswordChangeSubject' => '%s: cambiar contraseña',
    'emailPasswordChangeContent' => '<p>Hola,</p>' .
        '<p>Hemos recibido una petición de cambio de contraseña en tu cuenta de %s.</p>' .
        '<p>Si no has hecho tú esta petición puedes ignorar este correo electrónico. En caso contrario, haz clic en el siguiente enlace para cambiar tu contraseña:</p>' .
        '<p><a href="%s">Cambiar contraseña</a></p>' .
        '<p>Saludos,<br>%s</p>',
];
