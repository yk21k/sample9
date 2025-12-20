<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupLocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'shop_id',
        'name',
        'address',
        'phone',
        'youtube_url',
        'recorded_at',
        'status',
    ];

    protected $casts = [
        'recorded_at' => 'date',
    ];

    // 数値ステータスの定義
    public const STATUS_PENDING  = 0;
    public const STATUS_APPROVED = 1;
    public const STATUS_REJECTED = 2;

    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_PENDING  => '審査中',
            self::STATUS_APPROVED => '承認済',
            self::STATUS_REJECTED => '拒否',
        ];
    }

    // ラベル取得アクセサ
    public function getStatusLabelAttribute(): string
    {
        return self::getStatusLabels()[$this->status] ?? '不明';
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    // 関連ショップ
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id'); // 明示的に外部キーを指定
    }

    /**
     * 埋め込み用のYouTube URLを返すアクセサ
     */
    public function getEmbedYoutubeUrlAttribute()
    {
        if (empty($this->youtube_url)) {
            return null;
        }

        $url = $this->youtube_url;

        // ✅ さまざまなYouTube URL形式に対応
        if (str_contains($url, 'watch?v=')) {
            // 通常URL → embed
            $url = str_replace('watch?v=', 'embed/', $url);
        } elseif (str_contains($url, 'youtu.be/')) {
            // 短縮URL → embed
            $url = str_replace('youtu.be/', 'www.youtube.com/embed/', $url);
        } elseif (str_contains($url, 'shorts/')) {
            // shorts → embed
            $url = str_replace('shorts/', 'embed/', $url);
        }

        return $url;
    }
}
