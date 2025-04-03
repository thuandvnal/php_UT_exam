<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'id',
        'type',
        'amount',
        'flag',
        'status',
        'priority'
    ];

    protected $casts = [
        'flag' => 'boolean',
        'amount' => 'float'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->status = 'new';
        $this->priority = 'low';
    }
} 