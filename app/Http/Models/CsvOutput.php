<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class CsvOutput extends Model
{
    use SoftDeletes;
    
    protected $table = 'csv_output';
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $fillable = [
        'user_id',
        'rules',
        'sent',
        'process_status',
    ];

    public function user()
    {
        return $this->hasOne(Users::class, 'id', 'user_id');
    }
}
