<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="Content-Language" content="mr,en">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre Show Preparation List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        .container {
            width: 92%;
            margin: auto;
            padding: 10px;
        }
        h2, h3, h4 {
            text-align: center;
            margin: 5px 0;
            font-size: 14px;
        }
        p {
            margin: 3px 0;
            font-size: 12px;
        }

        /* Header table styling */
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

        /* Printing adjustments */
        @media print {
            body { margin: 0; padding: 0; }
            .container { width: 100%; padding: 5px; }
            h2, h3 { font-size: 13px; }
            table, th, td { font-size: 11px; padding: 3px; }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Header with logos and center information -->
    <table class="header-table">
        <tr>
            {{-- <td class="header-logo-left">
                <img src="{{ public_path('website/assets/Gokhale-logo.png') }}" alt="Gokhale Logo" style="height: 80px; width: 80px;">
            </td> --}}
            <td class="header-center">
                <p>G. E. Society's</p>
                <h2>GURUDAKSHINA PROJECT, NASHIK</h2>
                <h3>{{ $hallenquirie->hall }}</h3>
                <h2>Pre Show Preparation List</h2>
            </td>
            {{-- <td class="header-logo-right">
                <img style="height: 70px; width: 100px;" src="{{ public_path('website/assets/100gokhale-logo-2.png') }}" alt="100 Years Logo">
            </td> --}}
        </tr>
    </table>

    <hr style="border: none; border-top: 1px solid #ccc; margin: 20px 0;">

    <div style="font-size: 12px; line-height: 1.6;">
        <p><strong>Customer Name:</strong> {{ $hallenquirie->name }} </p>
        <p><strong>Contact Number:</strong> {{ $hallenquirie->contact_no }} </p>
        <p><strong>Organization Name:</strong> {{ $hallenquirie->organization }} </p>


        <p><strong>Show name:</strong> {{ $hallenquirie->event_type }} </p>
        <p><strong>Location in Premise:</strong> {{ $hallenquirie->hall }} </p>

        <br>
        @if(!empty($hallenquirie->event_setup))
            <p><strong>Special Note:</strong> {{ $hallenquirie->special_note }} </p>
        @endif

        @if(!empty($hallenquirie->event_setup))
            <p><strong>Event Setup:</strong> {{ $hallenquirie->event_setup }} </p>
        @endif

        @php
            $vendor_services = json_decode($hallenquirie->vendor, true) ?? [];
            $has_event = in_array('event', $vendor_services);
            $has_catering = in_array('catering', $vendor_services);
        @endphp

        <p><strong>Event Setup Requirements:</strong>
            @if($has_event)
                Yes
            @else
                No
            @endif
        </p>

        <p><strong>Comments:</strong>
            @php
                $selected_accessories = json_decode($hallenquirie->accessorie, true) ?? [];
                if (!empty($selected_accessories)) {
                    $accessory_names = \App\Models\Accessorie::whereIn('id', $selected_accessories)->pluck('name')->toArray();
                    echo implode(', ', $accessory_names);
                } else {
                    echo 'No';
                }
            @endphp

        </p>

        <p><strong>Catering Setup Requirements:</strong>
            @if($has_catering)
                Yes
            @else
                No
            @endif
        </p>

        @php
            $selected_accessories = json_decode($hallenquirie->accessorie, true) ?? [];
            $accessory_names = [];
            if (!empty($selected_accessories)) {
                $accessory_names = \App\Models\Accessorie::whereIn('id', $selected_accessories)->pluck('name')->toArray();
            }

            // Check if Sound Setup is selected
            $has_sound_setup = in_array('Sound Setup', $accessory_names);

            // Check if Cleaning is selected
            $has_cleaning = in_array('Cleaning', $accessory_names);
        @endphp

        <p><strong>Sound Setup:</strong>
            @if($has_sound_setup)
                Yes
            @else
                No
            @endif
        </p>

        <p><strong>Cleaning:</strong>
            @if($has_cleaning)
                Yes
            @else
                No
            @endif
        </p>

        <div style="margin-top: 80px;">
            <p style="margin-bottom: 20px;"><strong>Management Committee Member:</strong> _____________________________________</p>
            <p><strong>Name, Date and Signature:</strong> {{ date('d/m/Y') }} ____________________________________</p>
        </div>
    </div>

</div>

</body>
</html>
