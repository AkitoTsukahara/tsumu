<?php

namespace App\Livewire\Forms;

use App\Livewire\Forms\Dto\ValidatedExerciseInputDto;
use Domain\Equipment\EquipmentId;
use Domain\Exercise\BodyPart;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ExerciseForm extends Form
{
    public string $name = '';

    public string $equipmentId = '';

    public string $primaryTarget = '';

    public string $secondaryTarget = '';

    public function validatedInput(): ValidatedExerciseInputDto
    {
        $this->name = trim($this->name);
        $this->equipmentId = trim($this->equipmentId);
        $this->primaryTarget = trim($this->primaryTarget);
        $this->secondaryTarget = trim($this->secondaryTarget);

        /** @var array{name: string, equipmentId: string, primaryTarget: string, secondaryTarget: string} $input */
        $input = $this->validate();

        return new ValidatedExerciseInputDto(
            name: $input['name'],
            equipmentId: $input['equipmentId'] === ''
                ? null
                : EquipmentId::fromString($input['equipmentId']),
            primaryTarget: BodyPart::from($input['primaryTarget']),
            secondaryTarget: $input['secondaryTarget'] === ''
                ? null
                : BodyPart::from($input['secondaryTarget']),
        );
    }

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'equipmentId' => ['nullable', 'uuid:7'],
            'primaryTarget' => ['required', Rule::enum(BodyPart::class)],
            'secondaryTarget' => ['nullable', Rule::enum(BodyPart::class)],
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'name.required' => '種目名を入力してください。',
            'name.max' => '種目名は100文字以内で入力してください。',
            'equipmentId.uuid' => '使用機材を選択肢から選んでください。',
            'primaryTarget.required' => '主対象部位を選択してください。',
            'primaryTarget.enum' => '主対象部位を選択肢から選んでください。',
            'secondaryTarget.enum' => '副対象部位を選択肢から選んでください。',
        ];
    }
}
