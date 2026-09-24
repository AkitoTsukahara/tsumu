<?php

declare(strict_types=1);

namespace Domain\Exercise;

enum BodyPart: string
{
    case Chest = 'chest';
    case Back = 'back';
    case Shoulders = 'shoulders';
    case Biceps = 'biceps';
    case Triceps = 'triceps';
    case Forearms = 'forearms';
    case Quadriceps = 'quadriceps';
    case Hamstrings = 'hamstrings';
    case Glutes = 'glutes';
    case Calves = 'calves';
    case Core = 'core';
    case FullBody = 'full_body';
    case Other = 'other';
}
