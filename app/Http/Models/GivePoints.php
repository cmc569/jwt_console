<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class GivePoints extends Model
{
    use SoftDeletes;

    protected $table = 'give_points';

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $fillable = [
        'mobile',
        'card_no',
        'operation',
        'point',
        'send_at',
        'end_at',
        'remark',
        'order_id',
        'response',
        'upload_id',
        'process_status',
    ];

}
