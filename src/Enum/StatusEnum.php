<?php

namespace App\Enum;

enum StatusEnum: string
{
    case InProgress = 'En cours de réflexion';
    case InProcess = 'En cours';
    case Finished = 'Terminé';
    case DefaultStatus = 'Non défini';
}
