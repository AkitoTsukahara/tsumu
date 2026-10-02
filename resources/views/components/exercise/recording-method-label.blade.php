@props(['value'])

@switch($value)
    @case('weight_repetitions') 重量＋回数 @break
    @case('duration') 時間 @break
    @case('duration_distance') 時間＋距離 @break
    @case('speed_duration') 速度＋時間 @break
@endswitch
