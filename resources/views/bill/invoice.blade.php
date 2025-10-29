<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 0; }
        .container { width: 92%; margin: auto; padding: 10px; border: 1px solid #ddd; }
        h2, h3 { text-align: center; margin: 5px 0; font-size: 14px; }
        p { margin: 3px 0; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; font-size: 12px; }
        .total { text-align: right; font-weight: bold; }
        ul { padding-left: 15px; font-size: 12px; margin: 5px 0; }

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
            .container { width: 100%; padding: 5px; border: none; }
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
            <td class="header-logo-left">
                <img src="{{ asset('website/assets/Gokhale-logo.png') }}" alt="Gokhale Logo" style="height: 80px; width: 80px;">
            </td>
            <td class="header-center">
                <p>G. E. Society's</p>
                <h2>Gurudakshina Project</h2>
                <p>{{ $enquiry->hall ?? 'N/A' }}, Nashik</p>
                <p style="font-weight: bold;">Contact No. : 8956984482</p>
                <h2>Quotation</h2>
            </td>
            <td class="header-logo-right">
                <img style="height: 70px; width: 100px;" src="{{ public_path('website/assets/100gokhale-logo-2.png') }}" alt="100 Years Logo">
            </td>
        </tr>
    </table>

    <p><strong>Customer Name:</strong> {{ $enquiry->name }}</p>
    <p><strong>Mob:</strong> {{ $enquiry->contact_no }}</p>
    <p><strong>Event Venue:</strong> {{ $enquiry->hall ?? 'N/A' }}</p>
    <p><strong>Event Date:</strong> {{ $enquiry->event_date }}</p>

    {{-- <p><strong>Schedule of Event:</strong> {{ $enquiry->start_time }} TO {{ $enquiry->end_time }}</p> --}}
    <p>
        <strong>Schedule of Event:</strong>
        {{ date('g:i A', strtotime($enquiry->start_time)) }} TO {{ date('g:i A', strtotime($enquiry->end_time)) }}
    </p>

    <p><strong>Type of Event:</strong> {{ $enquiry->event_type }}</p>
    {{-- <p><strong>Hall Name:</strong> {{ $enquiry->hall ?? 'N/A' }}</p> --}}

    <table>
        <tr>
            <th>Sr. No.</th>
            <th>Particulars</th>
            <th>Amount (Rs.)</th>
        </tr>

        <!-- Refundable Deposits -->
        <tr>
            <td>1</td>
            <td>{{$enquiry->hall}} (Refundable Deposit)</td>
            <td>{{ number_format($enquiry->deposit, 2) }}</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Parking Space</td>
            <td>0.00</td>
        </tr>
        <tr>
            <td colspan="2" class="total"><strong>Total Deposit</strong></td>
            <td><strong>{{ number_format($enquiry->deposit, 2) }}</strong></td>
        </tr>

        <!-- Charges Section -->
        {{-- <tr>
            <td>1</td>
            <td>{{ $enquiry->hall }}</td>
            <td>0.00</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Parking Space</td>
            <td>0.00</td>
        </tr> --}}
        {{-- <tr>
            <td colspan="2" class="total"><strong>Total Charges</strong></td>
            <td><strong>0.00</strong></td>
        </tr> --}}

        <!-- Charges Section (Hall Rent + Accessories) -->
        <tr>
            <td>1</td>
            <td>{{ $enquiry->hall }} (Hall Rent)</td>
            <td>{{ number_format($enquiry->rent_amount, 2) }}</td>
        </tr>

        @php $srNo = 2; @endphp
        @foreach ($accessories as $accessory)
        <tr>
            <td>{{ $srNo++ }}</td>
            <td>{{ $accessory->name }}</td>
            <td>{{ number_format($accessory->price, 2) }}</td>
        </tr>
        @endforeach

        <tr>
            <td colspan="2" class="total"><strong>Total Charges (Hall + Accessories)</strong></td>
            <td><strong>{{ number_format($totalAmount, 2) }}</strong></td>
        </tr>

        <!-- GST -->
        <tr>
            <td colspan="2" class="total">CGST @ 9%</td>
            <td>{{ number_format($gst / 2, 2) }}</td>
        </tr>
        <tr>
            <td colspan="2" class="total">SGST @ 9%</td>
            <td>{{ number_format($gst / 2, 2) }}</td>
        </tr>
        <tr>
            <td colspan="2" class="total"><strong>Total Charges (Including GST)</strong></td>
            <td><strong>{{ number_format($finalAmount, 2) }}</strong></td>
        </tr>
        <tr>
            <td colspan="2" class="total"><strong>Total Amt. (Including Deposit & GST)</strong></td>
            <td><strong>{{ number_format($enquiry->deposit + $finalAmount, 2) }}</strong></td>
        </tr>

    </table>

    <!-- Notes -->
    <h3>Notes:</h3>
    <ul>
        <li>1) Eatables are not allowed in the hall other than allotted space.</li>
        <li>2) Parking at Owners' Risk.</li>
        <li>3) Signing rules and regulations of respective halls.</li>
        <li>4) All rights are reserved with the Management.</li>
        <li>5) Every amount other than Deposit will be charged with GST.</li>
        <li>6) Full amount must be paid 7 Days before the event.</li>
    </ul>

    <!-- Bank Details -->
    <h3 style="margin-top: 20px">Bank Details for Booking</h3>
    <table>
        <tr><td><strong>Bank A/c Name:</strong></td> <td>Gokhale Education Society's Gurudakshina Project A/c</td></tr>
        <tr><td><strong>Bank:</strong></td> <td>Bank of Maharashtra</td></tr>
        <tr><td><strong>Branch:</strong></td> <td>College Campus, Nashik - 5</td></tr>
        <tr><td><strong>A/c No.:</strong></td> <td>6045177965</td></tr>
        <tr><td><strong>IFSC:</strong></td> <td>MAHB0000214</td></tr>
    </table>

    <!-- Service Contact Details -->
    <h3 style="margin-top: 20px">Gurudakshina Auditorium – Services Contact No.</h3>

    <table border="1" cellspacing="0" cellpadding="6" style="border-collapse: collapse; text-align:left;">
        <tr>
            <th>Role</th>
            <th>Name</th>
            <th>Contact</th>
        </tr>
        <tr>
            <td rowspan="2">Event Manager</td>
            <td>Ketan Ahire</td>
            <td>7972241909</td>
        </tr>
        <tr>
            <td>Nayan Chavan</td>
            <td>9552840788</td>
        </tr>
        <tr>
            <td rowspan="2">Catering Service</td>
            <td>Deepak Rai (Curry Leaves)</td>
            <td>7722015994</td>
        </tr>
        <tr>
            <td>Hiraman (Curry Leaves)</td>
            <td>7721910303</td>
        </tr>
        <tr>
            <td>Sound & Light Engineer</td>
            <td>Harish Pardeshi</td>
            <td>9860234696</td>
        </tr>
        <tr>
            <td>Photographer & Video Grapher</td>
            <td>Vishal Sonawane</td>
            <td>9766184642</td>
        </tr>
    </table>

</div>

</body>
</html>
