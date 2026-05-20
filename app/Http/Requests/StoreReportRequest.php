<?php

namespace App\Http\Requests;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Report::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:district_agricultural,irrigation,land_holding,crop_trend,dashboard'],
            'title' => ['required', 'string', 'max:255'],
            'format' => ['required', 'in:pdf,csv'],
            'filters' => ['nullable', 'array'],
            'filters.region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'filters.state' => ['nullable', 'string', 'max:100'],
            'filters.season' => ['nullable', 'in:kharif,rabi,zaid'],
            'filters.year' => ['nullable', 'integer', 'min:2018', 'max:2030'],
        ];
    }
}
