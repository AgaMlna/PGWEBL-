<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\PolylinesModel;
use Illuminate\Http\Request;

class PolylinesController extends Controller
{
    public function __construct()
    {
        $this->polylines = new PolylinesModel();
    }

    public function index()
    {
        $polylines = DB::table('polylines')
            ->selectRaw('id, ST_AsGeoJSON(geom) as geom, name, description, image, created_at, updated_at')
            ->get();

        return response()->json($polylines);
    }


    public function getPolylines()
    {
        $polylines = $this->polylines->all();
        return response()->json($polylines);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('polylines.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi request
        $request->validate(
            [
                'name' => 'required|unique:polylines,name',
                'description' => 'required',
                'geom_polyline' => 'required',
                'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            ],
            [
                'name.required' => 'Name is required',
                'name.unique' => 'Name already exists',
                'description.required' => 'Description is required',
                'geom_polyline.required' => 'Geometry is required',
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
            $name_image = time() . "_polyline." . strtolower($image->getClientOriginalExtension());
            $image->move('storage/images', $name_image);
        } else {
            $name_image = null;
        }


        $data = [
            'geom' => DB::raw("ST_GeomFromText('" . $request->geom_polyline . "', 4326)"),
            'name' => $request->name,
            'description' => $request->description,
            'image'       => $name_image,
        ];

        // Gagal
        $insert = DB::table('polylines')->insert($data);
        if (!$insert) {
            return redirect()->route('map')->with('error', 'Polyline failed to add');
        }

        // Berhasil
        return redirect()->route('map')->with('success', 'Polyline has been added');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $polyline = $this->polylines->findOrFail($id);
        return view('polylines.show', compact('polyline'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = [
            'title' => 'Edit Polyline',
            'id' => $id,
        ];
        return view('Edit-polyline', $data);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:polylines,name,' . $id,
            'description' => 'required',
            'geom_polyline' => 'required',
        ]);

        $data = [
            'geom' => DB::raw("ST_GeomFromText('" . $request->geom_polyline . "', 4326)"),
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image ?? null,
        ];

        if (!$this->polylines->where('id', $id)->update($data)) {
            return redirect()->route('map')->with('error', 'Failed to update polyline');
        }

        return redirect()->route('map')->with('success', 'Polyline updated successfully');
    }






    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $polylines = $this->polylines->find($id);
        $imagefile = $polylines ? $polylines->image : null;


        if (!$this->polylines->destroy($id)) {
            return redirect()->route('map')->with('erorr', 'Polylines failed to delete');
        }

        //Delete image file
        if ($imagefile != null) {
            if (file_exists('./storage/images/' . $imagefile)) {
                unlink('./storage/images/' . $imagefile);
            }
        }

        return redirect()->route('map')->with('success', 'Polylines has been deleted');
    }
}
