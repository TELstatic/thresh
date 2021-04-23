<?php

namespace TELstatic\Thresh\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThreshRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'POST' => [
                'title'            => 'required|max:191',
                'type'             => 'required|integer',
                'name'             => 'required|max:191',
                'sort'             => 'required|integer',
                'is_show'          => 'required|integer',
                'thumb'            => 'required',
                'enabled_at'       => 'bail|required|date',
                'disabled_at'      => 'bail|required|date',
                'variants.*'       => 'bail|required|array',
                'variants.*.title' => 'bail|required|max:191',
                'variants.*.thumb' => 'bail|required|max:191',
                'variants.*.price' => 'bail|required|numeric',
                'variants.*.stock' => 'bail|required|integer|min:1',
            ],
            'PUT'  => [
                'title'            => 'required|max:191',
                'name'             => 'required|max:191',
                'sort'             => 'required|integer',
                'is_show'          => 'required|integer',
                'thumb'            => 'required',
                'enabled_at'       => 'bail|required|date',
                'disabled_at'      => 'bail|required|date',
                'variants.*'       => 'bail|required|array',
                'variants.*.title' => 'bail|required|max:191',
                'variants.*.thumb' => 'bail|required|max:191',
                'variants.*.price' => 'bail|required|numeric',
                'variants.*.stock' => 'bail|required|integer|min:1',
            ],
        ];

        if ($this->method() === 'PATCH') {
            $this->setMethod('PUT');
        }

        return $rules[$this->method()] ?? [];
    }
}
