<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditBlock extends Model
{
    protected $table = 'audit_blocks';

    // Evita líos de Mass Assignment
    protected $guarded = [];

    protected $casts = [
        'event_data' => 'array',
    ];
}
