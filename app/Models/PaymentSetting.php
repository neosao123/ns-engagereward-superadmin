<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

    protected $table = 'payment_settings';

    protected $fillable = [
        'payment_mode',
        'test_secret_key',
        'test_client_id',
        'live_secret_key',
        'live_client_id',
        'webhook_secret_key',
        'webhook_secret_live_key',
        'payment_gateway',
        'is_active',
        'is_delete',
    ];

    protected $casts = [
        'payment_mode' => 'integer',
        'is_active' => 'integer',
        'is_delete' => 'integer',
    ];

    /**
     * Get payment mode label
     */
    public function getPaymentModeLabel()
    {
        return $this->payment_mode == 1 ? 'Live' : 'Test';
    }

    /**
     * Scope for active records
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1)->where('is_delete', 0);
    }
}
