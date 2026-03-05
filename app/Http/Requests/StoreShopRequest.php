<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;


class StoreShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isDraft = $this->input('save_type') === 'draft';

        $myDraftShopIds = Auth::user()->shops()
            ->where('is_draft', true)
            ->pluck('id')
            ->toArray();

        $rules = [
            'registration_type' => ($isDraft ? 'nullable' : 'required') . '|in:個人,個人事業主,法人,業務請負',

            'name' => [
                $isDraft ? 'nullable' : 'required',
                'string',
                'between:1,30',
                Rule::unique('shops', 'name')
                    ->ignore($this->route('shop')?->id)
                    ->where(fn ($q) => $myDraftShopIds ? $q->whereNotIn('id', $myDraftShopIds) : null),
            ],

            'manager' => [
                $isDraft ? 'nullable' : 'required',
                'string',
                'between:1,100',
                Rule::unique('shops', 'manager')
                    ->ignore($this->route('shop')?->id)
                    ->where(fn ($q) => $myDraftShopIds ? $q->whereNotIn('id', $myDraftShopIds) : null),
            ],

            'email' => ($isDraft ? 'nullable' : 'required') . '|email|max:255',
            'telephone' => ($isDraft ? 'nullable' : 'required') . '|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'description' => ($isDraft ? 'nullable' : 'required') . '|string|max:2000',
            'representative' => ($isDraft ? 'nullable' : 'required') . '|string|between:1,100',
            'product_type' => ($isDraft ? 'nullable' : 'required') . '|string|max:255',
            'volume' => ($isDraft ? 'nullable' : 'required') . '|numeric|min:1',
            'note' => 'nullable|string|max:2000',

            /*
            |--------------------------------------------------------------------------
            | 担当者 共通（nullable が基本）
            |--------------------------------------------------------------------------
            */
            'person_1' => 'nullable|string|max:30',
            'person_2' => 'nullable|string|max:30',
            'person_3' => 'nullable|string|max:30',

            'id_1_1' => 'nullable|string|in:運転免許証,パスポート',
            'id_2_1' => 'nullable|string|in:運転免許証,パスポート',
            'id_3_1' => 'nullable|string|in:運転免許証,パスポート',

            'photo_1' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_2' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_3' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'photo_1_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_2_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_3_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

            // その他ファイル
            'file_1' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_2' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_3' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_4' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        /*
        |--------------------------------------------------------------------------
        | 🔥 本申請時のみ：条件付き必須
        |--------------------------------------------------------------------------
        */
        if (!$isDraft) {

            // ==============================
            // 担当者 1〜3 共通ロジック
            // ==============================
            foreach ([1, 2, 3] as $i) {

                $rules["id_{$i}_1"] =
                    "required_with:person_{$i}";

                $rules["photo_{$i}"] =
                    "required_with:person_{$i}|file|mimes:jpg,jpeg,png,pdf|max:5120";

                $rules["photo_{$i}_back"] = [
                    Rule::requiredIf(fn () =>
                        $this->input("id_{$i}_1") === '運転免許証'
                        && $this->filled("person_{$i}")
                    ),
                    'file',
                    'mimes:jpg,jpeg,png,pdf',
                    'max:5120',
                ];
            }

            // ==============================
            // registration_type 別
            // ==============================
            $type = $this->input('registration_type');

            if (in_array($type, ['個人', '個人事業主'])) {

                $rules['identification_1'] =
                    'required|string|in:運転免許証,パスポート';

                $rules['license_expiry'] = [
                    'required',
                    'date',
                    'after_or_equal:today',
                ];
            }

            if ($type === '個人事業主') {

                $rules['identification_2_1'] =
                    'required|string|in:個人事業開始届出証明書';
            }

            if (in_array($type, ['法人', '業務請負'])) {

                $rules['identification_2_2'] =
                    'required|string|in:business_card';
            }

            // ==============================
            // ファイル系
            // ==============================

            // 全区分で必須
            $rules['file_1'] =
                'required|file|mimes:jpg,jpeg,png,pdf|max:5120';

            // 個人/個人事業主で必須
            $rules['photo_1'] = [
                'required_if:registration_type,個人,個人事業主',
                'file',
                'image',
            ];

            $rules['photo_2'] = [
                'required_if:registration_type,個人,個人事業主',
                'file',
                'image',
            ];    

            // 個人事業主のみ
            $rules['file_2'] =
                'required_if:registration_type,個人事業主|file|mimes:jpg,jpeg,png,pdf|max:5120';

            // 法人・業務請負
            $rules['corporate_number'] = [
                'nullable',
                'required_if:registration_type,法人,業務請負',
                'digits:13',
            ];

            $rules['invoice_number'] = [
                'nullable',
                'required_if:registration_type,法人,業務請負',
                'regex:/^T[0-9]{13}$/',
            ];
        }

        return $rules;
    }
}

