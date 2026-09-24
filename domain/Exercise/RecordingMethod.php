<?php

declare(strict_types=1);

namespace Domain\Exercise;

enum RecordingMethod: string
{
    case WeightAndRepetitions = 'weight_repetitions';
    case Duration = 'duration';
    case DurationAndDistance = 'duration_distance';
    case SpeedAndDuration = 'speed_duration';
}
