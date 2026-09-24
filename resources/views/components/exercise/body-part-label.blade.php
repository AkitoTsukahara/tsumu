@props(['value'])

@switch($value)
    @case('chest') 胸 @break
    @case('back') 背中 @break
    @case('shoulders') 肩 @break
    @case('biceps') 上腕二頭筋 @break
    @case('triceps') 上腕三頭筋 @break
    @case('forearms') 前腕 @break
    @case('quadriceps') 大腿四頭筋 @break
    @case('hamstrings') ハムストリング @break
    @case('glutes') 臀部 @break
    @case('calves') ふくらはぎ @break
    @case('core') 体幹 @break
    @case('full_body') 全身 @break
    @case('other') その他 @break
@endswitch
