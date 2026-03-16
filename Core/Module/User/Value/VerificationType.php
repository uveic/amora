<?php

namespace Amora\Core\Module\User\Value;

enum VerificationType: int
{
    case VerifyEmailAddress = 1;
    case PasswordReset = 2;
    case PasswordCreation = 3;
}
