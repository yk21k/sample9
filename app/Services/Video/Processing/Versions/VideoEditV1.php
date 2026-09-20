<?php

namespace App\Services\Video\Processing\Versions;

use App\Services\Video\Processing\LogoProcessor;

class VideoEditV1
{
    public function __construct(
        protected LogoProcessor $logoProcessor
    ) {
    }

    /**
     * V1
     *
     * ロゴ追加
     */
    public function build(
        string $logoPath
    ): array {

        return $this->logoProcessor->build(
            $logoPath
        );
    }
}