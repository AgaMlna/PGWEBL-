<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class PolylinesModel extends Model
{
    protected $table = 'polylines';
    protected $guarded = ['id'];

    public function geojson_polylines()
    {
        $polylines = DB::table($this->table)
            ->selectRaw("
                ST_AsGeoJSON(geom) AS geom,
                name,
                description,
                image,
                ST_Length(geom, true) AS length_m,
                ST_Length(geom, true) / 1000 AS length_km,
                created_at,
                updated_at
            ")
            ->get();

        return [
            'type' => 'FeatureCollection',
            'features' => collect($polylines)->map(function ($polyline) {
                return [
                    'type' => 'Feature',
                    'geometry' => json_decode($polyline->geom),
                    'properties' => [
                        'name' => $polyline->name,
                        'description' => $polyline->description,
                        'image' => $polyline->image,
                        'length_m' => $polyline->length_m,
                        'length_km' => $polyline->length_km,
                        'created_at' => $polyline->created_at,
                        'updated_at' => $polyline->updated_at
                    ],
                ];
            })->toArray(),
        ];
    }

    protected $fillable = [
        'geom',
        'name',
        'description',
        'image',
    ];
}
