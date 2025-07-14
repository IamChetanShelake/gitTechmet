<?php

namespace App\Http\Controllers;


use App\Models\Hall;
use App\Models\Donation;
use Barryvdh\DomPDF\PDF;
use App\Models\Accessorie;


use App\Models\HallEnquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BillController extends Controller
{
    // public function generateBill($id)
    // {
    //     // Fetch hall enquiry details
    //     $enquiry = Hallenquiry::with(['hall'])->where('id', $id)->firstOrFail();


    //     //Fetch accessories using stored IDs
    //     // Retrieve actual accessories based on IDs
    //     $accessoryIds = json_decode($enquiry->accessorie, true); // Convert JSON string to array
    //     $accessories = Accessorie::whereIn('id', $accessoryIds)->get();

    //     // Calculate total accessories price
    //     $totalAccessoriesPrice = $accessories->sum(function ($accessory) {
    //         return $accessory->price ? $accessory->price * $accessory->quantity * ($accessory->hours ?? 1) : 0;
    //     });

    //     // Calculate total amount
    //     $totalAmount = ($enquiry->rent_amount ?? 0) + $totalAccessoriesPrice;
    //     $gst = $totalAmount * 0.18;  // Assuming 18% GST
    //     $finalAmount = $totalAmount + $gst;

    //     // Generate PDF
    //     // $pdf = PDF::loadView('bill.invoice', compact('enquiry', 'hall', 'accessories', 'total'));
    //     $pdf = app(PDF::class);
    //     $pdf = $pdf->loadView('bill.invoice', compact('enquiry', 'totalAccessoriesPrice', 'totalAmount', 'gst', 'finalAmount'));
    //     return $pdf->download('bill.pdf');
    // }



        //     public function generateBill($id)
        // {
        //     // Fetch hall enquiry details
        //     $enquiry = HallEnquiry::with(['hall'])->where('id', $id)->firstOrFail();

        //     // Fetch accessories using stored IDs
        //     $accessoryIds = json_decode($enquiry->accessorie, true); // Convert JSON string to array
        //     $accessories = Accessorie::whereIn('id', $accessoryIds)->get();

        //     // Calculate total accessories price
        //     $totalAccessoriesPrice = $accessories->sum(function ($accessory) {
        //         return $accessory->price ? $accessory->price * $accessory->quantity * ($accessory->hours ?? 1) : 0;
        //     });

        //     // Calculate total amount (Hall Rent + Accessories)
        //     $totalAmount = ($enquiry->rent_amount ?? 0) + $totalAccessoriesPrice;
        //     $gst = $totalAmount * 0.18;  // Assuming 18% GST
        //     $finalAmount = $totalAmount + $gst;

        //     // Generate PDF
        //     $pdf = app(PDF::class);
        //     $pdf = $pdf->loadView('bill.invoice', compact('enquiry', 'accessories', 'totalAccessoriesPrice', 'totalAmount', 'gst', 'finalAmount'));

        //     return $pdf->download('bill.pdf');
        // }


        public function generateBill($id)
    {
        // Fetch hall enquiry details
        $enquiry = HallEnquiry::with('halll')->where('id', $id)->firstOrFail();

        // Fetch accessories using stored IDs
        $accessoryIds = json_decode($enquiry->accessorie, true); // Convert JSON string to array
        $accessories = Accessorie::whereIn('id', $accessoryIds)->get();

        // Calculate total accessories price (excluding free accessories)
        $totalAccessoriesPrice = $accessories->sum(function ($accessory) {
            return $accessory->price ?? 0; // Just add price, no multiplication
        });

        // Calculate total amount (Hall Rent + Paid Accessories)
        $totalAmount = ($enquiry->rent_amount ?? 0) + $totalAccessoriesPrice;
        $gst = $totalAmount * 0.18;  // Assuming 18% GST
        $finalAmount = $totalAmount + $gst;

        // // Generate PDF
        // $pdf = app(PDF::class);
        // $pdf = $pdf->loadView('bill.invoice', compact('enquiry', 'accessories', 'totalAccessoriesPrice', 'totalAmount', 'gst', 'finalAmount'));


        // // return $pdf->download('bill.pdf');
        // return $pdf->stream('bill.pdf');
        $pdf = app(PDF::class);
        $pdf = $pdf->loadView('bill.invoice', compact('enquiry', 'accessories', 'totalAccessoriesPrice', 'totalAmount', 'gst', 'finalAmount'));

        // ✅ Define file name like "quotation_1.pdf"
        $fileName = 'quotation_' . $enquiry->id . '.pdf';
        $directory = public_path('quotation'); // Folder in public directory
        $filePath = $directory . '/' . $fileName;

        // ✅ Ensure the directory exists
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true, true);
        }

        // Save the PDF
        $pdf->save($filePath);

        // ✅ Store only the file name in the database
        $enquiry->quotation_file = $fileName;
        $enquiry->save();

        session()->flash('pdf_download', $filePath);

        // ✅ Redirect with success message
        return redirect()->route('AdminHallEnquiry')->with('success', 'Enquiry details and quotation is generated successfully.');





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
}
