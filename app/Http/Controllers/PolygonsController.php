<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PolygonsModel;


class PolygonsController extends Controller
{

    public function __construct()
    {
        $this->polygons = new PolygonsModel();
    }
    /**
     * Display a listing of the resource.
     */


    /**
     * Show the form for creating a new resource.
     */


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi request
        $request->validate(
            [
                'name' => 'required|unique:polygons,name',
                'description' => 'required',
                'geom_polygon' => 'required',
                'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            ],
            [
                'name.required' => 'Name is required',
                'name.unique' => 'Name already exist',
                'description.required' => 'Description is required',
                'geom_polygon.required' => 'Geometry is required',
                'image.image'          => 'File harus berupa gambar',
                'image.mimes'          => 'Format gambar hanya jpeg,png,jpg,gif,svg',
                'image.max'            => 'Ukuran gambar maksimal 10MB',
            ]
        );

        //Create image directory if not exsits
        if (!is_dir('storage/images')) {
            mkdir('./storage/images', 0777);
        }

        //Get image file
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name_image = time() . "_polygon." . strtolower($image->getClientOriginalExtension());
            $image->move('storage/images', $name_image);
        } else {
            $name_image = null;
        }

        // Simpan data
        $data = [
            'geom' => $request->geom_polygon,
            'name' => $request->name,
            'description' => $request->description,
            'image'       => $name_image,
        ];

        // Simpan ke database
        if (!$this->polygons->create($data)) {
            return redirect()->route('map')->with('error', 'Polygon failed to add');
        }

        // Redirect ke halaman peta
        return redirect()->route('map')->with('success', 'Polygon has been added');
    }

    public function create()
    {
        return view('polygons.create');
    }
}
