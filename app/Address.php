<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;

class Address extends Model
{
    use PostgisTrait;

    protected $fillable = [
        'digital_address',
        'type',
        'region',
        'district'
    ];

    protected $postgisFields = [
        'polygon'
    ];

    protected $postgisTypes = [
        "polygon" => [
            'geomtype' => 'geography',
            'srid' => 4326
        ]
    ];





}
