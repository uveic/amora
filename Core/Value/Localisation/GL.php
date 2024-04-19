<?php

use Amora\Core\Module\User\Service\UserService;

return [
    'navDashboard' => 'Inicio',
    'navAdministrator' => 'Admin',
    'navAdminDashboard' => 'Inicio',
    'navAccount' => 'Os teus datos',
    'navSignOut' => 'Saír',
    'navSignIn' => 'Entra',
    'navSignUp' => 'Rexístrate',
    'navChangePassword' => 'Cambiar contrasinal',
    'navCreatePassword' => 'Crear contrasinal',
    'navDownloadAccountData' => 'Descargar os teus datos',
    'navDeleteAccount' => 'Eliminar conta',
    'navAdminUsers' => 'Usuarios',
    'navAdminAnalytics' => 'Visitas',
    'navAdminEmails' => 'Correos',
    'navAdminContent' => 'Contido',
    'navAdminAlbums' => 'Álbums',
    'navAdminImages' => 'Imaxes',
    'navAdminMedia' => 'Arquivos',
    'navAdminArticles' => 'Páxinas',
    'navAdminBlogPosts' => 'Entradas blog',
    'navAdminArticleOptions' => 'Configuración',
    'navAdminPageContentEdit' => 'Contido',

    'editorTitlePlaceholder' => 'Título...',
    'editorSubtitlePlaceholder' => 'Subtítulo...',
    'editorDisableControls' => 'Desactivar controles',
    'editorEnableControls' => 'Activar controles',
    'editorMainImage' => 'Imaxe',
    'editorMainImageActionTitle' => 'Seleccionar imaxe destacada',

    'authenticationActionHomeLink' => 'Ir á páxina de entrada',
    'authenticationLoginSubtitle' => 'Benvida/o de volta. Adiante!',
    'authenticationRegisterSubtitle' => 'A un paso.',
    'authenticationRegisterTOS' => 'Ao rexistráreste aceptas os <a href="#">termos de uso</a> e a <a href="#">política de privacidade</a>.',
    'authenticationRegisterPasswordHelp' => 'Lonxitude mínima: ' . UserService::USER_PASSWORD_MIN_LENGTH . ' caracteres. Recomenable: letras, números e símbolos.',
    'authenticationRegisterAlreadyLogin' => 'Xa tes unha conta?',

    'authenticationForgotPassword' => 'Esquecíches o teu contrasinal?',
    'authenticationForgotPasswordSubtitle' => 'Non te preocupes, introduce o correo electrónico co que te rexistraches e enviarémosche unha ligazón para restaurar o teu contrasinal.',
    'authenticationForgotPasswordAction' => 'Enviar ligazón de restauración',
    'authenticationForgotPasswordActionSuccess' => 'Se temos o teu correo (<b><span id="register-feedback-email"></span></b>) na nosa base de datos enviarémosche unha mensaxe con instrucións para restaurar o teu contrasinal nuns intres. Comproba a bandexa de entrada e segue as instrucións.',
    'authenticationEmailVerified' => 'Correo electrónico verificado correctamente.',
    'authenticationEmailVerifiedError' => 'A ligazón para verificar o teu correo electrónico non é válida.',
    'authenticationEmailVerifiedExpired' => 'A ligazón para verificar o teu correo electrónico caducou. Por favor, inicia o proceso de novo.',
    'authenticationPasswordResetLinkError' => 'A ligazón para cambiar o teu contrasinal non é válida. Inicia o proceso de cambio de contrasinal de novo.',
    'authenticationPasswordCreationLinkError' => 'A ligazón para crear o teu contrasinal non é válida. Por favor, cambia o teu contrasinal ou contacta co/a adminstrador/a da páxina.',
    'authenticationEmailAndOrPassNotValid' => 'Correo electrónico e/ou contrasinal non válidos.',
    'authenticationEmailNotValid' => 'Correo electrónico non válido.',
    'authenticationUserRegistrationDisabled' => 'O rexistro de usuarios non está habilitado. Ponte en contacto co administrador do sitio web.',
    'authenticationPasswordTooShort' => 'O contrasinal é demasiado curto. Cámbiao e inténtao outra vez.',
    'authenticationPasswordsDoNotMatch' => 'Os contrasinais non coinciden. Corríxeo e inténtao outra vez.',
    'authenticationRegistrationErrorExistingEmail' => 'Xa hai outra conta co mesmo email. Por favor, identifícate <a href="%s">aquí</a>.',
    'authenticationPassNotValid' => 'O contrasinal actual non é válido.',

    'authenticationPasswordResetSubtitle' => 'Cambia o contrasinal',
    'authenticationPasswordResetActionSuccess' => 'Cambiouse o contrasinal correctamente.',
    'authenticationPasswordResetAlreadyLogin' => 'Queres <a href="%s">entrar na túa conta</a> sen cambiar o contrasinal?',
    'authenticationPasswordCreateSubtitle' => 'Crea o teu contrasinal',
    'authenticationPasswordCreationActionSuccess' => 'Contrasinal creado correctamente.',

    'authenticationInviteRequest' => 'Consegue unha invitación',
    'authenticationInviteRequestSubtitle' => 'A web está actualmente nunha beta privada, estamos traballando para asegurármonos de que todo funciona correctamente e con moita gana de que estea lista para que a poidas utilizar. Déixanos o teu correo e enviarémosche unha invitación tan pronto como sexa posible. Respectamos a túa privacidade, só utilizaremos o teu correo para enviarche a invitación. Grazas pola túa paciencia!',
    'authenticationInviteRequestActionSuccess' => '<h2>Petición recibida correctamente</h2><p>Enviarémosche unha invitación en canto teñamos todo listo. Grazas pola espera!</p><p>O teu correo: <b><span id="register-feedback-email"></span></b>.</p>',
    'authenticationInviteRequestHomeLink' => 'Volver á páxina principal',
    'authenticationInviteRequestFormAction' => 'Solicitar invitación',
    'authenticationVerifyEmailBannerTitle' => 'Confirma o teu correo',
    'authenticationVerifyEmailBannerContent' => 'Por favor, verifica a túa conta seguindo as instrucións que che enviamos nun correo a <b>%s</b>. Se non o recibiches revisa a caixa do lixo ou <a class="verified-link" data-user-id="%s" href="#">fai clic aquí</a> e enviarémosche outro. Podes <a href="/gl/account">modificar o teu enderezo</a> se fora necesario.',

    'formYourAccount' => 'A túa conta',
    'formPlaceholderUserName' => 'Nome',
    'formPlaceholderUserHelp' => 'Mínimo tres letras',
    'formEmail' => 'O teu correo electrónico',
    'formEmailNewUserHelp' => 'Enviaráselle un correo electrónico para crear o contrasinal unha vez gardado.',
    'formPlaceholderEmail' => 'nome@exemplo.com',
    'formEmailUpdateWarning' => 'Por favor, verifica o teu novo correo electrónico (%s) seguindo as instrucións que che enviamos.',
    'formPlaceholderPassword' => 'O teu contrasinal',
    'formPlaceholderCreatePassword' => 'Crea un contrasinal',
    'formPlaceholderPasswordNew' => 'Novo contrasinal',
    'formPlaceholderPasswordConfirmation' => 'Repite o contrasinal',
    'formLoginAction' => 'Entrar',
    'formPasswordResetAction' => 'Cambiar contrasinal',
    'formPasswordCreateAction' => 'Crear contrasinal',
    'formArticlePath' => 'Enderezo do artigo',
    'formArticlePreviousPaths' => 'Enderezos anteriores',
    'formTimezone' => 'Zona horaria',

    'dashboardGoTo' => 'Contido',
    'dashboardHomepage' => 'Portada',
    'dashboardShortcuts' => 'Accesos directos',
    'dashboardNewBlogPost' => 'Crear entrada no blog',

    'globalYes' => 'Si',
    'globalNo' => 'Non',
    'globalBy' => 'por',
    'globalOf' => 'de',
    'globalSettings' => 'Configuración',
    'globalClose' => 'Pechar',
    'globalRemove' => 'Eliminar',
    'globalFormat' => 'Formato',
    'globalRequired' => 'Obrigatorio',
    'globalCreate' => 'Crear',
    'globalNew' => 'Novo',
    'globalAdd' => 'Engadir',
    'globalEdit' => 'Editar',
    'globalModify' => 'Modificar',
    'globalTitle' => 'Título',
    'globalSubtitle' => 'Subtítulo',
    'globalContent' => 'Contido',
    'globalName' => 'Nome',
    'globalEmail' => 'Correo electrónico',
    'globalPassword' => 'Contrasinal',
    'globalLanguage' => 'Idioma',
    'globalRole' => 'Rol',
    'globalTimezone' => 'Zona horaria',
    'globalBio' => 'Breve biografía',
    'globalCancel' => 'Cancelar',
    'globalSave' => 'Gardar',
    'globalSend' => 'Enviar',
    'globalUpdate' => 'Actualizar',
    'globalUpdated' => 'Actualizado',
    'globalUpdatedAt' => 'Actualizado',
    'globalSubmittedAt' => 'Hora envío',
    'globalPublishOn' => 'Publicar o',
    'globalCreated' => 'Creado',
    'globalCreatedAt' => 'Creado o',
    'globalStatus' => 'Estado',
    'globalPreview' => 'Previsualizar',
    'globalArticle' => 'páxina',
    'globalBlogPost' => 'entrada',
    'globalTags' => 'Etiquetas',
    'globalDateFormat' => 'dd/mm/aaaa',
    'globalActivated' => 'Activado',
    'globalDeactivated' => 'Desactivado',
    'globalUser' => 'Ususario/a',
    'globalUserAccount' => 'Conta de usuario/a',
    'globalUserAccountSettings' => 'Configuración de conta de usuario/a',
    'globalPrevious' => 'Anterior',
    'globalNext' => 'Seguinte',
    'globalNoTitle' => 'Sen título',
    'globalComingSoon' => 'Proximamente...',
    'globalLoading' => 'Cargando...',
    'globalSaving' => 'Gardando...',
    'globalSending' => 'Enviando...',
    'globalMore' => 'Máis...',
    'globalSequence' => 'Posición',
    'globalSearch' => 'Buscar',

    'globalUploadImage' => 'Subir imaxe(s)',
    'globalUploadMedia' => 'Subir arquivo(s)',
    'globalAddImage' => 'Engadir imaxe(s)',
    'globalRemoveImage' => 'Eliminar imaxe',
    'globalSelectImage' => 'Selecionar imaxe',
    'globalImages' => 'imaxes',
    'globalAddParagraph' => 'Engadir párrafo',
    'globalAddTextTitle' => 'Engadir título',
    'globalAddTextSubtitle' => 'Engadir subtítulo',
    'globalAddVideo' => 'Engadir vídeo',
    'globalAddHtml' => 'Engadir HTML',

    'globalGenericError' => 'Algo non foi ben, por favor inténtao de novo.',
    'globalPageNotFoundTitle' => 'Vaia, chegaches a unha rúa sen saída...',
    'globalPageNotFoundContent' => '<p>Este enderezo non existe (aínda).</p><p>Poida que sexa erro noso, mais tamén pode ser que escribises mal o enderezo, sobre todo se o fixeches manualmente.</p><p>Podes volver á <a href="%s">portada facendo clic aquí</a>.</p>',
    'globalPageDeactivatedTitle' => 'Temporalmente desactivada',
    'globalPageDeactivatedContent' => 'A páxina que buscas está desactivada temporalmente. Se tes algunha consulta podes poñerte en contacto con nós en <a href="mailto:contacto@contame.es">contacto@contame.es</a>. Desculpa as molestias.',

    'articleStatusDraft' => 'Borrador',
    'articleStatusPublished' => 'Publicado',
    'articleStatusDeleted' => 'Eliminado',
    'articleStatusPrivate' => 'Privado',
    'articleStatusUnlisted' => 'Non listado',
    'articleTypeBlog' => 'Blog',
    'articleTypePage' => 'Páxina',
    'paragraphPlaceholder' => 'Escribe aquí...',
    'formFilterTitle' => 'Filtro',
    'formFilterButton' => 'Filtrar',
    'formFilterClean' => 'Restaurar filtro',
    'formFilterArticleTypeTitle' => 'Tipo',

    'pageContentEditTitle' => 'Editar contido',
    'pageContentEditTitleHomepage' => 'Portada',
    'pageContentEditTitleBlogBottom' => 'Texto despois dun post do blog',

    'mediaUploadedBy' => 'Subido por %s o %s',
    'mediaSelectImageForArticle' => 'Selecciona unha imaxe para engadir ao artigo',

    'userRoleAdmin' => 'Administrador/a',
    'userRoleUser' => 'Usuario/a',
    'userStatusEnabled' => 'Activado',
    'userStatusDisabled' => 'Desactivado',
    'userJourneyRegistration' => 'Rexistro completo',
    'userJourneyPendingPasswordCreation' => 'Pendente de crear o contrasinal',

    'sectionRemove' => 'Eliminar sección',
    'sectionMoveUp' => 'Mover para arriba',
    'sectionMoveDown' => 'Mover para abaixo',

    'emailConfirmationSubject' => 'Benvido/a a %s! Confirma o teu correo electrónico',
    'emailConfirmationContent' => '<p>¡Benvido/a!</p>' .
        '<p>Por favor, confirma o teu correo electrónico facendo clic na seguinte ligazón.</p>' .
        '<p><a href="%s">Confirmar correo electrónico</a></p>' .
        '<p>%s</p>',
    'emailUpdateVerificationSubject' => 'Verifica o teu correo electrónico',
    'emailUpdateVerificationContent' => '<p>Ola,</p>' .
        '<p>Recibimos unha petición de cambio de correo electrónico na túa conta de %s.</p>' .
        '<p>Se non fuches ti o que fixeches esta petición podes ignorar este correo. En caso contrario, fai clic na seguinte ligazón para verificar o teu novo correo:</p>' .
        '<p><a href="%s">Confirma o teu correo electrónico</a></p>' .
        '<p>%s</p>',
    'emailPasswordChangeSubject' => '%s: cambiar contrasinal',
    'emailPasswordChangeContent' => '<p>Ola,</p>' .
        '<p>Recibimos unha petición de cambio de contrasinal na túa conta de %s.</p>' .
        '<p>Se non fuches ti o que fixeches esta petición podes ignorar este correo electrónico. En caso contrario, fai clic na seguinte ligazón para cambiar o teu contrasinal:</p>' .
        '<p><a href="%s">Cambiar contrasinal</a></p>' .
        '<p>%s</p>',
    'emailPasswordCreationSubject' => 'A túa nova conta en %s',
    'emailPasswordCreationContent' => '<p>Ola %s,</p>' .
        '<p>Fai clic na seguinte ligazón para crear un contrasinal na túa nova conta en %s.</p>' .
        '<p>Usuario: %s</p>' .
        '<p><a href="%s">Crear contrasinal</a></p>' .
        '<p>%s</p>',

    'analyticsToday' => 'hoxe',
    'analyticsSource' => 'Orixe',
    'analyticsPage' => 'Páxina',
    'analyticsBrowser' => 'Navegador',
    'analyticsDevice' => 'Dispositivo',
    'analyticsCountry' => 'País',
    'analyticsLanguage' => 'Idioma',
    'analyticsPageViews' => 'Visitas',
    'analyticsVisitors' => 'Visitantes',
    'analyticsEventTypeAll' => 'Todos',
    'analyticsEventTypeVisitor' => 'Visitantes',
    'analyticsEventTypeUser' => 'Usuarios/as rexistrados/as',
    'analyticsEventTypeBot' => 'Bots',
    'analyticsEventTypeProbablyBot' => 'Probablemente un bot',
    'analyticsEventTypeApi' => 'API',
    'analyticsEventTypeCrawler' => 'Crawler',

    'mailerListTitle' => 'Rexistro de correos enviados',
    'mailerListNoError' => 'Enviado',
    'mailerListError' => 'Erro: non enviado',
    'mailerTemplateAccountVerification' => 'Verificar correo electrónico',
    'mailerTemplatePasswordCreation' => 'Crear contrasinal',
    'mailerTemplatePasswordReset' => 'Restablecer contrasinal',

    'albumFormContent' => 'Contido',
    'albumFormNew' => 'Novo álbum',
    'albumFormMainImageTitle' => 'Imaxe principal',
    'albumFormPublicLinkTitle' => 'Enderezo público',
    'albumFormTemplateTitle' => 'Deseño do álbum',
    'albumPublicReadMore' => 'Ler máis',
    'albumPublicMorePictures' => 'Ver máis fotos',
    'albumAddSection' => 'Engadir sección',
];
