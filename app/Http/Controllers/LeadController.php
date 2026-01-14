<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:50',
                'email' => 'required|email|max:255',
                'message' => 'nullable|string',
                'project_id' => 'nullable|exists:projects,id',
                'service_id' => 'nullable|exists:services,id',
            ]);

            $lead = Lead::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'message' => $validated['message'] ?? '',
                'source' => $request->header('referer') ?? 'contact_form',
                'status' => 'new',
                'project_id' => $validated['project_id'] ?? null,
                'service_id' => $validated['service_id'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Спасибо за заявку',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['general' => 'Произошла ошибка при создании заявки. Попробуйте позже.'],
            ], 500);
        }
    }
}