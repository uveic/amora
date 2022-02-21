<?php

namespace Amora\Core\Value;

enum QueryOrderDirection: string
{
    case ASC = 'ASC';
    case DESC = 'DESC';
    case RAND = 'RAND()';
}
