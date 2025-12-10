<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BillController;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\Hall;
use Barryvdh\DomPDF\PDF;
use App\Models\Accessorie;
use App\Models\BookedHall;
use App\Models\HallEnquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Exports\HallEnquiryExport;


class HallEnquiryController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'contact_no' => 'required|digits:10|regex:/^[6-9]\d{9}$/',
            'event_type' => 'required',
            'selected_halls' => 'required|array|min:1',
            'selected_halls.*' => 'exists:halls,id',
            'sign_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Generate group code only for multi-hall enquiries
        $groupCode = count($request->selected_halls) > 1 ? 'grp_' . uniqid() : null;

        // Booking conflict validation removed as requested

        $enquiryRecords = [];

        // Create one enquiry record per hall with dates stored as JSON
        foreach ($request->selected_halls as $hallId) {
            $hallDetails = $request->hall_details[$hallId];
            $hall = Hall::find($hallId);

            // Filter out empty dates
            $validDates = array_filter($hallDetails['event_dates'], function($date) {
                return !empty($date) && trim($date) !== '';
            });

            // Sort dates for consistency
            sort($validDates);

            $hallenquiry = new HallEnquiry();
            if ($groupCode) {
                $hallenquiry->group_code = $groupCode;
            }
            $hallenquiry->name = $request->name;
            $hallenquiry->organization = $request->organization;
            $hallenquiry->gst_no = $request->gst_no;
            $hallenquiry->email = $request->email;
            $hallenquiry->contact_no = $request->contact_no;
            $hallenquiry->address = $request->address;
            $hallenquiry->referred_by = $request->referred_by;
            $hallenquiry->event_type = $request->event_type;
            $hallenquiry->event_dates = json_encode($validDates); // Store dates as JSON
            $hallenquiry->event_date = $validDates[0] ?? null; // Keep first date for backward compatibility
            $hallenquiry->hall = $hallDetails['hall_name'];
            $hallenquiry->hall_id = $hallId;
            // Handle duration and time based on hall type
            if (isset($hallDetails['session'])) {
                // For Gurudakshina hall with session selection
                // Map session values to enum values
                if ($hallDetails['session'] === 'morning') {
                    $hallenquiry->duration = 'half_day_morning';
                    $hallenquiry->start_time = '08:00';
                    $hallenquiry->end_time = '14:00';
                } elseif ($hallDetails['session'] === 'evening') {
                    $hallenquiry->duration = 'half_day_evening';
                    $hallenquiry->start_time = '16:00';
                    $hallenquiry->end_time = '21:00';
                } elseif ($hallDetails['session'] === 'full_day') {
                    $hallenquiry->duration = 'full_day';
                    // Check if custom times are provided for full day
                    if (isset($hallDetails['start_time']) && isset($hallDetails['end_time'])) {
                        $hallenquiry->start_time = $hallDetails['start_time'];
                        $hallenquiry->end_time = $hallDetails['end_time'];
                    } else {
                        // Default times if not provided
                        $hallenquiry->start_time = '08:00';
                        $hallenquiry->end_time = '21:00';
                    }
                }
            } else {
                // For other halls with duration selection
                $durationMapping = [
                    'morning' => 'half_day_morning',
                    'afternoon' => 'half_day_afternoon',
                    'evening' => 'half_day_evening',
                    'full_day' => 'full_day',
                    'half_day_morning' => 'half_day_morning',
                    'half_day_afternoon' => 'half_day_afternoon',
                    'half_day_evening' => 'half_day_evening',
                ];
                $mappedDuration = $durationMapping[$hallDetails['duration']] ?? $hallDetails['duration'];
                $hallenquiry->duration = $mappedDuration;

                // Check if custom times are provided
                if (isset($hallDetails['start_time']) && isset($hallDetails['end_time'])) {
                    $hallenquiry->start_time = $hallDetails['start_time'];
                    $hallenquiry->end_time = $hallDetails['end_time'];
                } else {
                    // Use default times based on duration
                    if ($mappedDuration === 'half_day_morning') {
                        $hallenquiry->start_time = '08:00';
                        $hallenquiry->end_time = '12:00';
                    } elseif ($mappedDuration === 'half_day_afternoon') {
                        $hallenquiry->start_time = '12:00';
                        $hallenquiry->end_time = '17:00';
                    } elseif ($mappedDuration === 'half_day_evening') {
                        $hallenquiry->start_time = '17:00';
                        $hallenquiry->end_time = '21:00';
                    } elseif ($mappedDuration === 'full_day') {
                        // For Art Gallery with full day (fallback to defaults if not provided, but should be provided)
                        $hallenquiry->start_time = '08:00';
                        $hallenquiry->end_time = '21:00';
                    }
                }
            }
            $hallenquiry->expected_audience = $hallDetails['expected_audience'];
            // Vendor services removed from enquiry form - will be set by admin later
            $hallenquiry->vendor = json_encode([]);
            $hallenquiry->accessorie = json_encode($request->accessorie ?? []);
            $hallenquiry->status = 'pending';

            // Handle signature upload (same for all records in the group)
            if ($request->hasFile('sign_image')) {
                $imageName = time() . '_' . $hallId . '_' . str_replace('-', '', ($validDates[0] ?? 'no_date')) . '.' . $request->sign_image->extension();
                $request->sign_image->move('sign_images', $imageName);
                $hallenquiry->sign_image = $imageName;
            } elseif ($request->digital_signature) {
                $digitalSignature = $request->digital_signature;
                $signatureData = explode(',', $digitalSignature)[1];
                $signatureDecoded = base64_decode($signatureData);
                $signatureName = 'digital_' . time() . '_' . $hallId . '_' . str_replace('-', '', ($validDates[0] ?? 'no_date')) . '.png';
                file_put_contents('sign_images/' . $signatureName, $signatureDecoded);
                $hallenquiry->sign_image = $signatureName;
            }

            $hallenquiry->typed_signature = $request->typed_signature;
            $hallenquiry->save();

            $enquiryRecords[] = $hallenquiry;
        }

        // Send WhatsApp message for the first enquiry (representative of the group)
        $representativeEnquiry = $enquiryRecords[0];
        $this->sendWhatsappMessage($representativeEnquiry);

        // Generate and send rules & regulations PDF for each enquiry
        foreach ($enquiryRecords as $enquiry) {
            $this->generateAndSendRulesPrint($enquiry);
        }

        return redirect()->back()->with('success', 'Multi-hall enquiry submitted successfully! A confirmation message has been sent.');
    }



    private function sendWhatsappMessage($hallenquiry)
        {
            // Check if this is a multi-hall enquiry
            $groupedEnquiries = collect();
            if ($hallenquiry->group_code) {
                $groupedEnquiries = HallEnquiry::where('group_code', $hallenquiry->group_code)->orderBy('hall')->get();
            } else {
                $groupedEnquiries->push($hallenquiry);
            }

            $isMultiHall = $groupedEnquiries->count() > 1;

            // Build hall information
            if ($isMultiHall) {
                $hallNames = $groupedEnquiries->pluck('hall')->toArray();
                $hallInfo = implode(', ', $hallNames) . ' (' . count($hallNames) . ' halls)';
            } else {
                $hallInfo = $hallenquiry->hall ?? 'N/A';
            }

            // Generate rules print link
            $rulesPrintLink = route('rules.print.view', $hallenquiry->id);

            $apiUrl = config('oneclick.api_url') . "/" . config('oneclick.api_version') . "/" . config('oneclick.phone_id') . "/messages";
            $token = trim(config('oneclick.api_token'));

            // Clean user's contact number
            $contactNumber = "+91" . $hallenquiry->contact_no;

            // 🧠 Fetch admin mobile number from users table
            $admin = \App\Models\User::where('role', 'admin')->first();

            $payloadArray = [
                "to" => $contactNumber,
                "recipient_type" => "individual",
                "type" => "template",
                "template" => [
                    "name" => "template1",
                    "language" => [
                        "policy" => "deterministic",
                        "code" => "en"
                    ],
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                ["type" => "text", "text" => $hallenquiry->name],
                                ["type" => "text", "text" => $hallInfo],
                                ["type" => "text", "text" => $admin->mobile ?? 'Not Provided'],
                                ["type" => "text", "text" => $rulesPrintLink]
                            ]
                        ]
                    ]
                ]
            ];

            $payload = json_encode($payloadArray);

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

            // Logging
            Log::info("📤 WhatsApp API Request Sent", [
                "URL" => $apiUrl,
                "Payload (raw JSON)" => $payload,
                "Payload (array)" => $payloadArray,
                "HTTP Code" => $httpCode,
                "Response" => $response,
                "cURL Error" => $curlError,
            ]);

            if ($httpCode == 200) {
                Log::info("✅ WhatsApp message sent to {$contactNumber}.");
            } else {
                Log::error("❌ WhatsApp message failed for {$contactNumber}. HTTP {$httpCode} - {$response}");
            }
        }

    private function sendWhatsappMessageForAdmin($hallenquiry)
        {
            // Check if this is a multi-hall enquiry
            $groupedEnquiries = collect();
            if ($hallenquiry->group_code) {
                $groupedEnquiries = HallEnquiry::where('group_code', $hallenquiry->group_code)->orderBy('hall')->get();
            } else {
                $groupedEnquiries->push($hallenquiry);
            }

            $isMultiHall = $groupedEnquiries->count() > 1;

            // Build hall information
            if ($isMultiHall) {
                $hallNames = $groupedEnquiries->pluck('hall')->toArray();
                $hallInfo = implode(', ', $hallNames) . ' (' . count($hallNames) . ' halls)';
            } else {
                $hallInfo = $hallenquiry->hall ?? 'N/A';
            }

            // Build date information
            $allDates = [];
            foreach ($groupedEnquiries as $hallEnquiry) {
                $dates = $hallEnquiry->event_dates ? json_decode($hallEnquiry->event_dates, true) : [$hallEnquiry->event_date];
                $dates = array_filter($dates);
                $allDates = array_merge($allDates, $dates);
            }
            $allDates = array_unique($allDates);
            sort($allDates);

            $dateInfo = '';
            if (count($allDates) > 1) {
                $dateInfo = implode(', ', $allDates) . ' (' . count($allDates) . ' dates)';
            } elseif (count($allDates) == 1) {
                $dateInfo = $allDates[0];
            }

            $apiUrl = config('oneclick.api_url') . "/" . config('oneclick.api_version') . "/" . config('oneclick.phone_id') . "/messages";
            $token = trim(config('oneclick.api_token'));

            // 🧠 Fetch admin from the users table
            $admin = \App\Models\User::where('role', 'admin')->first();

            if (!$admin || !$admin->mobile) {
                Log::error("❌ Admin not found or mobile number is missing.");
                return;
            }

            $adminPhone = "+91" . $admin->mobile;

            // ⚙️ Construct payload as per your template2
            $payloadArray = [
                "to" => $adminPhone,
                "recipient_type" => "individual",
                "type" => "template",
                "template" => [
                    "language" => [
                        "policy" => "deterministic",
                        "code" => "en"
                    ],
                    "name" => "template2",
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                ["type" => "text", "text" => $hallenquiry->name],
                                ["type" => "text", "text" => $hallInfo],
                                ["type" => "text", "text" => $dateInfo]
                            ]
                        ]
                    ]
                ]
            ];

            $payload = json_encode($payloadArray);

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

            // 🧾 Log for debugging
            Log::info("📤 WhatsApp Admin Message Sent", [
                "URL" => $apiUrl,
                "Payload" => $payload,
                "HTTP Code" => $httpCode,
                "Response" => $response,
                "cURL Error" => $curlError,
            ]);

            if ($httpCode == 200) {
                Log::info("✅ Admin WhatsApp message sent to {$adminPhone}.");
            } else {
                Log::error("❌ Admin WhatsApp message failed. HTTP {$httpCode} - {$response}");
            }
        }

    public function index()
    {
        $hallenquiries = HallEnquiry::whereIn('status', ['Viewed', 'pending'])
                                   ->whereNull('cancelled_at')
                                   ->orderBy('created_at', 'desc')
                                   ->get();

        // Group enquiries by group_code for display
        $groupedEnquiries = collect();
        $processedGroups = [];

        foreach ($hallenquiries as $enquiry) {
            if ($enquiry->group_code && !in_array($enquiry->group_code, $processedGroups)) {
                // This is a multi-hall enquiry group
                $groupEnquiries = HallEnquiry::where('group_code', $enquiry->group_code)
                                           ->whereIn('status', ['Viewed', 'pending'])
                                           ->whereNull('cancelled_at')
                                           ->get();

                // Group by hall within the group_code
                $hallGroups = $groupEnquiries->groupBy('hall');

                // Create a representative enquiry object for the group
                $groupRep = $groupEnquiries->first();
                $groupRep->is_group = true;

                // Build detailed hall information with dates
                $hallDetails = [];
                foreach ($hallGroups as $hallName => $hallEnquiries) {
                    $allDates = [];
                    foreach ($hallEnquiries as $enquiry) {
                        if ($enquiry->event_dates) {
                            $dates = json_decode($enquiry->event_dates, true) ?? [];
                            $allDates = array_merge($allDates, $dates);
                        } else {
                            $allDates[] = $enquiry->event_date;
                        }
                    }
                    $allDates = array_unique($allDates);
                    sort($allDates);
                    $hallDetails[] = $hallName . ' (' . count($allDates) . ' dates: ' . implode(', ', $allDates) . ')';
                }

                $groupRep->group_halls = $hallDetails;
                $groupRep->group_count = $groupEnquiries->count();
                $groupedEnquiries->push($groupRep);

                $processedGroups[] = $enquiry->group_code;
            } elseif (!$enquiry->group_code) {
                // This is a single hall enquiry
                $enquiry->is_group = false;
                $dates = $enquiry->event_dates ? json_decode($enquiry->event_dates, true) ?? [$enquiry->event_date] : [$enquiry->event_date];
                $dates = array_filter($dates); // Remove empty values
                $enquiry->group_halls = [$enquiry->hall . ' (' . count($dates) . ' date' . (count($dates) > 1 ? 's' : '') . ': ' . implode(', ', $dates) . ')'];
                $enquiry->group_count = 1;
                $groupedEnquiries->push($enquiry);
            }
        }

        return view('admin.AdminHallEnquiry.HallEnquiryTable', compact('groupedEnquiries'));
    }


    public function view($id){
        $hallenquirie = HallEnquiry::find($id);

        // Check if enquiry exists
        if (!$hallenquirie) {
            return redirect()->route('AdminHallEnquiry')->with('error', 'Enquiry not found.');
        }

        $accessories = Accessorie::all();

        // Check if this enquiry belongs to a group
        $groupedEnquiries = collect();
        if ($hallenquirie->group_code) {
            // Fetch all enquiries in the same group
            $groupedEnquiries = HallEnquiry::where('group_code', $hallenquirie->group_code)
                                         ->orderBy('hall')
                                         ->orderBy('event_date')
                                         ->get();
        } else {
            // Single enquiry, add it to the collection
            $groupedEnquiries->push($hallenquirie);
        }

        return view('admin.AdminHallEnquiry.ViewHallEnquiry', compact('hallenquirie', 'groupedEnquiries', 'accessories'));
    }

    public function storeOffice(Request $request, $id)
        {
            $hallEnquiry = HallEnquiry::find($id);
            // Check if this is a multi-hall enquiry
            if ($hallEnquiry->group_code) {
                // Handle multi-hall enquiry - update all enquiries in the group
                $groupEnquiries = HallEnquiry::where('group_code', $hallEnquiry->group_code)->get();

                foreach ($groupEnquiries as $enquiry) {
                    // Update per-hall specific fields
                    if (isset($request->rent_amount[$enquiry->id])) {
                        $enquiry->rent_amount = $request->rent_amount[$enquiry->id];
                    }
                    if (isset($request->deposit[$enquiry->id])) {
                        $enquiry->deposit = $request->deposit[$enquiry->id];
                    }
                    if (isset($request->special_note[$enquiry->id])) {
                        $enquiry->special_note = $request->special_note[$enquiry->id];
                    }
                    if (isset($request->event_setup[$enquiry->id])) {
                        $enquiry->event_setup = $request->event_setup[$enquiry->id];
                    }

                    // Update per-hall accessories
                    if (isset($request->accessorie[$enquiry->id])) {
                        $enquiry->accessorie = json_encode($request->accessorie[$enquiry->id]);
                    } else {
                        $enquiry->accessorie = json_encode([]);
                    }

                    // Update per-hall chair counts
                    if (isset($request->stage_chairs_count[$enquiry->id])) {
                        $enquiry->stage_chairs_count = $request->stage_chairs_count[$enquiry->id] === '' ? null : $request->stage_chairs_count[$enquiry->id];
                    } else {
                        $enquiry->stage_chairs_count = null;
                    }
                    if (isset($request->hall_chairs_count[$enquiry->id])) {
                        $enquiry->hall_chairs_count = $request->hall_chairs_count[$enquiry->id] === '' ? null : $request->hall_chairs_count[$enquiry->id];
                    } else {
                        $enquiry->hall_chairs_count = null;
                    }

                    // Update common fields for all halls in the group
                    $enquiry->vendor = json_encode($request->vendor ?? []);
                    $enquiry->id_proof = $request->id_proof;
                    $enquiry->status = 'Viewed';
                    $enquiry->save();
                }

                // Generate bill for the first enquiry (representative of the group)
                $billController = new BillController();
                return $billController->generateBill($groupEnquiries->first()->id);
            } else {
                // Handle single hall enquiry
                $hallEnquiry->rent_amount = $request->rent_amount;
                $hallEnquiry->deposit = $request->deposit;
                $hallEnquiry->special_note = $request->special_note ?: null;
                $hallEnquiry->id_proof = $request->id_proof;
                $hallEnquiry->event_setup = $request->event_setup ?: null;
                $hallEnquiry->stage_chairs_count = $request->stage_chairs_count === '' ? null : $request->stage_chairs_count;
                $hallEnquiry->hall_chairs_count = $request->hall_chairs_count === '' ? null : $request->hall_chairs_count;
                $hallEnquiry->vendor = json_encode($request->vendor ?? []);
                $hallEnquiry->status = 'Viewed';
                $hallEnquiry->accessorie = json_encode($request->accessorie ?? []);
                $hallEnquiry->save();

                // Generate Bill using BillController
                $billController = new BillController();
                return $billController->generateBill($id);
            }
        }

    public function cancel($id)
    {
        $hallEnquiry = HallEnquiry::findOrFail($id);

        // Check if this is part of a multi-hall enquiry
        if (!empty($hallEnquiry->group_code)) {
            // Cancel all enquiries in the group
            Log::info("Cancelling group enquiry with group_code: {$hallEnquiry->group_code}");

            $groupEnquiries = HallEnquiry::where('group_code', $hallEnquiry->group_code)
                                        ->whereNull('cancelled_at')
                                        ->get();

            Log::info("Found " . $groupEnquiries->count() . " enquiries in group {$hallEnquiry->group_code}");

            foreach ($groupEnquiries as $groupEnquiry) {
                $groupEnquiry->cancelled_at = now();
                $groupEnquiry->save();
                Log::info("Cancelled enquiry ID: {$groupEnquiry->id}");
            }

            return redirect()->route('AdminHallEnquiry')->with('success', 'Group enquiry cancelled successfully.');
        } else {
            // Cancel single enquiry
            Log::info("Cancelling single enquiry ID: {$id}");

            $hallEnquiry->cancelled_at = now();
            $hallEnquiry->save();

            return redirect()->route('AdminHallEnquiry')->with('success', 'Hall enquiry cancelled successfully.');
        }
    }

    public function preShowStream($id)
    {
        $hallenquirie = HallEnquiry::findOrFail($id);
        $pdf = app(PDF::class);
        $pdf = $pdf->loadView('admin.AdminHallEnquiry.PreShowPreparationList', compact('hallenquirie'));
        return $pdf->stream('Pre_Show_Preparation_List.pdf');
    }

    public function exportExcel($period = 'all')
    {
        $filename = 'hall_enquiries_report';

        switch ($period) {
            case 'weekly':
                $filename .= '_weekly.xlsx';
                break;
            case 'monthly':
                $filename .= '_monthly.xlsx';
                break;
            case 'yearly':
                $filename .= '_yearly.xlsx';
                break;
            default:
                $filename .= '_all.xlsx';
        }

        return \Maatwebsite\Excel\Facades\Excel::download(new HallEnquiryExport($period), $filename);
    }

    public function edit($id)
    {
        $hallenquirie = HallEnquiry::findOrFail($id);

        // Check if this enquiry belongs to a group
        $groupedEnquiries = collect();
        if ($hallenquirie->group_code) {
            // Fetch all enquiries in the same group
            $groupedEnquiries = HallEnquiry::where('group_code', $hallenquirie->group_code)
                                         ->orderBy('hall')
                                         ->orderBy('event_date')
                                         ->get();
        } else {
            // Single enquiry, add it to the collection
            $groupedEnquiries->push($hallenquirie);
        }

        $halls = Hall::all();

        // Prepare hall details data for JavaScript initialization
        $existingHallDetails = [];
        foreach ($groupedEnquiries as $enquiry) {
            $hallData = [
                'hall_name' => $enquiry->hall,
                'event_dates' => $enquiry->event_dates ? json_decode($enquiry->event_dates, true) : (isset($enquiry->event_date) && !empty($enquiry->event_date) ? [$enquiry->event_date] : []),
                'expected_audience' => $enquiry->expected_audience,
            ];

            // Filter out null/empty dates
            $hallData['event_dates'] = array_filter($hallData['event_dates'], function($date) {
                return !empty($date) && trim($date) !== '';
            });

            // Determine duration/session based on existing data
            $hall = Hall::find($enquiry->hall_id);
            if ($hall) {
                $hallName = strtolower($hall->name);
                if (str_contains($hallName, 'gurudakshina')) {
                    // For Gurudakshina hall
                    if ($enquiry->duration === 'half_day_morning') {
                        $hallData['session'] = 'morning';
                    } elseif ($enquiry->duration === 'half_day_evening') {
                        $hallData['session'] = 'evening';
                    } elseif ($enquiry->duration === 'full_day') {
                        $hallData['session'] = 'full_day';
                        $hallData['start_time'] = $enquiry->start_time;
                        $hallData['end_time'] = $enquiry->end_time;
                    }
                } elseif (str_contains($hallName, 'art gallery') || str_contains($hallName, 'art') && str_contains($hallName, 'gallery')) {
                    // For Art Gallery
                    $hallData['duration'] = $enquiry->duration;
                    if ($enquiry->duration === 'full_day') {
                        $hallData['start_time'] = $enquiry->start_time;
                        $hallData['end_time'] = $enquiry->end_time;
                    }
                } else {
                    // For other halls
                    $hallData['duration'] = $enquiry->duration;
                    $hallData['start_time'] = $enquiry->start_time;
                    $hallData['end_time'] = $enquiry->end_time;
                }
            }

            $existingHallDetails[$enquiry->hall_id] = $hallData;
        }

        return view('admin.AdminHallEnquiry.EditHallEnquiry', compact('hallenquirie', 'groupedEnquiries', 'halls', 'existingHallDetails'));
    }

    public function sendQuotation($id)
    {
        $enquiry = HallEnquiry::findOrFail($id);

        // Check if quotation exists
        if (!$enquiry->quotation_file) {
            return redirect()->back()->with('error', 'Quotation not generated yet.');
        }

        // Use the sendQuotationMessage method from BillController
        $billController = new BillController();
        $reflection = new \ReflectionClass($billController);
        $method = $reflection->getMethod('sendQuotationMessage');
        $method->setAccessible(true);
        $method->invoke($billController, $enquiry);

        return redirect()->back()->with('success', 'Quotation sent to customer successfully.');
    }

    public function rulesPrints()
    {
        $enquiries = HallEnquiry::whereNotNull('rules_print_file')
                               ->whereNull('cancelled_at')
                               ->orderBy('created_at', 'desc')
                               ->get();

        // Group enquiries by group_code for display
        $groupedEnquiries = collect();
        $processedGroups = [];

        foreach ($enquiries as $enquiry) {
            if ($enquiry->group_code && !in_array($enquiry->group_code, $processedGroups)) {
                // This is a multi-hall enquiry group
                $groupEnquiries = HallEnquiry::where('group_code', $enquiry->group_code)
                                           ->whereNotNull('rules_print_file')
                                           ->whereNull('cancelled_at')
                                           ->get();

                // Create a representative enquiry object for the group
                $groupRep = $groupEnquiries->first();
                $groupRep->is_group = true;
                $groupRep->group_count = $groupEnquiries->count();

                // Build hall information
                $hallNames = $groupEnquiries->pluck('hall')->unique()->toArray();
                $groupRep->group_halls = implode(', ', $hallNames);

                $groupedEnquiries->push($groupRep);
                $processedGroups[] = $enquiry->group_code;
            } elseif (!$enquiry->group_code) {
                // This is a single hall enquiry
                $enquiry->is_group = false;
                $enquiry->group_count = 1;
                $enquiry->group_halls = $enquiry->hall;
                $groupedEnquiries->push($enquiry);
            }
        }

        return view('admin.AdminHallEnquiry.RulesPrintsTable', compact('groupedEnquiries'));
    }

    public function update(Request $request, $id)
    {
        // Debug: Log the incoming request data
        Log::info('Update Request Data:', [
            'selected_halls' => $request->selected_halls,
            'hall_details' => $request->hall_details,
            'all_data' => $request->all()
        ]);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'contact_no' => 'required|digits:10|regex:/^[6-9]\d{9}$/',
            'event_type' => 'required',
            'selected_halls' => 'required|array|min:1',

        ]);

        $hallEnquiry = HallEnquiry::findOrFail($id);

        // Check if this is a multi-hall enquiry
        if ($hallEnquiry->group_code) {
            // Handle multi-hall enquiry update
            $groupEnquiries = HallEnquiry::where('group_code', $hallEnquiry->group_code)->get();

            // Get existing hall IDs for this group
            $existingHallIds = $groupEnquiries->pluck('hall_id')->toArray();
            $newHallIds = $request->selected_halls;

            // Handle additions and removals
            $hallsToAdd = array_diff($newHallIds, $existingHallIds);
            $hallsToRemove = array_diff($existingHallIds, $newHallIds);

            // Remove enquiries for halls that are no longer selected
            if (!empty($hallsToRemove)) {
                HallEnquiry::where('group_code', $hallEnquiry->group_code)
                          ->whereIn('hall_id', $hallsToRemove)
                          ->delete();
            }

            // Add new enquiries for newly selected halls
            if (!empty($hallsToAdd)) {
                $firstEnquiry = $groupEnquiries->first();
                foreach ($hallsToAdd as $hallId) {
                    if (isset($request->hall_details[$hallId])) {
                        $hall = Hall::find($hallId);
                        $hallData = $request->hall_details[$hallId];

                        // Filter out empty dates
                        $validDates = array_filter($hallData['event_dates'], function($date) {
                            return !empty($date) && trim($date) !== '';
                        });
                        sort($validDates);

                        $newEnquiry = new HallEnquiry();
                        $newEnquiry->group_code = $hallEnquiry->group_code;
                        $newEnquiry->name = $request->name;
                        $newEnquiry->organization = $request->organization;
                        $newEnquiry->gst_no = $request->gst_no;
                        $newEnquiry->email = $request->email;
                        $newEnquiry->contact_no = $request->contact_no;
                        $newEnquiry->address = $request->address;
                        $newEnquiry->referred_by = $request->referred_by;
                        $newEnquiry->event_type = $request->event_type;
                        $newEnquiry->event_dates = json_encode($validDates);
                        $newEnquiry->event_date = $validDates[0] ?? null;
                        $newEnquiry->hall = $hall->name;
                        $newEnquiry->hall_id = $hallId;
                        $newEnquiry->expected_audience = $hallData['expected_audience'];
                        $newEnquiry->status = $firstEnquiry->status;

                        // Handle duration and time based on hall type
                        if (isset($hallData['session'])) {
                            if ($hallData['session'] === 'morning') {
                                $newEnquiry->duration = 'half_day_morning';
                                $newEnquiry->start_time = '08:00';
                                $newEnquiry->end_time = '14:00';
                            } elseif ($hallData['session'] === 'evening') {
                                $newEnquiry->duration = 'half_day_evening';
                                $newEnquiry->start_time = '16:00';
                                $newEnquiry->end_time = '21:00';
                            } elseif ($hallData['session'] === 'full_day') {
                                $newEnquiry->duration = 'full_day';
                                // Check if custom times are provided for full day
                                if (isset($hallData['start_time']) && isset($hallData['end_time'])) {
                                    $newEnquiry->start_time = $hallData['start_time'];
                                    $newEnquiry->end_time = $hallData['end_time'];
                                } else {
                                    // Default times if not provided
                                    $newEnquiry->start_time = '08:00';
                                    $newEnquiry->end_time = '21:00';
                                }
                            }
                        } elseif (isset($hallData['duration'])) {
                            $newEnquiry->duration = $hallData['duration'];

                            // Check if custom times are provided
                            if (isset($hallData['start_time']) && isset($hallData['end_time'])) {
                                $newEnquiry->start_time = $hallData['start_time'];
                                $newEnquiry->end_time = $hallData['end_time'];
                            } else {
                                // Use default times based on duration
                                if ($hallData['duration'] === 'half_day_morning') {
                                    $newEnquiry->start_time = '08:00';
                                    $newEnquiry->end_time = '12:00';
                                } elseif ($hallData['duration'] === 'half_day_afternoon') {
                                    $newEnquiry->start_time = '12:00';
                                    $newEnquiry->end_time = '17:00';
                                } elseif ($hallData['duration'] === 'half_day_evening') {
                                    $newEnquiry->start_time = '17:00';
                                    $newEnquiry->end_time = '21:00';
                                } elseif ($hallData['duration'] === 'full_day') {
                                    // For Art Gallery with full day (fallback to defaults if not provided, but should be provided)
                                    $newEnquiry->start_time = '08:00';
                                    $newEnquiry->end_time = '21:00';
                                }
                            }
                        }

                        $newEnquiry->save();
                    }
                }
            }

            // Update all remaining enquiries in the group with common data and hall-specific data
            $remainingEnquiries = HallEnquiry::where('group_code', $hallEnquiry->group_code)->get();
            foreach ($remainingEnquiries as $enquiry) {
                // Update common fields
                $enquiry->name = $request->name;
                $enquiry->organization = $request->organization;
                $enquiry->gst_no = $request->gst_no;
                $enquiry->email = $request->email;
                $enquiry->contact_no = $request->contact_no;
                $enquiry->address = $request->address;
                $enquiry->referred_by = $request->referred_by;
                $enquiry->event_type = $request->event_type;
                $enquiry->vendor = json_encode($request->vendor ?? []);

                // Update hall-specific fields if provided
                if (isset($request->hall_details[$enquiry->hall_id])) {
                    $hallData = $request->hall_details[$enquiry->hall_id];

                    // Filter out empty dates
                    $validDates = array_filter($hallData['event_dates'], function($date) {
                        return !empty($date) && trim($date) !== '';
                    });
                    sort($validDates);

                    $enquiry->event_dates = json_encode($validDates);
                    $enquiry->event_date = $validDates[0] ?? null;
                    $enquiry->expected_audience = $hallData['expected_audience'];

                    // Handle duration and time based on hall type
                    if (isset($hallData['session'])) {
                        if ($hallData['session'] === 'morning') {
                            $enquiry->duration = 'half_day_morning';
                            $enquiry->start_time = '08:00';
                            $enquiry->end_time = '14:00';
                        } elseif ($hallData['session'] === 'evening') {
                            $enquiry->duration = 'half_day_evening';
                            $enquiry->start_time = '16:00';
                            $enquiry->end_time = '21:00';
                        } elseif ($hallData['session'] === 'full_day') {
                            $enquiry->duration = 'full_day';
                            // Check if custom times are provided for full day
                            if (isset($hallData['start_time']) && isset($hallData['end_time'])) {
                                $enquiry->start_time = $hallData['start_time'];
                                $enquiry->end_time = $hallData['end_time'];
                            } else {
                                // Default times if not provided
                                $enquiry->start_time = '08:00';
                                $enquiry->end_time = '21:00';
                            }
                        }
                    } elseif (isset($hallData['duration'])) {
                        $enquiry->duration = $hallData['duration'];

                        // Check if custom times are provided
                        if (isset($hallData['start_time']) && isset($hallData['end_time'])) {
                            $enquiry->start_time = $hallData['start_time'];
                            $enquiry->end_time = $hallData['end_time'];
                        } else {
                            // Use default times based on duration
                            if ($hallData['duration'] === 'half_day_morning') {
                                $enquiry->start_time = '08:00';
                                $enquiry->end_time = '12:00';
                            } elseif ($hallData['duration'] === 'half_day_afternoon') {
                                $enquiry->start_time = '12:00';
                                $enquiry->end_time = '17:00';
                            } elseif ($hallData['duration'] === 'half_day_evening') {
                                $enquiry->start_time = '17:00';
                                $enquiry->end_time = '21:00';
                            } elseif ($hallData['duration'] === 'full_day') {
                                // For Art Gallery with full day (fallback to defaults if not provided, but should be provided)
                                $enquiry->start_time = '08:00';
                                $enquiry->end_time = '21:00';
                            }
                        }
                    }
                }

                $enquiry->save();
            }
        } else {
            // Handle single hall enquiry update
            $hallId = $request->selected_halls[0];
            $hall = Hall::find($hallId);

            $hallEnquiry->name = $request->name;
            $hallEnquiry->organization = $request->organization;
            $hallEnquiry->gst_no = $request->gst_no;
            $hallEnquiry->email = $request->email;
            $hallEnquiry->contact_no = $request->contact_no;
            $hallEnquiry->address = $request->address;
            $hallEnquiry->referred_by = $request->referred_by;
            $hallEnquiry->event_type = $request->event_type;
            $hallEnquiry->vendor = json_encode($request->vendor ?? []);
            $hallEnquiry->hall = $hall->name;
            $hallEnquiry->hall_id = $hall->id;

            // Update hall-specific fields
            if (isset($request->hall_details[$hallId])) {
                $hallData = $request->hall_details[$hallId];

                // Filter out empty dates
                $validDates = array_filter($hallData['event_dates'], function($date) {
                    return !empty($date) && trim($date) !== '';
                });
                sort($validDates);

                $hallEnquiry->event_dates = json_encode($validDates);
                $hallEnquiry->event_date = $validDates[0] ?? null;
                $hallEnquiry->expected_audience = $hallData['expected_audience'];

                // Handle duration and time based on hall type
                if (isset($hallData['session'])) {
                    if ($hallData['session'] === 'morning') {
                        $hallEnquiry->duration = 'half_day_morning';
                        $hallEnquiry->start_time = '08:00';
                        $hallEnquiry->end_time = '14:00';
                    } elseif ($hallData['session'] === 'evening') {
                        $hallEnquiry->duration = 'half_day_evening';
                        $hallEnquiry->start_time = '16:00';
                        $hallEnquiry->end_time = '21:00';
                    } elseif ($hallData['session'] === 'full_day') {
                        $hallEnquiry->duration = 'full_day';
                        // Check if custom times are provided for full day
                        if (isset($hallData['start_time']) && isset($hallData['end_time'])) {
                            $hallEnquiry->start_time = $hallData['start_time'];
                            $hallEnquiry->end_time = $hallData['end_time'];
                        } else {
                            // Default times if not provided
                            $hallEnquiry->start_time = '08:00';
                            $hallEnquiry->end_time = '21:00';
                        }
                    }
                } elseif (isset($hallData['duration'])) {
                    $hallEnquiry->duration = $hallData['duration'];

                    // Check if custom times are provided
                    if (isset($hallData['start_time']) && isset($hallData['end_time'])) {
                        $hallEnquiry->start_time = $hallData['start_time'];
                        $hallEnquiry->end_time = $hallData['end_time'];
                    } else {
                        // Use default times based on duration
                        if ($hallData['duration'] === 'half_day_morning') {
                            $hallEnquiry->start_time = '08:00';
                            $hallEnquiry->end_time = '12:00';
                        } elseif ($hallData['duration'] === 'half_day_afternoon') {
                            $hallEnquiry->start_time = '12:00';
                            $hallEnquiry->end_time = '17:00';
                        } elseif ($hallData['duration'] === 'half_day_evening') {
                            $hallEnquiry->start_time = '17:00';
                            $hallEnquiry->end_time = '21:00';
                        } elseif ($hallData['duration'] === 'full_day') {
                            // For Art Gallery with full day (fallback to defaults if not provided, but should be provided)
                            $hallEnquiry->start_time = '08:00';
                            $hallEnquiry->end_time = '21:00';
                        }
                    }
                }
            }

            $hallEnquiry->save();
        }

        // Regenerate the quotation PDF with updated data
        $billController = new BillController();
        $billController->regenerateQuotation($hallEnquiry->id);

        return redirect()->route('AdminHallEnquiry')->with('success', 'Hall enquiry updated successfully.');
    }

    private function generateAndSendRulesPrint($enquiry)
    {
        try {
            // Generate PDF
            $pdf = app(PDF::class);
            $pdf = $pdf->loadView('bill.rules-print', compact('enquiry'));

            // Create filename
            $filename = 'rules_print_' . $enquiry->id . '_' . time() . '.pdf';
            $filepath = 'rules_prints/' . $filename;

            // Ensure directory exists
            if (!file_exists(public_path('rules_prints'))) {
                mkdir(public_path('rules_prints'), 0755, true);
            }

            // Save PDF to file
            $pdf->save(public_path($filepath));

            // Update enquiry record with PDF path
            $enquiry->rules_print_file = $filepath;
            $enquiry->save();

            // Send PDF via WhatsApp
            $this->sendRulesPrintWhatsApp($enquiry, $filepath);

            Log::info("✅ Rules & Regulations PDF generated and sent for enquiry ID: {$enquiry->id}");

        } catch (\Exception $e) {
            Log::error("❌ Failed to generate/send rules print for enquiry ID: {$enquiry->id}. Error: " . $e->getMessage());
        }
    }



    private function sendRulesPrintWhatsApp($enquiry, $pdfPath)
    {
        try {
            $apiUrl = config('oneclick.api_url') . "/" . config('oneclick.api_version') . "/" . config('oneclick.phone_id') . "/messages";
            $token = trim(config('oneclick.api_token'));

            $contactNumber = "+91" . $enquiry->contact_no;

            // Prepare document payload
            $payloadArray = [
                "to" => $contactNumber,
                "recipient_type" => "individual",
                "type" => "document",
                "document" => [
                    "link" => asset($pdfPath),
                    "filename" => "Rules_and_Regulations_" . $enquiry->hall . ".pdf",
                    "caption" => "Please find attached the Rules and Regulations agreement for " . $enquiry->hall . ". Kindly review and keep this document for your records."
                ]
            ];

            $payload = json_encode($payloadArray);

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

            Log::info("📤 WhatsApp Rules Print Sent", [
                "URL" => $apiUrl,
                "Payload" => $payload,
                "HTTP Code" => $httpCode,
                "Response" => $response,
                "cURL Error" => $curlError,
            ]);

            if ($httpCode == 200) {
                Log::info("✅ Rules print WhatsApp message sent to {$contactNumber}.");
            } else {
                Log::error("❌ Rules print WhatsApp message failed for {$contactNumber}. HTTP {$httpCode} - {$response}");
            }

        } catch (\Exception $e) {
            Log::error("❌ Failed to send rules print WhatsApp. Error: " . $e->getMessage());
        }
    }

    public function viewRulesPrint($id)
    {
        $enquiry = HallEnquiry::findOrFail($id);

        // Check if rules print exists
        if (!$enquiry->rules_print_file) {
            abort(404, 'Rules print not found.');
        }

        $pdfPath = public_path($enquiry->rules_print_file);

        if (!file_exists($pdfPath)) {
            abort(404, 'Rules print file not found.');
        }

        return response()->file($pdfPath);
    }


}
