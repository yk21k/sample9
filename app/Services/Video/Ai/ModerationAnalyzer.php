<?php

namespace App\Services\Video\Ai;

use App\Models\ShopVideoDraft;

class ModerationAnalyzer
{
    /**
     * Rekognition結果解析
     */
    public function analyze(
        array $result
    ): array {

        $labels = [];

        foreach (
            $result['ModerationLabels'] ?? []
            as $item
        ) {

            $labels[] = [

                'name'
                    => $item['ModerationLabel']['Name']
                        ?? null,

                'confidence'
                    => (float) (
                        $item['ModerationLabel']['Confidence']
                        ?? 0
                    ),

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | リスク計算
        |--------------------------------------------------------------------------
        */

        $riskScore = $this->calculateRisk(
            $labels
        );

        /*
        |--------------------------------------------------------------------------
        | 判定
        |--------------------------------------------------------------------------
        */

        $status = $this->judge(
            $riskScore
        );

        return [

            'status'
                => $status,

            'risk_score'
                => $riskScore,

            'labels'
                => $labels,

            'provider'
                => 'amazon_rekognition',

        ];
    }

    /**
     * Risk計算
     *
     * 現段階では検出ラベルの
     * 最大ConfidenceをRisk Scoreとする。
     */
    protected function calculateRisk(
        array $labels
    ): float {

        $max = 0.0;

        foreach ($labels as $label) {

            $confidence = (float) (
                $label['confidence'] ?? 0
            );

            if ($confidence > $max) {

                $max = $confidence;

            }
        }

        return $max;
    }

    /**
     * 判定
     *
     * 仮ルール：
     *
     * 90以上  → rejected
     * 70以上  → warning
     * 70未満 → approved
     */
    protected function judge(
        float $riskScore
    ): string {

        if ($riskScore >= 90) {

            return ShopVideoDraft::AI_REJECTED;
        }

        if ($riskScore >= 70) {

            return ShopVideoDraft::AI_WARNING;
        }

        return ShopVideoDraft::AI_APPROVED;
    }

    
}