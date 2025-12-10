<?php

namespace App\Http\Controllers;

use App\Mpdels\User;
use App\Models\Hall;
use App\Models\Donation;
use Barryvdh\DomPDF\PDF;
use App\Models\Accessorie;
use App\Models\HallEnquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BillController extends Controller
{

public function generateBill($id)
    {
        // Fetch hall enquiry details
        $enquiry = HallEnquiry::with('halll')->where('id', $id)->firstOrFail();

        // Check if this is a multi-hall enquiry
        $groupedEnquiries = collect();
        if ($enquiry->group_code) {
            // Fetch all enquiries in the same group
            $groupedEnquiries = HallEnquiry::where('group_code', $enquiry->group_code)
                                         ->orderBy('hall')
                                         ->get();
        } else {
            // Single enquiry, add it to the collection
            $groupedEnquiries->push($enquiry);
        }

        $isMultiHall = $groupedEnquiries->count() > 1;

        // Calculate totals for all halls
        $totalDeposit = 0;
        $totalRent = 0;
        $totalAccessoriesPrice = 0;
        $allAccessories = collect();

        foreach ($groupedEnquiries as $hallEnquiry) {
                // Calculate hours per day from start and end time
                $startTime = Carbon::createFromFormat('H:i:s', $hallEnquiry->start_time . ':00');
                $endTime = Carbon::createFromFormat('H:i:s', $hallEnquiry->end_time . ':00');
                $hoursPerDay = $startTime->diffInHours($endTime, false); // false to get positive difference

                // Get number of days for this hall
                $dates = $hallEnquiry->event_dates ? json_decode($hallEnquiry->event_dates, true) : [$hallEnquiry->event_date];
                $dates = array_filter($dates);
                $numberOfDays = count($dates);

                // Add to totals
                $totalDeposit += ($hallEnquiry->deposit ?? 0);
                $totalRent += ($hallEnquiry->rent_amount ?? 0);

                // Fetch accessories using stored IDs for this hall
                $accessoryIds = json_decode($hallEnquiry->accessorie, true); // Convert JSON string to array
                if (!empty($accessoryIds)) {
                    $hallAccessories = Accessorie::whereIn('id', $accessoryIds)->get();

                    // Calculate accessories price for this hall (per day × number of days)
                    $hallAccessoriesPrice = $hallAccessories->sum(function ($accessory) use ($hoursPerDay, $numberOfDays) {
                        $price = (float) ($accessory->price ?? 0);
                        $hours = (float) ($accessory->hours ?? 1);
                        if ($price <= 0 || $hours <= 0) return 0;

                        // Calculate blocks per day
                        $blocksPerDay = floor($hoursPerDay / $hours);
                        $pricePerDay = $price * max($blocksPerDay, 1); // Minimum 1 block per day

                        // Multiply by number of days
                        return $pricePerDay * $numberOfDays;
                    });

                    $totalAccessoriesPrice += $hallAccessoriesPrice;

                    // Add hall-specific accessories with hall info
                    foreach ($hallAccessories as $accessory) {
                        $price = (float) ($accessory->price ?? 0);
                        $hours = (float) ($accessory->hours ?? 1);
                        if ($price > 0 && $hours > 0) {
                            // Calculate blocks per day
                            $blocksPerDay = floor($hoursPerDay / $hours);
                            $pricePerDay = $price * max($blocksPerDay, 1); // Minimum 1 block per day

                            // Multiply by number of days
                            $totalPrice = $pricePerDay * $numberOfDays;

                            $accessory->calculated_price = $totalPrice;
                            $accessory->hall_name = $hallEnquiry->hall;
                            $allAccessories->push($accessory);
                        }
                    }
                }
            }

        // Calculate total amount (Hall Rent + Paid Accessories)
        $totalAmount = $totalRent + $totalAccessoriesPrice;
        $gst = $totalAmount * 0.18;  // Assuming 18% GST
        $finalAmount = $totalAmount + $gst;

        $pdf = app(PDF::class);
        $pdf = $pdf->loadView('bill.invoice', [
            'enquiry' => $enquiry,
            'groupedEnquiries' => $groupedEnquiries,
            'isMultiHall' => $isMultiHall,
            'allAccessories' => $allAccessories,
            'totalAccessoriesPrice' => $totalAccessoriesPrice,
            'totalAmount' => $totalAmount,
            'totalDeposit' => $totalDeposit,
            'totalRent' => $totalRent,
            'gst' => $gst,
            'finalAmount' => $finalAmount,
            'cache_buster' => time(),
        ]);

        // ✅ Define file name like "quotation_1.pdf"
        $fileName = 'quotation_' . $enquiry->id . '_' . time() . '.pdf';
        $directory = public_path('quotation'); // Folder in public directory
        $filePath = $directory . '/' . $fileName;

        // ✅ Ensure the directory exists
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true, true);
        }

        $pdf->save($filePath);

        // Update quotation_file for all enquiries in the group (for multi-hall enquiries)
        if ($enquiry->group_code) {
            // For multi-hall enquiries, update quotation_file for all enquiries in the group
            HallEnquiry::where('group_code', $enquiry->group_code)
                      ->update(['quotation_file' => $fileName]);
        } else {
            // For single hall enquiries, update just this enquiry
            $enquiry->quotation_file = $fileName;
            $enquiry->save();
        }

        session()->flash('pdf_download', $filePath);

        // ✅ Redirect with success message
        return redirect()->route('AdminHallEnquiry')->with('success', 'Enquiry details and quotation is generated successfully.');
    }




    private function sendQuotationMessage($enquiry)
    {
        if (!$enquiry->quotation_file) {
            Log::error("❌ Quotation file missing for ID: {$enquiry->id}");
            return;
        }

        // Check if this is a multi-hall enquiry
        $groupedEnquiries = collect();
        if ($enquiry->group_code) {
            $groupedEnquiries = HallEnquiry::where('group_code', $enquiry->group_code)->orderBy('hall')->get();
        } else {
            $groupedEnquiries->push($enquiry);
        }

        $isMultiHall = $groupedEnquiries->count() > 1;

        // Build hall information
        if ($isMultiHall) {
            $hallNames = $groupedEnquiries->pluck('hall')->toArray();
            $hallInfo = implode(', ', $hallNames) . ' (' . count($hallNames) . ' halls)';
        } else {
            $hallInfo = $enquiry->hall ?? 'N/A';
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
            $dateInfo = ' (' . count($allDates) . ' dates: ' . implode(', ', $allDates) . ')';
        } elseif (count($allDates) == 1) {
            $dateInfo = ' (' . $allDates[0] . ')';
        }

        $hallAndDateInfo = $hallInfo . $dateInfo;

        $admin = \App\Models\User::where('role', 'admin')->first();
        $quotationUrl = url('public/quotation/' . $enquiry->quotation_file);
        $adminMobile = $admin->mobile ?? 'Not Provided';
        $text = $adminMobile . ' | Click here to view: ' . $quotationUrl;

        $apiUrl = config('oneclick.api_url') . '/' . config('oneclick.api_version') . '/' . config('oneclick.phone_id') . '/messages';
        $token = config('oneclick.api_token');

        $payload = json_encode([
            'to' => $enquiry->contact_no,
            'recipient_type' => 'individual',
            'type' => 'template',
            'template' => [
                'name' => 'template3',
                'language' => [
                    'policy' => 'deterministic',
                    'code' => 'en'
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $enquiry->name],
                            ['type' => 'text', 'text' => $hallAndDateInfo],
                            ['type' => 'text', 'text' => $text]
                        ]
                    ]
                ]
            ]
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . trim($token),
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            Log::info("✅ Quotation WhatsApp sent to {$enquiry->contact_no}: $response");
        } else {
            Log::error("❌ Failed to send quotation WhatsApp to {$enquiry->contact_no}: [$httpCode] $response");
        }
    }



    public function DonationQuotation($id){
        $donation = Donation::findOrFail($id);

        $pdf = app(PDF::class);
        $pdf = $pdf->loadView('bill.DonationQuotation', compact('donation'));
        return $pdf->stream('DonationQuotation.pdf');
    }
    public function streamQuotation($id)
    {
        // Fetch the enquiry
        $enquiry = HallEnquiry::findOrFail($id);

        // Check if quotation file is stored
        if (!$enquiry->quotation_file) {
            abort(404, 'Quotation not generated yet.');
        }

        // Build the file path
        $filePath = public_path('quotation/' . $enquiry->quotation_file);

        // Check if file exists
        if (!File::exists($filePath)) {
            abort(404, 'Quotation file not found.');
        }

        // Stream the file to the browser
        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $enquiry->quotation_file . '"',
        ]);
    }

    public function editQuotation($id)
    {
        $enquiry = HallEnquiry::with('halll')->findOrFail($id);

        // Check if quotation exists
        if (!$enquiry->quotation_file) {
            return redirect()->back()->with('error', 'Quotation not generated yet.');
        }

        $accessories = Accessorie::all();

        return view('bill.edit-quotation', compact('enquiry', 'accessories'));
    }

    public function updateQuotation(Request $request, $id)
    {
        $enquiry = HallEnquiry::findOrFail($id);

        // Check if this is a multi-hall enquiry
        $groupedEnquiries = collect();
        if ($enquiry->group_code) {
            $groupedEnquiries = HallEnquiry::where('group_code', $enquiry->group_code)
                                         ->orderBy('hall')
                                         ->get();
        } else {
            $groupedEnquiries->push($enquiry);
        }
        $isMultiHall = $groupedEnquiries->count() > 1;

        if ($isMultiHall) {
            // Multi-hall validation
            $request->validate([
                'name' => 'required|string|max:255',
                'organization' => 'nullable|string|max:255',
                'gst_no' => 'nullable|string|max:255',
                'email' => 'required|email|max:255',
                'contact_no' => 'required|string|max:15',
                'address' => 'nullable|string',
                'referred_by' => 'nullable|string|max:255',
                'event_type' => 'nullable|string|max:255',
                'hall' => 'required|string|max:255',
                'event_date' => 'required|date',
                'duration' => 'nullable|string|max:255',
                'start_time' => 'required',
                'end_time' => 'required',
                'expected_audience' => 'nullable|integer',
                'stage_chairs_count' => 'nullable|integer',
                'hall_chairs_count' => 'nullable|integer',
                'hall_ids' => 'required|array',
                'hall_ids.*' => 'integer|exists:hallenquirys,id',
                'rent_amount' => 'required|array',
                'rent_amount.*' => 'nullable|string|max:255',
                'deposit' => 'required|array',
                'deposit.*' => 'nullable|string|max:255',
                'special_note' => 'nullable|array',
                'special_note.*' => 'nullable|string',
                'event_setup' => 'nullable|array',
                'event_setup.*' => 'nullable|string|max:255',
                'accessorie' => 'nullable|array',
                'stage_chairs_count' => 'nullable|array',
                'stage_chairs_count.*' => 'nullable|integer',
                'hall_chairs_count' => 'nullable|array',
                'hall_chairs_count.*' => 'nullable|integer',
                'id_proof' => 'nullable|string|max:255',
            ]);

            // Update main enquiry data
            $enquiry->update([
                'name' => $request->name,
                'organization' => $request->organization,
                'gst_no' => $request->gst_no,
                'email' => $request->email,
                'contact_no' => $request->contact_no,
                'address' => $request->address,
                'referred_by' => $request->referred_by,
                'event_type' => $request->event_type,
                'hall' => $request->hall,
                'event_date' => $request->event_date,
                'duration' => $request->duration,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'expected_audience' => $request->expected_audience,
                'id_proof' => $request->id_proof,
            ]);

            // Update each hall's specific data
            foreach ($request->hall_ids as $hallId) {
                $hallEnquiry = HallEnquiry::find($hallId);
                if ($hallEnquiry) {
                    $hallEnquiry->update([
                        'rent_amount' => $request->rent_amount[$hallId] ?? null,
                        'deposit' => $request->deposit[$hallId] ?? null,
                        'special_note' => $request->special_note[$hallId] ?? null,
                        'event_setup' => $request->event_setup[$hallId] ?? null,
                        'accessorie' => isset($request->accessorie[$hallId]) ? json_encode($request->accessorie[$hallId]) : null,
                        'stage_chairs_count' => $request->stage_chairs_count[$hallId] ?? null,
                        'hall_chairs_count' => $request->hall_chairs_count[$hallId] ?? null,
                    ]);
                }
            }
        } else {
            // Single hall validation - skip hall/event specific validations since they're readonly
            $request->validate([
                'name' => 'required|string|max:255',
                'organization' => 'nullable|string|max:255',
                'gst_no' => 'nullable|string|max:255',
                'email' => 'required|email|max:255',
                'contact_no' => 'required|string|max:15',
                'address' => 'nullable|string',
                'referred_by' => 'nullable|string|max:255',
                'event_type' => 'nullable|string|max:255',
                'expected_audience' => 'nullable|integer',
                'stage_chairs_count' => 'nullable|integer',
                'hall_chairs_count' => 'nullable|integer',
                'rent_amount' => 'nullable|string|max:255',
                'deposit' => 'nullable|string|max:255',
                'id_proof' => 'nullable|string|max:255',
                'event_setup' => 'nullable|string|max:255',
                'special_note' => 'nullable|string',
                'accessorie' => 'nullable|array',
                'accessorie.*' => 'integer|exists:accessories,id',
            ]);

            // Update enquiry data - skip hall/date/time fields since they're readonly display fields
            $updateData = [
                'name' => $request->name,
                'organization' => $request->organization,
                'gst_no' => $request->gst_no,
                'email' => $request->email,
                'contact_no' => $request->contact_no,
                'address' => $request->address,
                'referred_by' => $request->referred_by,
                'event_type' => $request->event_type,
                'expected_audience' => $request->expected_audience,
                'stage_chairs_count' => $request->stage_chairs_count,
                'hall_chairs_count' => $request->hall_chairs_count,
                'rent_amount' => $request->rent_amount,
                'deposit' => $request->deposit,
                'id_proof' => $request->id_proof,
                'event_setup' => $request->event_setup,
                'special_note' => $request->special_note,
                'accessorie' => $request->accessorie ? json_encode($request->accessorie) : null,
            ];

            $enquiry->update($updateData);
        }

        // Regenerate the quotation PDF
        $this->regenerateQuotation($enquiry->id);

        return redirect()->route('AdminHallEnquiry')->with('success', 'Quotation updated successfully.');
    }

    public function regenerateQuotation($id)
    {
        // Fetch hall enquiry details
        $enquiry = HallEnquiry::with('halll')->where('id', $id)->firstOrFail();

        // Check if this is a multi-hall enquiry
        $groupedEnquiries = collect();
        if ($enquiry->group_code) {
            // Fetch all enquiries in the same group
            $groupedEnquiries = HallEnquiry::where('group_code', $enquiry->group_code)
                                         ->orderBy('hall')
                                         ->get();
        } else {
            // Single enquiry, add it to the collection
            $groupedEnquiries->push($enquiry);
        }

        $isMultiHall = $groupedEnquiries->count() > 1;

        // Calculate totals for all halls
        $totalDeposit = 0;
        $totalRent = 0;
        $totalAccessoriesPrice = 0;
        $allAccessories = collect();

            foreach ($groupedEnquiries as $hallEnquiry) {
                // Calculate hours per day from start and end time
                $startTime = Carbon::createFromFormat('H:i:s', $hallEnquiry->start_time . ':00');
                $endTime = Carbon::createFromFormat('H:i:s', $hallEnquiry->end_time . ':00');
                $hoursPerDay = $startTime->diffInHours($endTime, false); // false to get positive difference

                // Get number of days for this hall
                $dates = $hallEnquiry->event_dates ? json_decode($hallEnquiry->event_dates, true) : [$hallEnquiry->event_date];
                $dates = array_filter($dates);
                $numberOfDays = count($dates);

                // Add to totals
                $totalDeposit += ($hallEnquiry->deposit ?? 0);
                $totalRent += ($hallEnquiry->rent_amount ?? 0);

                // Fetch accessories using stored IDs for this hall
                $accessoryIds = json_decode($hallEnquiry->accessorie, true); // Convert JSON string to array
                if (!empty($accessoryIds)) {
                    $hallAccessories = Accessorie::whereIn('id', $accessoryIds)->get();

                    // Calculate accessories price for this hall (per day × number of days)
                    $hallAccessoriesPrice = $hallAccessories->sum(function ($accessory) use ($hoursPerDay, $numberOfDays) {
                        $price = (float) ($accessory->price ?? 0);
                        $hours = (float) ($accessory->hours ?? 1);
                        if ($price <= 0 || $hours <= 0) return 0;

                        // Calculate blocks per day
                        $blocksPerDay = floor($hoursPerDay / $hours);
                        $pricePerDay = $price * max($blocksPerDay, 1); // Minimum 1 block per day

                        // Multiply by number of days
                        return $pricePerDay * $numberOfDays;
                    });

                    $totalAccessoriesPrice += $hallAccessoriesPrice;

                    // Add hall-specific accessories with hall info
                    foreach ($hallAccessories as $accessory) {
                        $price = (float) ($accessory->price ?? 0);
                        $hours = (float) ($accessory->hours ?? 1);
                        if ($price > 0 && $hours > 0) {
                            // Calculate blocks per day
                            $blocksPerDay = floor($hoursPerDay / $hours);
                            $pricePerDay = $price * max($blocksPerDay, 1); // Minimum 1 block per day

                            // Multiply by number of days
                            $totalPrice = $pricePerDay * $numberOfDays;

                            $accessory->calculated_price = $totalPrice;
                            $accessory->hall_name = $hallEnquiry->hall;
                            $allAccessories->push($accessory);
                        }
                    }
                }
            }

        // Calculate total amount (Hall Rent + Paid Accessories)
        $totalAmount = $totalRent + $totalAccessoriesPrice;
        $gst = $totalAmount * 0.18;  // Assuming 18% GST
        $finalAmount = $totalAmount + $gst;

        $pdf = app(PDF::class);
        $pdf = $pdf->loadView('bill.invoice', [
            'enquiry' => $enquiry,
            'groupedEnquiries' => $groupedEnquiries,
            'isMultiHall' => $isMultiHall,
            'allAccessories' => $allAccessories,
            'totalAccessoriesPrice' => $totalAccessoriesPrice,
            'totalAmount' => $totalAmount,
            'totalDeposit' => $totalDeposit,
            'totalRent' => $totalRent,
            'gst' => $gst,
            'finalAmount' => $finalAmount,
            'cache_buster' => time(),
        ]);

        // ✅ Define file name like "quotation_1.pdf"
        $fileName = 'quotation_' . $enquiry->id . '_' . time() . '.pdf';
        $directory = public_path('quotation'); // Folder in public directory
        $filePath = $directory . '/' . $fileName;

        // ✅ Ensure the directory exists
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true, true);
        }

        $pdf->save($filePath);

        // Update quotation_file for all enquiries in the group (for multi-hall enquiries)
        if ($enquiry->group_code) {
            // For multi-hall enquiries, update quotation_file for all enquiries in the group
            HallEnquiry::where('group_code', $enquiry->group_code)
                      ->update(['quotation_file' => $fileName]);
        } else {
            // For single hall enquiries, update just this enquiry
            $enquiry->quotation_file = $fileName;
            $enquiry->save();
        }
    }
}
