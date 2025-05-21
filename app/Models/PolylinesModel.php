<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PolylinesModel extends Model
{
    protected $table = 'polylines';
    protected $guarded = ['id'];

    /**
     * Fetch polylines and return GeoJSON FeatureCollection.
     */
    public function geojson_polylines()
    {
        $polylines = DB::table($this->table)
            ->select(
                'id',
                DB::raw('ST_AsGeoJSON(geom) AS geom'),
                'name',
                'description',
                'image',
                DB::raw('ST_Length(geom, true) AS length_m'),
                DB::raw('ST_Length(geom, true) / 1000 AS length_km'),
                'created_at',
                'updated_at'
            )
            ->get();

        return [
            'type'     => 'FeatureCollection',
            'features' => $polylines->map(function ($polyline) {
                return [
                    'type'       => 'Feature',
                    'geometry'   => json_decode($polyline->geom),
                    'properties' => [
                        'id'          => $polyline->id,
                        'name'        => $polyline->name,
                        'description' => $polyline->description,
                        'image'       => $polyline->image,
                        'length_m'    => $polyline->length_m,
                        'length_km'   => $polyline->length_km,
                        'created_at'  => $polyline->created_at,
                        'updated_at'  => $polyline->updated_at,
                    ],
                ];
            })->toArray(),
        ];
    }

    public function geojson_polyline($id)
    {
        $polylines = DB::table($this->table)
            ->select(
                'id',
                DB::raw('ST_AsGeoJSON(geom) AS geom'),
                'name',
                'description',
                'image',
                DB::raw('ST_Length(geom, true) AS length_m'),
                DB::raw('ST_Length(geom, true) / 1000 AS length_km'),
                'created_at',
                'updated_at'
            )
            ->where('id', $id)
            ->get();

        return [
            'type'     => 'FeatureCollection',
            'features' => $polylines->map(function ($polyline) {
                return [
                    'type'       => 'Feature',
                    'geometry'   => json_decode($polyline->geom),
                    'properties' => [
                        'id'          => $polyline->id,
                        'name'        => $polyline->name,
                        'description' => $polyline->description,
                        'image'       => $polyline->image,
                        'length_m'    => $polyline->length_m,
                        'length_km'   => $polyline->length_km,
                        'created_at'  => $polyline->created_at,
                        'updated_at'  => $polyline->updated_at,
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
