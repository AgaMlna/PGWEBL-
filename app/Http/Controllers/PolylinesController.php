<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PolylinesModel;

class PolylinesController extends Controller
{
    protected $polylines;

    public function __construct()
    {
        $this->polylines = new PolylinesModel();
    }

    /**
     * Return all polylines as GeoJSON with length.
     */
    public function index()
    {
        $polylines = DB::table('polylines')
            ->select(
                'id',
                DB::raw('ST_AsGeoJSON(geom) AS geom'),
                'name',
                'description',
                'image',
                DB::raw('ST_Length(geom, true) / 1000 AS length_km'),
                'created_at',
                'updated_at'
            )
            ->get();

        return response()->json($polylines);
    }

    /**
     * Show form to create a new polyline.
     */
    public function create()
    {
        return view('polylines.create');
    }

    /**
     * Store a newly created polyline.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'name'          => 'required|unique:polylines,name',
                'description'   => 'required',
                'geom_polyline' => 'required',
                'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            ],
            [
                'name.required'          => 'Name is required',
                'name.unique'            => 'Name already exists',
                'description.required'   => 'Description is required',
                'geom_polyline.required' => 'Geometry is required',
                'image.image'            => 'File must be an image',
                'image.mimes'            => 'Image format must be jpeg,png,jpg,gif,svg',
                'image.max'              => 'Max image size is 10MB',
            ]
        );

        // Membuat folder penyimpanan jika belum ada
        $storagePath = storage_path('app/public/images');
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        // Upload gambar jika ada
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_polyline.' . strtolower($file->getClientOriginalExtension());
            $file->move($storagePath, $fileName);
        } else {
            $fileName = null;
        }

        $data = [
            'geom'        => DB::raw("ST_GeomFromText('{$request->geom_polyline}',4326)"),
            'name'        => $request->name,
            'description' => $request->description,
            'image'       => $fileName,
            'created_at'  => now(),
            'updated_at'  => now(),
            'user_id'     => auth()->user()->id,
        ];

        $inserted = DB::table('polylines')->insert($data);

        if (!$inserted) {
            return redirect()->route('map')->with('error', 'Failed to add polyline');
        }

        return redirect()->route('map')->with('success', 'Polyline has been added');
    }

    /**
     * Display a single polyline.
     */
    public function show($id)
    {
        $polyline = $this->polylines->findOrFail($id);
        return view('polylines.show', compact('polyline'));
    }

    /**
     * Show form to edit polyline.
     */
    public function edit(string $id)
    {
        $data = [
            'title' => 'Edit Polyline',
            'id'    => $id,
        ];

        return view('edit-polyline', $data);
    }

    /**
     * Update polyline with optional image replacement.
     */
    public function update(Request $request, $id)
    {
        $polyline = $this->polylines->findOrFail($id);

        $request->validate(
            [
                'name'          => 'required|unique:polylines,name,' . $id,
                'description'   => 'required',
                'geom_polyline' => 'required',
                'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            ],
            [
                'name.required'          => 'Name is required',
                'name.unique'            => 'Name already exists',
                'description.required'   => 'Description is required',
                'geom_polyline.required' => 'Geometry is required',
                'image.image'            => 'File must be an image',
                'image.mimes'            => 'Image format must be jpeg,png,jpg,gif,svg',
                'image.max'              => 'Max image size is 10MB',
            ]
        );

        // Buat folder jika belum ada
        $storagePath = storage_path('app/public/images');
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        // Tangani upload gambar baru
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($polyline->image && file_exists($storagePath . '/' . $polyline->image)) {
                unlink($storagePath . '/' . $polyline->image);
            }
            $file = $request->file('image');
            $fileName = time() . '_polyline.' . strtolower($file->getClientOriginalExtension());
            $file->move($storagePath, $fileName);
        } else {
            $fileName = $polyline->image; // pakai gambar lama
        }

        $this->polylines->where('id', $id)->update([
            'geom'        => DB::raw("ST_GeomFromText('{$request->geom_polyline}',4326)"),
            'name'        => $request->name,
            'description' => $request->description,
            'image'       => $fileName,
            'updated_at'  => now(),
        ]);

        return redirect()->route('map')->with('success', 'Polyline updated successfully');
    }

    /**
     * Delete a polyline and its image file.
     */
    public function destroy($id)
    {
        $polyline = $this->polylines->findOrFail($id);
        $storagePath = storage_path('app/public/images');

        if (!$polyline->delete()) {
            return redirect()->route('map')->with('error', 'Failed to delete polyline');
        }

        if ($polyline->image && file_exists($storagePath . '/' . $polyline->image)) {
            unlink($storagePath . '/' . $polyline->image);
        }

        return redirect()->route('map')->with('success', 'Polyline deleted successfully');
    }
}
