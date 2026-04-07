<?php

namespace App\Ports\Http\Controllers\Api;

use App\Infrastructure\Models\Form;
use App\Ports\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function submitForm(Request $request): JsonResponse
    {
        $request->validate([
            'full_name' => 'required|string',
            'phone' => 'required',
        ]);

        Form::query()->create([
            'full_name' => $request->input('full_name'),
            'phone' => $request->input('phone'),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
