<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class ProjectBurgerkingCoupons extends Model
{
    use SoftDeletes;
    
    public $timestamps = false;

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $fillable = [
        'id',
        'guid',
        'campaign_guid',
        'title',
        'subtitle',
        'memo',
        'description_picture',
        'description_title',
        'description',
        'end_at',
        'verify_type',
        'show_type',
        'verify_description',
        'style',
        'picture',
        'sort',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

}
