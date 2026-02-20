<?php

namespace App\Models\SocialMedia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstagramSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'instagram_settings';

    protected $fillable = [
        'app_id',
        'app_secret',
        'is_active'
    ];
}
