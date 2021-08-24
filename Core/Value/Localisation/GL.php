<?php

use Amora\Core\Module\User\Service\UserService;

return [
    'navDashboard' => 'Inicio',
    'navAccount' => 'Os teus datos',
    'navSignOut' => 'Saír',
    'navSignIn' => 'Entra',
    'navSignUp' => 'Rexístrate',
    'navChangePassword' => 'Cambiar contrasinal',
    'navCreatePassword' => 'Crear contrasinal',
    'navDownloadAccountData' => 'Descargar os teus datos',
    'navDeleteAccount' => 'Eliminar conta',
    'navAdministrator' => 'Admin',
    'navAdminDashboard' => 'Inicio',
    'navAdminUsers' => 'Usuarios',
    'navAdminContent' => 'Contido',
    'navAdminImages' => 'Imaxes',
    'navAdminArticles' => 'Artigos',
    'navAdminBlogPosts' => 'Entradas blog',
    'navAdminArticleOptions' => 'Configuración artigo',

    'editorTitlePlaceholder' => 'Título...',

    'authenticationActionHomeLink' => 'Ir á páxina de entrada',
    'authenticationLoginSubtitle' => 'Benvida/o de volta. Adiante!',
    'authenticationRegisterSubtitle' => 'A un paso.',
    'authenticationRegisterTOS' => 'Ao rexistráreste aceptas os <a href="#">termos de uso</a> e a <a href="#">política de privacidade</a>.',
    'authenticationRegisterPasswordHelp' => 'Lonxitude mínima: ' . UserService::USER_PASSWORD_MIN_LENGTH . ' caracteres. Recomenable: letras, números e símbolos.',
    'authenticationRegisterAlreadyLogin' => 'Xa tes unha conta?',

    'authenticationForgotPassword' => 'Esquecíches o teu contrasinal?',
    'authenticationForgotPasswordSubtitle' => 'Non te preocupes, introduce o correo electrónico co que te rexistraches e enviarémosche unha ligazón para restaurar o teu contrasinal.',
    'authenticationForgotPasswordAction' => 'Enviar ligazón de restauración',
    'authenticationForgotPasswordActionSuccess' => 'Se temos o teu enderezo (<b><span id="register-feedback-email"></span></b>) na nosa base de datos xa che enviamos un correo para restaurar o contrasinal. Comproba a bandexa de entrada e segue as instruccións.',
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

    'authenticationPasswordResetSubtitle' => 'Cambia o contrasinal',
    'authenticationPasswordResetActionSuccess' => 'Cambiouse o contrasinal correctamente.',
    'authenticationPasswordResetAlreadyLogin' => 'Queres <a href="%s">entrar na túa conta</a> sen cambiar o contrasinal?',
    'authenticationPasswordCreateSubtitle' => 'Crea o teu contrasinal',
    'authenticationPasswordCreationActionSuccess' => 'Contrasinal creado correctamente.',

    'authenticationInviteRequest' => 'Consegue unha invitación',
    'authenticationInviteRequestSubtitle' => 'A web está actualmente nunha beta privada, estamos traballando para asegurarnos de que todo funciona correctamente e con moitas ganas de que estea lista para que a poidas utilizar. Déixanos o teu correo e enviarémosche unha invitación tan pronto como sexa posible. Só utilizaremos o teu correo para enviarche a invitación.<br>Grazas pola túa paciencia!',
    'authenticationInviteRequestActionSuccess' => '<h2>Petición recibida correctamente</h2><p>Enviarémosche unha invitación en canto teñamos todo listo. Grazas pola espera!</p><p>O teu correo: <b><span id="register-feedback-email"></span></b>.</p>',
    'authenticationInviteRequestHomeLink' => 'Volver á páxina principal',
    'authenticationInviteRequestFormAction' => 'Pedir invitación',
    'authenticationVerifyEmailBanner' => 'Por favor, verifica a túa conta seguindo as instrucións que che enviamos nun correo a <b>%s</b>. Se non o recibiches revisa a caixa do lixo ou <a class="verified-link" data-user-id="%s" href="#">fai clic aquí</a> e enviarémosche outro. Podes <a href="/gl/account">modificar o teu enderezo</a> se fora necesario.',
    'authenticationPassNotValid' => 'O contrasinal actual non é válido.',

    'formYourAccount' => 'A túa conta',
    'formPlaceholderUserName' => 'Nome',
    'formPlaceholderUserHelp' => 'Mínimo tres letras',
    'formEmail' => 'O teu correo electrónico',
    'formEmailNewUserHelp' => 'Enviaráselle un correo electrónico para crear o contrasinal unha vez gardado.',
    'formPlaceholderEmail' => 'nome@exemplo.com',
    'formEmailUpdateWarning' => 'Por favor, verifica o teu novo correo electrónico (%s) para cambialo.',
    'formPlaceholderPassword' => 'O teu contrasinal',
    'formPlaceholderCreatePassword' => 'Crea un contrasinal',
    'formPlaceholderPasswordNew' => 'Novo contrasinal',
    'formPlaceholderPasswordConfirmation' => 'Repite o contrasinal',
    'formTimezone' => 'Zona horaria',
    'formLoginAction' => 'Entrar',
    'formPasswordResetAction' => 'Cambiar contrasinal',
    'formPasswordCreateAction' => 'Crear contrasinal',
    'formArticleUri' => 'URI do artigo',

    'dashboardGoTo' => 'Ir a...',
    'dashboardHomepage' => 'Portada',
    'dashboardShortcuts' => 'Accessos directos',
    'dashboardNewBlogPost' => 'Crear entrada no blog',

    'globalYes' => 'Si',
    'globalNo' => 'Non',
    'globalBy' => 'por',
    'globalSettings' => 'Configuración',
    'globalClose' => 'Cerrar',
    'globalRemove' => 'Eliminar',
    'globalFormat' => 'Formato',
    'globalRequired' => 'Obrigatorio',
    'globalNew' => 'Novo',
    'globalEdit' => 'Editar',
    'globalTitle' => 'Título',
    'globalName' => 'Nome',
    'globalEmail' => 'Correo electrónico',
    'globalPassword' => 'Contrasinal',
    'globalLanguage' => 'Idioma',
    'globalRole' => 'Rol',
    'globalTimezone' => 'Zona horaria',
    'globalBio' => 'Breve biografía',
    'globalSave' => 'Gardar',
    'globalUpdate' => 'Actualizar',
    'globalUpdated' => 'Actualizado',
    'globalUpdatedAt' => 'Actualizado',
    'globalSubmittedAt' => 'Hora envío',
    'globalPublishOn' => 'Publicar o',
    'globalCreated' => 'Creado',
    'globalStatus' => 'Estado',
    'globalPreview' => 'Previsualizar',
    'globalArticle' => 'artigo',
    'globalBlogPost' => 'entrada',
    'globalTags' => 'Etiquetas',
    'globalDateFormat' => 'dd/mm/aaaa',
    'globalActivated' => 'Activado',
    'globalDeactivated' => 'Desactivado',
    'globalUser' => 'Ususario/a',
    'globalUserAccount' => 'Conta de usuario/a',
    'globalUserAccountSettings' => 'Configuración de conta de usuario/a',
    'globalNext' => 'Seguinte',
    'globalNoTitle' => 'Sen título',
    'globalComingSoon' => 'Proximamente...',

    'globalUploadImage' => 'Subir imaxe(s)',
    'globalAddImage' => 'Engadir imaxe(s)',
    'globalAddParagraph' => 'Engadir párrafo',
    'globalAddTextTitle' => 'Engadir título',
    'globalAddTextSubtitle' => 'Engadir subtítulo',
    'globalAddVideo' => 'Engadir vídeo',
    'globalAddHtml' => 'Engadir HTML',

    'globalGenericError' => 'Algo non foi ben, por favor inténtao de novo.',
    'globalPageNotFoundTitle' => 'Non se atopu a páxinga :(',
    'globalPageNotFoundContent' => 'A páxina que buscas non existe.',

    'articleEditHomepageTitle' => 'Editar o contido da portada',
    'articleStatusDraft' => 'Borrador',
    'articleStatusPublished' => 'Publicado',
    'articleStatusDeleted' => 'Eliminado',
    'articleStatusArchived' => 'Arquivado',
    'articleTypeHome' => 'Portada',
    'articleTypeBlog' => 'Blog',
    'articleTypeArticle' => 'Artigo',
    'paragraphPlaceholder' => 'Escribe aquí...',
    'articleBlogFooterInfo' => 'Grazas por ler e chegar ata aquí 💕. Non hai comentarios pero pódesme escribir a <a href="mailto:%s">%s</a>, encantarame recibir o teu correo.',

    'userRoleAdmin' => 'Administrador/a',
    'userRoleUser' => 'Usuario/a',

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
];
