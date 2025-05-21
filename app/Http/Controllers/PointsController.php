<?php

namespace App\Http\Controllers;

use App\Models\PointsModel;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function __construct()
    {
        $this->points = new PointsModel();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [
            'title' => 'Map',
        ];
        return view('map', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Validate request
        $request->validate(
            [
                'name'        => 'required|unique:points,name',
                'description' => 'required',
                'geom_point'  => 'required',
                'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            ],
            [
                'name.required'        => 'Name is required',
                'name.unique'          => 'Name already exists',
                'description.required' => 'Description is required',
                'geom_point.required'  => 'Geometry point is required',
                'image.image'          => 'File harus berupa gambar',
                'image.mimes'          => 'Format gambar hanya jpeg,png,jpg,gif,svg',
                'image.max'            => 'Ukuran gambar maksimal 10MB',
            ]
        );

        //Membuat tempat penyimpanan gambar
        if (!is_dir('storage/images')) {
            mkdir('./storage/images', 0777, true);
        }

        //Mendapatkan file gambar (hanya jika ada)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name_image = time() . "_point." . strtolower($image->getClientOriginalExtension());
            $image->move('storage/images', $name_image);
        } else {
            $name_image = null;
        }

        $data = [
            'geom'        => $request->geom_point,
            'name'        => $request->name,
            'description' => $request->description,
            'image'       => $name_image,
        ];

        // Membuat data baru
        if (!$this->points->create($data)) {
            return redirect()->route('map')->with('error', 'Point failed to add');
        }

        // Kembali ke halaman map dengan pesan sukses
        return redirect()->route('map')->with('success', 'Point has been added');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = [
            'title' => 'Edit Point',
            'id' => $id,
        ];

        return view('edit-point', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //Validate request
        $request->validate(
            [
                'name' => 'required|unique:points,name,' . $id . ',id',
                'description' => 'required',
                'geom_point'  => 'required',
                'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            ],
            [
                'name.required'        => 'Name is required',
                'name.unique'          => 'Name already exists',
                'description.required' => 'Description is required',
                'geom_point.required'  => 'Geometry point is required',
                'image.image'          => 'File harus berupa gambar',
                'image.mimes'          => 'Format gambar hanya jpeg,png,jpg,gif,svg',
                'image.max'            => 'Ukuran gambar maksimal 10MB',
            ]
        );

        //Membuat tempat penyimpanan gambar
        if (!is_dir('storage/images')) {
            mkdir('./storage/images', 0777, true);
        }

        //Ambil data lama untuk image
        $old_image = $this->points->find($id)->image;

        //Mendapatkan file gambar baru jika ada
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name_image = time() . "_point." . strtolower($image->getClientOriginalExtension());
            $image->move('storage/images', $name_image);

            //Hapus file gambar lama jika ada
            if ($old_image != null && file_exists('./storage/images/' . $old_image)) {
                unlink('./storage/images/' . $old_image);
            }
        } else {
            $name_image = $old_image; // jika tidak upload gambar baru, tetap pakai gambar lama
        }

        $data = [
            'geom'        => $request->geom_point,
            'name'        => $request->name,
            'description' => $request->description,
            'image'       => $name_image,
        ];

        // Update data
        if (!$this->points->find($id)->update($data)) {
            return redirect()->route('map')->with('error', 'Point failed to update');
        }

        // Kembali ke halaman map dengan pesan sukses
        return redirect()->route('map')->with('success', 'Point has been updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $imagefile = $this->points->find($id)->image;

        if (!$this->points->destroy($id)){
            return redirect()->route('map')->with('error', 'Point failed to delete');
        }

        //Delete image file jika ada
        if ($imagefile != null) {
            if (file_exists('./storage/images/' . $imagefile)) {
                unlink('./storage/images/' . $imagefile);
            }
        }

        return redirect()->route('map')->with('success', 'Point has been deleted');
    }
}
