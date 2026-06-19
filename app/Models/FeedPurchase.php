<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedPurchase extends Model
{
    protected $fillable = [
        'purchase_date', 'feed_type', 'quantity', 'unit', 'rate', 'amount',
        'supplier', 'remarks', 'feed_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public static function feedTypes(): array
    {
        return [
            'green_fodder' => 'હરી ચારો',
            'dry_fodder' => 'સૂકો ચારો',
            'khal' => 'ખળ',
            'concentrate' => 'કોન્સન્ટ્રેટ',
            'mineral_mix' => 'મિનરલ મિક્સ',
            'other' => 'અન્ય',
        ];
    }
}
