<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class polygonsModel extends Model
{
    protected $table = 'polygons';

    protected $guarded = ['id'];



    public function geojson_polygons()
    {
        $polygons = $this->select(DB::raw('polygons.id,
            ST_AsGeoJSON(polygons.geom) as geom,
            polygons.name,
            polygons.description,
            polygons.image,
            polygons.created_at,
            polygons.updated_at,
            polygons.user_id,
            users.name as user_created'))
            ->LeftJoin('users', 'polygons.user_id', '=', 'users.id')
            ->get();


        $geojson = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];

        foreach ($polygons as $p) {
            $feature = [
                'type' => 'Feature',
                'geometry' => json_decode($p->geom),
                'properties' => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'description' => $p->description,
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at,
                    'image' => $p->image,
                    'user_created' => $p->user_created,
                    'user_id' => $p->user_id,
                ],
            ];
            array_push($geojson['features'], $feature);
        }
        return $geojson;
    }

    public function geojson_point($id)
    {
        $polygons = $this->select(DB::raw(
            'id, st_asgeojson(geom) as geom,
            name, description, image,
            created_at,
            updated_at'
        ))
            ->where('id', $id)
            ->get();


        $geojson = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];

        foreach ($polygons as $p) {
            $feature = [
                'type' => 'Feature',
                'geometry' => json_decode($p->geom),
                'properties' => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'description' => $p->description,
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at,
                    'image' => $p->image,
                ],
            ];
            array_push($geojson['features'], $feature);
        }
        return $geojson;
    }
}
