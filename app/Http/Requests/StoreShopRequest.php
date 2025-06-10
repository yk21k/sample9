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

            // 自分のすべてのドラフトIDを配列で取得
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
                    Rule::unique('shops', 'name')->ignore($this->route('shop')?->id)->where(function ($query) use ($myDraftShopIds) {
                        // ドラフトは除外したいのでここで除外条件を追加（ドラフトのIDは無視）
                        if ($myDraftShopIds) {
                            $query->whereNotIn('id', $myDraftShopIds);
                        }
                    }),
                ],

                'manager' => [
                    $isDraft ? 'nullable' : 'required',
                    'string',
                    'between:1,100',
                    Rule::unique('shops', 'manager')->ignore($this->route('shop')?->id)->where(function ($query) use ($myDraftShopIds) {
                        if ($myDraftShopIds) {
                            $query->whereNotIn('id', $myDraftShopIds);
                        }
                    }),
                ],

            'email' => ($isDraft ? 'nullable' : 'required') . '|string|email|max:255',
            'telephone' => ($isDraft ? 'nullable' : 'required') . '|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'description' => ($isDraft ? 'nullable' : 'required') . '|string|max:2000',
            'representative' => ($isDraft ? 'nullable' : 'required') . '|string|between:1,100',
            'product_type' => ($isDraft ? 'nullable' : 'required') . '|string|max:255',
            'volume' => ($isDraft ? 'nullable' : 'required') . '|numeric|min:1',
            'note' => 'nullable|string|max:2000',
            'person_1' => 'nullable|string|max:30',
            'person_2' => 'nullable|string|max:30',
            'person_3' => 'nullable|string|max:30',

            // ファイル（形式は厳格）
            'photo_1' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_2' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_3' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_1' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_2' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_3' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_4' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        // 本申請時の追加バリデーション
        if (!$isDraft) {
            $type = $this->input('registration_type');

            if (in_array($type, ['個人', '個人事業主'])) {
                $rules['identification_1'] = 'required|string|in:運転免許証,パスポート';
                $rules['license_expiry'] = 'required|date';
            }

            if ($type === '個人事業主') {
                $rules['identification_2_1'] = 'required|string|in:個人事業開始届出証明書';
            }

            if (in_array($type, ['法人', '業務請負'])) {
                $rules['identification_2_2'] = 'required|string|in:商業・法人登記簿（履歴事項全部証明書）';
            }

            // registration_type によるファイル必須
            $rules['file_1'] = 'required_if:registration_type,個人,個人事業主|file|mimes:jpg,jpeg,png,pdf|max:5120';
            $rules['file_2'] = 'required_if:registration_type,業務請負|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }

        return $rules;
    }
}

