<?php

use Amora\Core\Module\User\Service\UserService;

return [
    'navDashboard' => 'Inicio',
    'navAccount' => 'Tus datos',
    'navSignOut' => 'Salir',
    'navSignIn' => 'Entra',
    'navSignUp' => 'Regístrate',
    'navChangePassword' => 'Cambiar contraseña',
    'navCreatePassword' => 'Crear contraseña',
    'navDownloadAccountData' => 'Descargar tus datos',
    'navDeleteAccount' => 'Eliminar cuenta',
    'navAdministrator' => 'Admin',
    'navAdminDashboard' => 'Inicio',
    'navAdminUsers' => 'Usuarios',
    'navAdminContent' => 'Contenido',
    'navAdminImages' => 'Imágenes',
    'navAdminArticles' => 'Artículos',
    'navAdminArticleOptions' => 'Opciones artículo',

    'authenticationActionHomeLink' => 'Ir a la página de entrada',
    'authenticationLoginSubtitle' => '¡Bienvenido/a de nuevo!',
    'authenticationRegisterSubtitle' => 'A un paso.',
    'authenticationRegisterTOS' => 'Al registrarte aceptas los <a href="#">términos de uso</a> y la <a href="#">política de privacidad</a>.',
    'authenticationRegisterPasswordHelp' => 'Longitud mínima: ' . UserService::USER_PASSWORD_MIN_LENGTH . ' caracteres. Recomenable: letras, números y símbolos.',
    'authenticationRegisterAlreadyLogin' => '¿Ya tienes una cuenta?',

    'authenticationForgotPassword' => '¿Olvidaste tu contraseña?',
    'authenticationForgotPasswordSubtitle' => 'No te preocupes, introduce el correo electrónico que utilizaste para registrarte y te enviaremos un enlace para restaurar tu contraseña.',
    'authenticationForgotPasswordAction' => 'Enviar enlace de restauración',
    'authenticationForgotPasswordActionSuccess' => 'Si tenemos tu dirección (<b><span id="register-feedback-email"></span></b>) en nuestra base de datos ya te hemos enviado un correo para restaurar tu contraseña. Comprueba la bandeja de entrada y sigue las instruciones.',
    'authenticationEmailVerified' => 'Correo electrónico verificado correctamente.',
    'authenticationEmailVerifiedError' => 'El enlace para verificar tu correo electrónico no es válido.',
    'authenticationEmailVerifiedExpired' => 'El enlace para verificar tu correo electrónico ha caducado. Por favor, inicia el proceso de nuevo.',
    'authenticationPasswordResetLinkError' => 'El enlace para cambiar tu contraseña no es válido. Inicia el proceso de cambio de contraseña de nuevo.',
    'authenticationPasswordCreationLinkError' => 'El enlace para crear tu contraseña no es válido. Por favor, cambia tu contraseña o contacta con el/la administrador/a de la página.',
    'authenticationEmailAndOrPassNotValid' => 'Correo electrónico y/o contresña no válidos.',
    'authenticationEmailNotValid' => 'Correo electrónico no válido.',
    'authenticationUserRegistrationDisabled' => 'El registro de usuarios no está habilitado. Ponte en contacto con el administrador de la web.',
    'authenticationPasswordTooShort' => 'La contraseña es demasiado corta. Cámbiala e inténtalo de nuevo',
    'authenticationPasswordsDoNotMatch' => 'Las contraseñas no coinciden. Corrígelo e inténtalo de nuevo.',
    'authenticationRegistrationErrorExistingEmail' => 'Ya existe otra cuenta con el mismo correo electrónico. Por favor, identifícate <a href="%s">aquí</a>.',

    'authenticationPasswordResetSubtitle' => 'Cambia tu contraseña',
    'authenticationPasswordResetActionSuccess' => 'Se ha cambiado la contraseña correctamente.',
    'authenticationPasswordResetAlreadyLogin' => '¿Quieres <a href="%s">entrar en tu cuenta</a> sin cambiar tu contraseña?',
    'authenticationPasswordCreateSubtitle' => 'Crea tu contraseña',
    'authenticationPasswordCreationActionSuccess' => 'Contraseña creada correctamente.',

    'authenticationInviteRequest' => 'Consigue una invitación',
    'authenticationInviteRequestSubtitle' => 'La web está actualmente en beta privada, estamos trajando para asegurarnos de que todo funciona correctamente. Tenemos muchas ganas de que esté lista para que la puedas utilizar. Déjanos tu correo y te enviaremos una invitación tan pronto como sea posible. Solo utilizaremos tu correo para enviarte la invitación. <br>¡Gracias por tu paciencia!',
    'authenticationInviteRequestActionSuccess' => '<h2>Petición recibida correctamente</h2><p>Te enviaremos tu invitación en cuanto tengamos todo listo. ¡Gracias por la espera!</p><p>Tu correo: <b><span id="register-feedback-email"></span></b>.</p>',
    'authenticationInviteRequestHomeLink' => 'Volver a la página principal',
    'authenticationInviteRequestFormAction' => 'Pedir una invitación',
    'authenticationVerifyEmailBanner' => 'Por favor, verifica tu cuenta siguiendo las instruciones que te enviamos en un correo a <b>%s</b>. Si no lo has recibido revisa la carpeta de correo basura o <a class="verified-link" data-user-id="$d" href="#">haz clic aquí</a> y te enviaremos otro. Puedes <a href="/es/account">modificar tu dirección de correo</a> si fuese necesario.',
    'authenticationPassNotValid' => 'La contraseña actual no es correcta.',

    'formYourAccount' => 'Tu cuenta',
    'formPlaceholderUserName' => 'Nombre',
    'formPlaceholderUserHelp' => 'Mínimo tres letras',
    'formEmail' => 'Tu correo electrónico',
    'formEmailNewUserHelp' => 'Se le enviará un correo electrónico para crear la contraseña una vez guardado.',
    'formPlaceholderEmail' => 'nombre@ejemplo.com',
    'formEmailUpdateWarning' => 'Por favor, verifica tu nuevo correo electrónico (%s) para cambiarlo.',
    'formPlaceholderPassword' => 'Tu contraseña',
    'formPlaceholderCreatePassword' => 'Crea una contraseña',
    'formPlaceholderPasswordNew' => 'Nueva contraseña',
    'formPlaceholderPasswordConfirmation' => 'Repite la contraseña',
    'formLoginAction' => 'Entrar',
    'formPasswordResetAction' => 'Cambiar contraseña',
    'formPasswordCreateAction' => 'Crear contraseña',
    'formArticleUri' => 'URI del artículo',
    'formTimezone' => 'Zona horaria',

    'dashboardGoTo' => 'Ir a...',
    'dashboardHomepage' => 'Portada',

    'globalYes' => 'Sí',
    'globalNo' => 'No',
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
    'globalPassword' => 'Contraseña',
    'globalLanguage' => 'Idioma',
    'globalRole' => 'Rol',
    'globalTimezone' => 'Zona horaria',
    'globalBio' => 'Breve biografía',
    'globalSave' => 'Guardar',
    'globalUpdate' => 'Actualizar',
    'globalUpdated' => 'Actualizado',
    'globalUpdatedAt' => 'Actualizado',
    'globalSubmittedAt' => 'Hora envío',
    'globalPublishOn' => 'Publicar el',
    'globalCreated' => 'Creado',
    'globalStatus' => 'Estado',
    'globalPreview' => 'Previsualizar',
    'globalArticle' => 'artículo',
    'globalTags' => 'Etiquetas',
    'globalDateFormat' => 'dd/mm/aaaa',
    'globalActivated' => 'Activado',
    'globalDeactivated' => 'Desactivado',
    'globalUser' => 'Ususario/a',
    'globalUserAccount' => 'Cuenta de usuario/a',
    'globalUserAccountSettings' => 'Configuración de cuenta de usuario/a',
    'globalNext' => 'Siguiente',
    'globalNoTitle' => 'Sin título',
    'globalComingSoon' => 'Próximamente...',

    'globalUploadImage' => 'Subir imagen(es)',
    'globalAddImage' => 'Añadir imagen(es)',
    'globalAddParagraph' => 'Añadir párrafo',
    'globalAddTextTitle' => 'Añadir título',
    'globalAddTextSubtitle' => 'Añadir subtítulo',
    'globalAddVideo' => 'Añadir vídeo',
    'globalAddHtml' => 'Añadir HTML',

    'globalGenericError' => 'Ha ocurrido un error inesperado, por favor inténtalo de nuevo.',
    'globalPageNotFoundTitle' => 'Página no encontrada :(',
    'globalPageNotFoundContent' => 'La página que buscas no existe.',

    'articleEditHomepageTitle' => 'Editar el contenido de la portada',
    'articleStatusDraft' => 'Borrador',
    'articleStatusPublished' => 'Publicado',
    'articleStatusDeleted' => 'Eliminado',
    'articleTypeHome' => 'Portada',
    'articleTypeArchived' => 'Archivado',
    'articleTypeBlog' => 'Blog',
    'articleTypeArticle' => 'Artículo',
    'paragraphPlaceholder' => 'Escribe aquí...',

    'userRoleAdmin' => 'Administrador/a',
    'userRoleUser' => 'Usuario/a',

    'sectionRemove' => 'Eliminar sección',
    'sectionMoveUp' => 'Mover para arriba',
    'sectionMoveDown' => 'Mover para abajo',

    'emailConfirmationSubject' => 'Bienvenido/a a %s! Confirma tu correo electrónico',
    'emailConfirmationContent' => '<p>¡Bienvenido/a!</p>' .
        '<p>Por favor, confirma tu correo electrónico haciendo clic en el siguiente enlace.</p>' .
        '<p><a href="%s">Confirmar correo electrónico</a></p>' .
        '<p>%s</p>',
    'emailUpdateVerificationSubject' => 'Verifica tu correo electrónico',
    'emailUpdateVerificationContent' => '<p>Hola,</p>' .
        '<p>Hemos recibido una petición de cambio de correo electrónico en tu cuenta de %s.</p>' .
        '<p>Si no has sido tú el que ha hecho esta petición puedes ignorar este correo. En caso contrario, haz clic en el siguiente enlace para confirmar tu nuevo correo:</p>' .
        '<p><a href="%s">Confirma tu correo electrónico</a></p>' .
        '<p>%s</p>',
    'emailPasswordChangeSubject' => '%s: cambiar contraseña',
    'emailPasswordChangeContent' => '<p>Hola,</p>' .
        '<p>Hemos recibido una petición de cambio de contraseña en tu cuenta de %s.</p>' .
        '<p>Si no has hecho tú esta petición puedes ignorar este correo electrónico. En caso contrario, haz clic en el siguiente enlace para cambiar tu contraseña:</p>' .
        '<p><a href="%s">Cambiar contraseña</a></p>' .
        '<p>%s</p>',
    'emailPasswordCreationSubject' => 'Tu nueva cuenta en %s',
    'emailPasswordCreationContent' => '<p>Hola %s,</p>' .
        '<p>Haz clic en el siguiente enlace para crear tu contraseña de tu nueva cuenta en %s.</p>' .
        '<p>Usuario: %s</p>' .
        '<p><a href="%s">Crear contraseña</a></p>' .
        '<p>%s</p>',
];
