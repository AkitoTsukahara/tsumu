<?php

namespace App\Livewire\Forms;

use App\Livewire\Forms\Dto\ValidatedEquipmentInputDto;
use App\Service\Query\Equipment\Dto\EquipmentListItemDto;
use Domain\Equipment\EquipmentCategory;
use Domain\Equipment\WeightUnit;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class EquipmentForm extends Form
{
    public string $name = '';

    public string $category = EquipmentCategory::Machine->value;

    public string $weightUnit = WeightUnit::Kilogram->value;

    public string $weightIncrement = '';

    public function fillFrom(EquipmentListItemDto $equipment): void
    {
        $this->name = $equipment->name;
        $this->category = $equipment->category;
        $this->weightUnit = $equipment->weightUnit;
        $this->weightIncrement = $equipment->weightIncrement;
        $this->resetValidation();
    }

    public function validatedInput(): ValidatedEquipmentInputDto
    {
        $this->name = trim($this->name);
        $this->weightIncrement = trim($this->weightIncrement);

        /** @var array{name: string, category: string, weightUnit: string, weightIncrement: string} $input */
        $input = $this->validate();

        return new ValidatedEquipmentInputDto(
            name: $input['name'],
            category: EquipmentCategory::from($input['category']),
            weightUnit: WeightUnit::from($input['weightUnit']),
            weightIncrement: $input['weightIncrement'],
        );
    }

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', Rule::enum(EquipmentCategory::class)],
            'weightUnit' => ['required', Rule::enum(WeightUnit::class)],
            'weightIncrement' => ['required', 'regex:/^(?:0|[1-9]\d{0,2})(?:\.\d{1,2})?$/', 'not_in:0,0.0,0.00'],
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'name.required' => '機材名を入力してください。',
            'name.max' => '機材名は100文字以内で入力してください。',
            'category.required' => 'カテゴリを選択してください。',
            'category.enum' => 'カテゴリを選択肢から選んでください。',
            'weightUnit.required' => '重量単位を選択してください。',
            'weightUnit.enum' => '重量単位を選択肢から選んでください。',
            'weightIncrement.required' => '重量の刻み幅を入力してください。',
            'weightIncrement.regex' => '重量の刻み幅は0.01〜999.99の範囲で、小数2桁まで入力してください。',
            'weightIncrement.not_in' => '重量の刻み幅は0より大きい値を入力してください。',
        ];
    }
}
