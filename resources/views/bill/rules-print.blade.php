<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rules and Regulations - {{ $enquiry->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .container {
            width: 92%;
            margin: auto;
            padding: 10px;
            border: 1px solid #ddd;
        }
        h1, h2, h3 {
            text-align: center;
            margin: 5px 0;
            font-size: 16px;
        }
        p {
            margin: 3px 0;
            font-size: 12px;
        }
        .header-table {
            width: 100%;
            margin-bottom: 15px;
            border: none;
        }
        .header-table td {
            border: none;
            vertical-align: middle;
            padding: 5px;
        }
        .header-logo-left {
            width: 60px;
            text-align: left;
        }
        .header-center {
            text-align: center;
        }
        .header-logo-right {
            width: 60px;
            text-align: right;
        }
        .rules-section {
            margin: 20px 0;
        }
        .rules-section h3 {
            text-align: left;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .rules-section ol, .rules-section ul {
            padding-left: 20px;
            margin: 10px 0;
        }
        .rules-section li {
            margin-bottom: 5px;
            font-size: 11px;
        }
        .signature-section {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 20px;
        }
        .signature-box {
            display: inline-block;
            width: 200px;
            height: 60px;
            border: 1px solid #000;
            margin: 10px;
            text-align: center;
            vertical-align: top;
        }
        .signature-label {
            font-size: 10px;
            margin-bottom: 5px;
        }
        .user-details {
            margin: 15px 0;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .user-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .user-details td {
            padding: 3px;
            border: none;
        }
        .user-details td:first-child {
            font-weight: bold;
            width: 120px;
        }

        @media print {
            body { margin: 0; padding: 0; }
            .container { width: 100%; padding: 5px; border: none; }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Header with logos and center information -->
    <table class="header-table">
        <tr>
            <td class="header-logo-left">
                <img src="{{ public_path('website/assets/Gokhale-logo.png') }}" alt="Gokhale Logo" style="height: 80px; width: 80px;">
            </td>
            <td class="header-center">
                <p>G. E. Society's</p>
                <h1>Gurudakshina Project</h1>
                <p>
                    @php
                        // Check if this is a multi-hall enquiry
                        if ($enquiry->group_code) {
                            $groupedEnquiries = \App\Models\HallEnquiry::where('group_code', $enquiry->group_code)->get();
                            $hallNames = $groupedEnquiries->pluck('hall')->unique()->toArray();
                            echo implode(', ', $hallNames);
                        } else {
                            echo $enquiry->hall ?? 'N/A';
                        }
                    @endphp
                    , Nashik
                </p>
                <p style="font-weight: bold;">Contact No. : 8956984482</p>
                <h2>Rules and Regulations Agreement</h2>
            </td>
            <td class="header-logo-right">
                <img style="height: 70px; width: 100px;" src="{{ public_path('website/assets/100gokhale-logo-2.png') }}" alt="100 Years Logo">
            </td>
        </tr>
    </table>

    <!-- User Details -->
    <div class="user-details">
        <h3>Customer Details</h3>
        <table>
            <tr>
                <td>Name:</td>
                <td>{{ $enquiry->name }}</td>
            </tr>
            <tr>
                <td>Organization:</td>
                <td>{{ $enquiry->organization ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Contact No:</td>
                <td>{{ $enquiry->contact_no }}</td>
            </tr>
            <tr>
                <td>Email:</td>
                <td>{{ $enquiry->email }}</td>
            </tr>
            <tr>
                <td>Event Type:</td>
                <td>{{ $enquiry->event_type }}</td>
            </tr>
            <tr>
                <td>Hall:</td>
                <td>
                    @php
                        // Check if this is a multi-hall enquiry
                        if ($enquiry->group_code) {
                            $groupedEnquiries = \App\Models\HallEnquiry::where('group_code', $enquiry->group_code)->get();
                            $hallNames = $groupedEnquiries->pluck('hall')->unique()->toArray();
                            echo implode(', ', $hallNames);
                        } else {
                            echo $enquiry->hall;
                        }
                    @endphp
                </td>
            </tr>
            <tr>
                <td>Event Date(s):</td>
                <td>
                    @php
                        $dates = $enquiry->event_dates ? json_decode($enquiry->event_dates, true) : [$enquiry->event_date];
                        $dates = array_filter($dates);
                        sort($dates);
                    @endphp
                    {{ implode(', ', $dates) }}
                </td>
            </tr>
            <tr>
                <td>Generated on:</td>
                <td>{{ date('d-m-Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <!-- Rules and Regulations -->
    <div class="rules-section">
        @php
            // Get all enquiries in the group if multi-hall, otherwise just this enquiry
            if ($enquiry->group_code) {
                $groupedEnquiries = \App\Models\HallEnquiry::where('group_code', $enquiry->group_code)->orderBy('hall')->get();
            } else {
                $groupedEnquiries = collect([$enquiry]);
            }

            // Map hall names to their specific rule files
            $hallRulesMapping = [
                'Gurudakshina' => 'Rules and Regulations for Gurudakshina.txt',
                'Palash Hall' => 'Rules and Regulations for Palash Ha.txt',
                'Prin. T.A. Kulkarni Hall' => 'Rules and Regulations for Prin. T..txt',
                'Sharman Hall' => 'Rules and Regulations for Sharman H.txt',
                'Varanya Hall' => 'Rules and Regulations for Varanya.txt',
                'Varenya' => 'Rules and Regulations for Varanya.txt',
                'Dr. Sunadatai M. Gosavi Art Gallery' => 'DR. SUNADATAI M. GOSAVI ART GALLERY.txt',
                'Dr. Sundatai M. Gosavi Art Gallery' => 'DR. SUNADATAI M. GOSAVI ART GALLERY.txt',
                'Dr. Sunandatai M. Gosavi Art Gallery' => 'DR. SUNADATAI M. GOSAVI ART GALLERY.txt', // Added this variant
                // Direct mappings for the exact database names from error feedback
                'Rules and Regulations for Varenya' => 'Rules and Regulations for Varanya.txt',
                'Rules and Regulations for Dr. Sundatai M. Gosavi Art Gallery' => 'DR. SUNADATAI M. GOSAVI ART GALLERY.txt',
                // Mappings for print-specific hall names found in enquiries table
                'Gurudakshina Auditorium Hall' => 'Rules and Regulations for Gurudakshina.txt',
                'New Prin. T.A. Kulkarni Hall' => 'Rules and Regulations for Prin. T..txt',
                'Sharman' => 'Rules and Regulations for Sharman H.txt',
            ];

            // Function to find the best matching rule file for a hall name
            $findRuleFile = function($hallName) use ($hallRulesMapping) {
                // First try exact match (case sensitive)
                if (isset($hallRulesMapping[$hallName])) {
                    $filePath = base_path($hallRulesMapping[$hallName]);
                    if (file_exists($filePath)) {
                        return $hallRulesMapping[$hallName];
                    }
                }

                // Try case-insensitive exact matches
                foreach ($hallRulesMapping as $key => $file) {
                    if (strcasecmp($hallName, $key) === 0) {
                        $filePath = base_path($file);
                        if (file_exists($filePath)) {
                            return $file;
                        }
                    }
                }

                // Try partial matches (contains)
                $hallNameLower = strtolower($hallName);
                foreach ($hallRulesMapping as $key => $file) {
                    $keyLower = strtolower($key);
                    if (strpos($hallNameLower, $keyLower) !== false ||
                        strpos($keyLower, $hallNameLower) !== false) {
                        $filePath = base_path($file);
                        if (file_exists($filePath)) {
                            return $file;
                        }
                    }
                }

                // Try word-based matching for key terms
                $hallWords = explode(' ', $hallNameLower);
                foreach ($hallRulesMapping as $key => $file) {
                    $keyWords = explode(' ', strtolower($key));
                    $intersection = array_intersect($hallWords, $keyWords);
                    if (count($intersection) > 0) {
                        $filePath = base_path($file);
                        if (file_exists($filePath)) {
                            return $file;
                        }
                    }
                }

                return null;
            };

            // Get unique halls for this enquiry
            $uniqueHalls = $groupedEnquiries->pluck('hall')->unique();
        @endphp

        @foreach($uniqueHalls as $hallName)
            <h3>Rules and Regulations for {{ $hallName }}:</h3>
            @php
                $rulesFile = $findRuleFile($hallName);
                $rulesContent = '';
                if ($rulesFile) {
                    $rulesContent = file_get_contents(base_path($rulesFile));
                }
            @endphp

            @if($rulesContent)
                <pre style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; margin: 10px 0; padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd;">{{ $rulesContent }}</pre>
            @else
                <p><em>Rules and regulations file not found for {{ $hallName }}.</em></p>
            @endif
        @endforeach

        <h3>Additional General Terms:</h3>
        <ul>
            <li>This enquiry form does not constitute a confirmed booking.</li>
            <li>Final confirmation will be communicated via phone or email.</li>
            <li>All terms and conditions are subject to change without notice.</li>
            <li>By signing this document, you acknowledge that you have read and understood all terms for the selected hall{{ $enquiry->group_code ? 's' : '' }}.</li>
        </ul>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <h3>Customer Agreement</h3>
        <p>I, {{ $enquiry->name }}, hereby acknowledge that I have read, understood, and agreed to abide by all the rules and regulations mentioned above for the use of
            @php
                // Check if this is a multi-hall enquiry
                if ($enquiry->group_code) {
                    $groupedEnquiries = \App\Models\HallEnquiry::where('group_code', $enquiry->group_code)->get();
                    $hallNames = $groupedEnquiries->pluck('hall')->unique()->toArray();
                    echo implode(', ', $hallNames);
                } else {
                    echo $enquiry->hall;
                }
            @endphp
            hall{{ $enquiry->group_code ? 's' : '' }}.</p>

        <div style="margin-top: 40px;">
            @if($enquiry->sign_image)
                <div style="text-align: center;">
                    <p><strong>Customer Signature:</strong></p>
                    <img src="{{ public_path('sign_images/' . $enquiry->sign_image) }}" alt="Customer Signature" style="max-width: 200px; max-height: 60px; border: 1px solid #000;">
                </div>
            @elseif($enquiry->typed_signature)
                <div style="text-align: center;">
                    <p><strong>Customer Signature (Typed):</strong></p>
                    <div style="font-family: 'Brush Script MT', cursive; font-size: 24px; border-bottom: 1px solid #000; display: inline-block; padding: 5px 20px;">
                        {{ $enquiry->typed_signature }}
                    </div>
                </div>
            @else
                <div style="text-align: center;">
                    <p><strong>Signature:</strong> ________________________________</p>
                </div>
            @endif
        </div>

        <div style="margin-top: 20px; text-align: center;">
            <p><strong>Date:</strong> {{ date('d-m-Y') }}</p>
        </div>
    </div>

    <!-- Footer Note -->
    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
        <p>This document serves as proof of agreement to the rules and regulations for hall booking.</p>
        <p>G. E. Society's Gurudakshina Project - All Rights Reserved</p>
    </div>

</div>

</body>
</html>
