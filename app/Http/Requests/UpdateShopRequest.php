<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isDraft = $this->input('save_type') === 'draft';

        // ログインユーザー取得
        $user = Auth::user();

        // 自分のドラフト（未提出）Shop IDを取得
        $myDraftIds = $user->shops()->where('is_draft', true)->pluck('id')->toArray();

        // 現在編集中のShop（ルートに渡っている場合）を除外対象に追加
        // $currentShopId = optional($this->route('shop'))->id;
        $currentShopId = optional(Auth::user()->shop)->id;

        if ($currentShopId) {
            $myDraftIds[] = $currentShopId;
        }

        $rules = [
            'registration_type' => ($isDraft ? 'nullable' : 'required') . '|in:個人,個人事業主,法人,業務請負',

            'name' => [
                $isDraft ? 'nullable' : 'required',
                'string',
                'between:1,30',
                Rule::unique('shops', 'name')
                    ->where(fn($query) => $query->whereNotIn('id', $myDraftIds)),
            ],

            'manager' => [
                $isDraft ? 'nullable' : 'required',
                'string',
                'between:1,100',
                Rule::unique('shops', 'manager')
                    ->where(fn($query) => $query->whereNotIn('id', $myDraftIds)),
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

            // ファイル（形式とサイズのみ厳格）
            'photo_1' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_2' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_3' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_4' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_5' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_6' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'photo_7' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_1' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_2' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_3' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'file_4' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        // 本申請時のみ追加で必須ルール
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

            $rules['file_1'] = 'required_if:registration_type,個人,個人事業主|file|mimes:jpg,jpeg,png,pdf|max:5120';
            $rules['file_2'] = 'required_if:registration_type,業務請負|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }

        return $rules;
    }


}    
