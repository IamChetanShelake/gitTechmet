<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppTestController extends Controller
{
    public function testWhatsApp()
    {
        try {
            $apiUrl = config('oneclick.api_url') . "/" . config('oneclick.api_version') . "/" . config('oneclick.phone_id') . "/messages";
            $token = trim(config('oneclick.api_token'));
            
            // Test payload - simple template
            $payloadArray = [
                "to" => "+919096879903", // Your test number
                "recipient_type" => "individual",
                "type" => "template",
                "template" => [
                    "language" => [
                        "policy" => "deterministic",
                        "code" => "en"
                    ],
                    "name" => "template10_clone",
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => "Test Customer"
                                ],
                                [
                                    "type" => "text", 
                                    "text" => "Test Hall"
                                ],
                                [
                                    "type" => "text",
                                    "text" => "2025-07-15"
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            
            $payload = json_encode($payloadArray);
            
            // Debug information
            Log::info("🔍 WhatsApp API Debug Test", [
                'api_url' => $apiUrl,
                'token_length' => strlen($token),
                'token_first_20' => substr($token, 0, 20),
                'token_last_20' => substr($token, -20),
                'payload' => $payloadArray
            ]);
            
            // Send cURL request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $token,
                "Content-Type: application/json"
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Log the result
            Log::info("📤 WhatsApp Test API Response", [
                'http_code' => $httpCode,
                'response' => $response,
                'curl_error' => $curlError
            ]);
            
            return response()->json([
                'status' => $httpCode == 200 ? 'success' : 'failed',
                'http_code' => $httpCode,
                'response' => json_decode($response, true),
                'curl_error' => $curlError,
                'api_url' => $apiUrl,
                'token_length' => strlen($token)
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp Test Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}