<?php

namespace App\Http\Controllers;

use App\Models\ReportPreset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportPresetController extends Controller
{
    /**
     * Display a listing of all report presets.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Retrieve all presets from the database
        $presets = ReportPreset::all();

        // Return the presets as a JSON response
        return response()->json([
            'success' => true,
            'data' => $presets
        ], 200);
    }

    /**
     * Store a newly created report preset in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {

        $preset = new ReportPreset;
        $preset->name = $request->name;
        $preset->settings = $request->settings;
        $preset->save();

        // Return the newly created preset as a JSON response
        return response()->json([
            'success' => true,
            'data' => $preset
        ], 201);
    }
}
