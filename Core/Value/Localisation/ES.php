<?php

use Amora\Core\Module\User\Service\UserService;

return [
    'navAuthorisedDashboard' => 'Inicio',
    'navAdministrator' => 'Admin',
    'navAdminDashboard' => 'Inicio',
    'navAccount' => 'Tus datos',
    'navSignOut' => 'Salir',
    'navSignIn' => 'Entra',
    'navSignUp' => 'Regístrate',
    'navChangePassword' => 'Cambiar contraseña',
    'navCreatePassword' => 'Crear contraseña',
    'navDownloadAccountData' => 'Descargar tus datos',
    'navDeleteAccount' => 'Eliminar cuenta',
    'navAdminUsers' => 'Usuarios',
    'navAdminAnalytics' => 'Visitas',
    'navAdminEmails' => 'Correos',
    'navAdminContent' => 'Contenido',
    'navAdminAlbums' => 'Álbums',
    'navAdminImages' => 'Imágenes',
    'navAdminMedia' => 'Archivos',
    'navAdminArticles' => 'Páginas',
    'navAdminBlogPosts' => 'Entradas blog',
    'navAdminArticleOptions' => 'Configuración',
    'navAdminPageContentEdit' => 'Textos',

    'editorTitlePlaceholder' => 'Título...',
    'editorSubtitlePlaceholder' => 'Subtítulo...',
    'editorDisableControls' => 'Desactivar controles',
    'editorEnableControls' => 'Activar controles',
    'editorMainImage' => 'Imagen',
    'editorMainImageActionTitle' => 'Selecciona imagen destacada',

    'authenticationActionHomeLink' => 'Ir a la página de entrada',
    'authenticationLoginSubtitle' => '¡Bienvenido/a de nuevo!',
    'authenticationRegisterSubtitle' => 'A un paso.',
    'authenticationRegisterTOS' => 'Al registrarte aceptas los <a href="#">términos de uso</a> y la <a href="#">política de privacidad</a>.',
    'authenticationRegisterPasswordHelp' => 'Longitud mínima: ' . UserService::USER_PASSWORD_MIN_LENGTH . ' caracteres. Recomenable: letras, números y símbolos.',
    'authenticationRegisterAlreadyLogin' => '¿Ya tienes una cuenta?',

    'authenticationForgotPassword' => '¿Olvidaste tu contraseña?',
    'authenticationForgotPasswordSubtitle' => 'No te preocupes, introduce el correo electrónico que utilizaste para registrarte y te enviaremos un enlace para restaurar tu contraseña.',
    'authenticationForgotPasswordAction' => 'Enviar enlace de restauración',
    'authenticationForgotPasswordActionSuccess' => 'Si tenemos tu dirección (<b><span id="register-feedback-email"></span></b>) en nuestra base de datos te enviaremos un correo para restaurar tu contraseña en un instante. Comprueba la bandeja de entrada y sigue las instruciones.',
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
    'authenticationRegistrationErrorExistingEmail' => 'Ya existe otra cuenta con el mismo correo electrónico. Por favor, identifícate.',
    'authenticationPassNotValid' => 'La contraseña actual no es correcta.',

    'authenticationPasswordResetSubtitle' => 'Cambia tu contraseña',
    'authenticationPasswordResetActionSuccess' => 'Se ha cambiado la contraseña correctamente.',
    'authenticationPasswordResetAlreadyLogin' => '¿Quieres <a href="%s">entrar en tu cuenta</a> sin cambiar tu contraseña?',
    'authenticationPasswordCreateSubtitle' => 'Crea tu contraseña',
    'authenticationPasswordCreationActionSuccess' => 'Contraseña creada correctamente.',

    'authenticationInviteRequest' => 'Consigue una invitación',
    'authenticationInviteRequestSubtitle' => 'La web está actualmente en beta privada, estamos trajando para asegurarnos de que todo funciona correctamente. Tenemos muchas ganas de que esté lista para que la puedas utilizar. Déjanos tu correo y te enviaremos una invitación tan pronto como sea posible. Respetamos tu privacidad, solo utilizaremos tu correo para enviarte la invitación. ¡Gracias por tu paciencia!',
    'authenticationInviteRequestActionSuccess' => '<h2>Petición recibida correctamente</h2><p>Te enviaremos tu invitación en cuanto tengamos todo listo. ¡Gracias por la espera!</p><p>Tu correo: <b><span id="register-feedback-email"></span></b>.</p>',
    'authenticationInviteRequestHomeLink' => 'Volver a la página principal',
    'authenticationInviteRequestFormAction' => 'Solicitar invitación',
    'authenticationVerifyEmailBannerTitle' => 'Confirma tu correo eletrónico',
    'authenticationVerifyEmailBannerContent' => 'Por favor, verifica tu cuenta siguiendo las instruciones que te enviamos en un correo a <b>%s</b>. Si no lo has recibido revisa la carpeta de correo basura o <a class="verified-link" data-user-id="$d" href="#">haz clic aquí</a> y te enviaremos otro. Puedes <a href="/es/account">modificar tu dirección de correo</a> si fuese necesario.',

    'formYourAccount' => 'Tu cuenta',
    'formPlaceholderUserName' => 'Nombre',
    'formPlaceholderUserHelp' => 'Mínimo tres letras',
    'formEmail' => 'Tu correo electrónico',
    'formEmailNewUserHelp' => 'Se le enviará un correo electrónico para crear la contraseña una vez guardado.',
    'formPlaceholderEmail' => 'nombre@ejemplo.com',
    'formEmailUpdateWarning' => 'Por favor, verifica tu nuevo correo electrónico (%s) siguiendo las instrucciones que te enviamos por correo.',
    'formPlaceholderPassword' => 'Tu contraseña',
    'formPlaceholderCreatePassword' => 'Crea una contraseña',
    'formPlaceholderPasswordNew' => 'Nueva contraseña',
    'formPlaceholderPasswordConfirmation' => 'Repite la contraseña',
    'formLoginAction' => 'Entrar',
    'formPasswordResetAction' => 'Cambiar contraseña',
    'formPasswordCreateAction' => 'Crear contraseña',
    'formArticlePath' => 'URL',
    'formArticlePreviousPaths' => 'URLs anteriores',
    'formTimezone' => 'Zona horaria',

    'dashboardGoTo' => 'Contenido',
    'dashboardHomepage' => 'Portada',
    'dashboardShortcuts' => 'Accesos directos',
    'dashboardNewBlogPost' => 'Crear entrada en el blog',

    'globalYes' => 'Sí',
    'globalNo' => 'No',
    'globalBy' => 'por',
    'globalOf' => 'de',
    'globalSettings' => 'Configuración',
    'globalClose' => 'Cerrar',
    'globalRemove' => 'Eliminar',
    'globalFormat' => 'Formato',
    'globalRequired' => 'Obligatorio',
    'globalCreate' => 'Crear',
    'globalNew' => 'Nuevo',
    'globalAdd' => 'Añadir',
    'globalEdit' => 'Editar',
    'globalModify' => 'Modificar',
    'globalTitle' => 'Título',
    'globalSubtitle' => 'Subtítulo',
    'globalContent' => 'Contenido',
    'globalName' => 'Nombre',
    'globalEmail' => 'Correo electrónico',
    'globalPassword' => 'Contraseña',
    'globalLanguage' => 'Idioma',
    'globalRole' => 'Rol',
    'globalTimezone' => 'Zona horaria',
    'globalBio' => 'Breve biografía',
    'globalCancel' => 'Cancelar',
    'globalSave' => 'Guardar',
    'globalSend' => 'Enviar',
    'globalUpdate' => 'Actualizar',
    'globalUpdated' => 'Actualizado',
    'globalUpdatedAt' => 'Actualizado',
    'globalSubmittedAt' => 'Hora envío',
    'globalPublishOn' => 'Publicar el',
    'globalCreated' => 'Creado',
    'globalCreatedAt' => 'Creado el',
    'globalStatus' => 'Estado',
    'globalPreview' => 'Previsualizar',
    'globalArticle' => 'página',
    'globalBlogPost' => 'entrada',
    'globalTags' => 'Etiquetas',
    'globalDateFormat' => 'dd/mm/aaaa',
    'globalActivated' => 'Activado',
    'globalDeactivated' => 'Desactivado',
    'globalUser' => 'Ususario/a',
    'globalUserAccount' => 'Cuenta de usuario/a',
    'globalUserAccountSettings' => 'Configuración de cuenta de usuario/a',
    'globalPrevious' => 'Anterior',
    'globalNext' => 'Siguiente',
    'globalNoTitle' => 'Sin título',
    'globalComingSoon' => 'Próximamente...',
    'globalLoading' => 'Cargando...',
    'globalSaving' => 'Guardando...',
    'globalSending' => 'Enviando...',
    'globalMore' => 'Más...',
    'globalSequence' => 'Posición',
    'globalSearch' => 'Buscar',

    'globalUploadImage' => 'Subir imagen(es)',
    'globalUploadMedia' => 'Subir archivo(s)',
    'globalAddImage' => 'Añadir imagen(es)',
    'globalRemoveImage' => 'Eliminar imagen',
    'globalSelectImage' => 'Seleccionar imagen',
    'globalImages' => 'imágenes',
    'globalAddParagraph' => 'Añadir párrafo',
    'globalAddTextTitle' => 'Añadir título',
    'globalAddTextSubtitle' => 'Añadir subtítulo',
    'globalAddVideo' => 'Añadir vídeo',
    'globalAddHtml' => 'Añadir HTML',

    'globalGenericError' => 'Ha ocurrido un error inesperado, por favor inténtalo de nuevo.',
    'globalPageNotFoundTitle' => '¡Vaya! Has llegado a una calle sin salida...',
    'globalPageNotFoundContent' => '<p>Esta dirección no existe (aún).</p><p>Puede que sea un error nuestro, pero también puede ser que hayas escrito mal la dirección, sobre todo si lo hiciste manualmente. Puedes volver a la <a href="%s">portada haciendo clic aquí</a>.</p>',
    'globalPageDeactivatedTitle' => 'Temporalmente desactivada',
    'globalPageDeactivatedContent' => 'La página que buscas está desactivada temporalmente. Si tienes alguna consulta puedes ponerte en contacto con nosotros en <a href="mailto:contacto@contame.es">contacto@contame.es</a>. Disculpa las molestias.',

    'articleStatusDraft' => 'Borrador',
    'articleStatusPublished' => 'Publicado',
    'articleStatusDeleted' => 'Eliminado',
    'articleStatusPrivate' => 'Privado',
    'articleStatusUnlisted' => 'No listado',
    'articleTypeBlog' => 'Blog',
    'articleTypePage' => 'Página',
    'paragraphPlaceholder' => 'Escribe aquí...',
    'formFilterTitle' => 'Filtro',
    'formFilterButton' => 'Filtrar',
    'formFilterClean' => 'Restaurar filtro',
    'formFilterArticleTypeTitle' => 'Tipo',

    'pageContentEditTitle' => 'Textos',
    'pageContentEditTitleHomepage' => 'Portada',
    'pageContentEditTitleBlogBottom' => 'Texto que se muestra después de un post del blog',
    'pageContentEditAction' => 'Enlace',
    'pageContentEditActionHelp' => 'Se mostrará un botón invitando a visitar la dirección web proporcionada.',

    'mediaSelectImageForArticle' => 'Selecciona una imagen para añadir al artículo',

    'userRoleAdmin' => 'Administrador/a',
    'userRoleUser' => 'Usuario/a',
    'userStatusEnabled' => 'Activado',
    'userStatusDisabled' => 'Desactivado',
    'userJourneyRegistration' => 'Registro completo',
    'userJourneyPendingPasswordCreation' => 'Pendiente de crear la contraseña',

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

    'analyticsToday' => 'hoy',
    'analyticsSource' => 'Origen',
    'analyticsPage' => 'Página',
    'analyticsBrowser' => 'Navegador',
    'analyticsDevice' => 'Dispositivo',
    'analyticsCountry' => 'País',
    'analyticsLanguage' => 'Idioma',
    'analyticsPageViews' => 'Visitas',
    'analyticsVisitors' => 'Visitantes',
    'analyticsEventTypeAll' => 'Todos',
    'analyticsEventTypeVisitor' => 'Visitantes',
    'analyticsEventTypeUser' => 'Usuarios/as registrados/as',
    'analyticsEventTypeBot' => 'Bots',
    'analyticsEventTypeProbablyBot' => 'Probablemente un bot',
    'analyticsEventTypeApi' => 'API',
    'analyticsEventTypeCrawler' => 'Crawler',

    'mailerListTitle' => 'Registro de correos enviados',
    'mailerListNoError' => 'Enviado',
    'mailerListError' => 'Error: no enviado',
    'mailerListNotSent' => 'No enviado',
    'mailerTemplateAccountVerification' => 'Verificar correo electrónico',
    'mailerTemplatePasswordCreation' => 'Crear contraseña',
    'mailerTemplatePasswordReset' => 'Restablecer contraseña',

    'albumFormContent' => 'Contenido',
    'albumFormNew' => 'Nuevo álbum',
    'albumFormMainImageTitle' => 'Imagen principal',
    'albumFormPublicLinkTitle' => 'Dirección pública',
    'albumFormTemplateTitle' => 'Diseño del álbum',
    'albumPublicReadMore' => 'Leer más',
    'albumPublicMorePictures' => 'Ver más fotos',
    'albumAddCollection' => 'Añadir colección',
];
