<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class MassGivePointUploads extends Model
{
    use SoftDeletes;

    protected $table = 'mass_give_point_uploads';

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $fillable = [
        'filename',
        'url',
        'send_at',
        'total',
        'ok_count',
        'ng_count',
        'ng_file_url',
        'process_status',
        'result',
    ];
}
