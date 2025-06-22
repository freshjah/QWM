<?php

namespace App\Http\Controllers\Web;

use App\Models\Watermark;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\WatermarkService;

class WatermarkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Watermark $watermark)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Watermark $watermark)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Watermark $watermark)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Watermark $watermark)
    {
        //
    }

    public function verify(Request $request)
    {
        $result = app(WatermarkService::class)->verify(
            $request->file_path,
            $request->timestamp,
            $request->document_uuid
        );

        return response()->json($result);
    }
}
