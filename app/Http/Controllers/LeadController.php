<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:leads');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:50|regex:/^[\d\s\+\-\(\)]+$/',
                'email' => 'required|email|max:255',
                'message' => 'nullable|string|max:1000',
                'source' => 'nullable|string|max:255',
                'project_id' => 'nullable|exists:projects,id',
                'service_id' => 'nullable|exists:services,id',
            ]);

            // Sanitize message to prevent XSS
            $message = $validated['message'] ?? '';
            $message = strip_tags($message);
            $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

            $lead = Lead::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'message' => $message,
                'source' => $validated['source'] ?? $request->header('referer') ?? 'contact_form',
                'status' => 'new',
                'project_id' => $validated['project_id'] ?? null,
                'service_id' => $validated['service_id'] ?? null,
            ]);

            Log::info('Lead created', ['lead_id' => $lead->id, 'email' => $lead->email]);

            return response()->json([
                'success' => true,
                'message' => __('messages.thank_you_for_feedback'),
            ]);
        } catch (ValidationException $e) {
            Log::warning('Lead validation failed', [
                'errors' => $e->errors(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lead creation error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'errors' => ['general' => __('messages.error')],
            ], 500);
        }
    }
}