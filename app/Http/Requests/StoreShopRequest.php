<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules()
    {
        $rules = [
            'registration_type' => 'required|in:個人,個人事業主,法人,業務請負',
            'name' => 'required|string|between:1,30|unique:shops,name',
            'email' => 'required|string|email|max:255',
            'telephone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'description' => 'required|string|max:2000',
            'manager' => 'required|string|between:1,100|unique:shops,manager',
            'representative' => 'required|string|between:1,100',
            'product_type' => 'required|string',
            'volume' => 'required|numeric|min:1',
            'note' => 'nullable|string|max:2000',
            'person_1' => 'nullable|string|max:30',
            'person_2' => 'nullable|string|max:30',
            'person_3' => 'nullable|string|max:30',

            // ファイルバリデーション
            'photo_1' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_2' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_3' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

            // registration_type による条件付きバリデーション
            'file_1' => [
                'required_if:registration_type,個人,個人事業主',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
                'file',
            ],
            'file_2' => [
                'required_if:registration_type,,業務請負',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
                'file',
            ],
            'file_3' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_4' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        // registration_type による条件付きバリデーション
        $type = $this->input('registration_type');

        // 個人または個人事業主の場合
        if (in_array($type, ['個人', '個人事業主'])) {
            $rules['identification_1'] = 'required|string|in:運転免許証,パスポート';
            $rules['license_expiry'] = 'required|date';
        }

        // 個人事業主の場合
        if ($type === '個人事業主') {
            $rules['identification_2_1'] = 'required|string|in:個人事業開始届出証明書';
        }

        // 法人または業務請負の場合
        if (in_array($type, ['法人', '業務請負'])) {
            $rules['identification_2_2'] = 'required|string|in:商業・法人登記簿（履歴事項全部証明書）';
        }

        return $rules;
    }


}
