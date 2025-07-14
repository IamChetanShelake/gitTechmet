<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class CateringController extends Controller
{
    public function index()
    {
        $caterings = User::where('role','catering')->get();
        return view('admin.CateringCrud.CateringTable', compact('caterings'));
    }

    public function create(){
        return view('admin.CateringCrud.AddCatering');
    }

    // public function add(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'mobile' => 'required|string|max:20',
    //         'email' => 'required|email|unique:users,email',
    //         'password' => 'required|string|min:6',
    //         'adhar_card' => 'required|image|mimes:jpeg,png,jpg,gif,web',
    //         'pan_card' => 'required|image|mimes:jpeg,png,jpg,gif,webp',
    //         'qr_code' => 'required|image|mimes:jpeg,png,jpg,gif,webp'
    //     ]);

    //     $catering = new User();
    //     $catering->name = $request->name;
    //     $catering->mobile = $request->mobile;
    //     $catering->email = $request->email;
    //     $catering->password = Hash::make($request->password);
    //     $catering->role = 'catering'; // Setting role as 'event' instead of default 'user'
    //     $catering->firm_name = $request->firm_name;
    //     $catering->address = $request->address;
    //     $catering->city = $request->city;



    //     // Handling Aadhar Card Image Upload
    //     if ($request->hasFile('adhar_card')) {
    //         $image = $request->file('adhar_card');
    //         $imageName = time() . '_adhar.' . $image->getClientOriginalExtension();
    //         $image->move(public_path('CateringVendor_images'), $imageName);
    //         $catering->adhar_card = $imageName;
    //     }

    //     // Handling PAN Card Image Upload
    //     if ($request->hasFile('pan_card')) {
    //         $image = $request->file('pan_card');
    //         $imageName = time() . '_pan.' . $image->getClientOriginalExtension();
    //         $image->move(public_path('CateringVendor_images'), $imageName);
    //         $catering->pan_card = $imageName;
    //     }

    //     // Handling QR Code Image Upload
    //     if ($request->hasFile('qr_code')) {
    //         $image = $request->file('qr_code');
    //         $imageName = time() . '_qr.' . $image->getClientOriginalExtension();
    //         $image->move(public_path('CateringVendor_images'), $imageName);
    //         $catering->qr_code = $imageName;
    //     }

    //     $catering->upi_id = $request->upi_id;
    //     $catering->bank_name = $request->bank_name;
    //     $catering->bank_holder_name = $request->bank_holder_name;
    //     $catering->account_number = $request->account_number;
    //     $catering->ifsc = $request->ifsc;


    //     $catering->save();

    //     if ($catering) {
    //         return redirect('/catering')->with('success', 'Catering Vendor Registered Successfully');
    //     } else {
    //         return redirect('/catering')->with('fail', 'Operation Failed');
    //     }
    // }

    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'adhar_card' => 'required|image|mimes:jpeg,png,jpg,gif,webp',
            'pan_card' => 'required|image|mimes:jpeg,png,jpg,gif,webp',
            'qr_code' => 'required|image|mimes:jpeg,png,jpg,gif,webp'
        ]);

        $catering = new User();
        $catering->name = $request->name;
        $catering->mobile = $request->mobile;
        $catering->email = $request->email;
        $catering->password = Hash::make($request->password);
        $catering->role = 'catering';
        $catering->firm_name = $request->firm_name;
        $catering->address = $request->address;
        $catering->city = $request->city;

        if ($request->hasFile('adhar_card')) {
        $imageName = time() . '_adhar.' . $request->adhar_card->extension();
        $request->adhar_card->move('CateringVendor_images', $imageName);
    } else {
        $imageName = '';
    }
    $catering->adhar_card = $imageName;

    // PAN
    if ($request->hasFile('pan_card')) {
        $imageName = time() . '_pan.' . $request->pan_card->extension();
        $request->pan_card->move('CateringVendor_images', $imageName);
    } else {
        $imageName = '';
    }
    $catering->pan_card = $imageName;

    // QR Code
    if ($request->hasFile('qr_code')) {
        $imageName = time() . '_qr.' . $request->qr_code->extension();
        $request->qr_code->move('CateringVendor_images', $imageName);
    } else {
        $imageName = '';
    }
    $catering->qr_code = $imageName;

        $catering->upi_id = $request->upi_id;
        $catering->bank_name = $request->bank_name;
        $catering->bank_holder_name = $request->bank_holder_name;
        $catering->account_number = $request->account_number;
        $catering->ifsc = $request->ifsc;

        $catering->save();

        return redirect('/catering')->with('success', 'Catering Vendor Registered Successfully');
    }




    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'mobile' => 'required|string|max:20',
    //         'email' => 'required|email|unique:users,email,' . $id,
    //         'adhar_card' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
    //         'pan_card' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
    //         'qr_code' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp'
    //     ]);

    //     $catering = User::findOrFail($id);
    //     $catering->name = $request->name;
    //     $catering->mobile = $request->mobile;
    //     $catering->email = $request->email;
    //     $catering->firm_name = $request->firm_name;
    //     $catering->address = $request->address;
    //     $catering->city = $request->city;
    //     $catering->upi_id = $request->upi_id;
    //     $catering->bank_name = $request->bank_name;
    //     $catering->bank_holder_name = $request->bank_holder_name;
    //     $catering->account_number = $request->account_number;
    //     $catering->ifsc = $request->ifsc;

    //     // Update Aadhar Card
    //     if ($request->hasFile('adhar_card')) {
    //         if ($catering->adhar_card) {
    //             unlink(public_path('CateringVendor_images/' . $catering->adhar_card));
    //         }
    //         $image = $request->file('adhar_card');
    //         $imageName = time() . '_adhar.' . $image->getClientOriginalExtension();
    //         $image->move(public_path('CateringVendor_images'), $imageName);
    //         $catering->adhar_card = $imageName;
    //     }

    //     // Update PAN Card
    //     if ($request->hasFile('pan_card')) {
    //         if ($catering->pan_card) {
    //             unlink(public_path('CateringVendor_images/' . $catering->pan_card));
    //         }
    //         $image = $request->file('pan_card');
    //         $imageName = time() . '_pan.' . $image->getClientOriginalExtension();
    //         $image->move(public_path('CateringVendor_images'), $imageName);
    //         $catering->pan_card = $imageName;
    //     }

    //     // Update QR Code
    //     if ($request->hasFile('qr_code')) {
    //         if ($catering->qr_code) {
    //             unlink(public_path('CateringVendor_images/' . $catering->qr_code));
    //         }
    //         $image = $request->file('qr_code');
    //         $imageName = time() . '_qr.' . $image->getClientOriginalExtension();
    //         $image->move(public_path('CateringVendor_images'), $imageName);
    //         $catering->qr_code = $imageName;
    //     }

    //     $catering->save();

    //     return redirect('/catering')->with('success', 'Catering Vendor Updated Successfully');
    // }
    public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'mobile' => 'required|string|max:20',
        'email' => 'required|email|unique:users,email,' . $id,
        'adhar_card' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
        'pan_card' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
        'qr_code' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp'
    ]);

    $catering = User::findOrFail($id);
    $catering->name = $request->name;
    $catering->mobile = $request->mobile;
    $catering->email = $request->email;
    $catering->firm_name = $request->firm_name;
    $catering->address = $request->address;
    $catering->city = $request->city;
    $catering->upi_id = $request->upi_id;
    $catering->bank_name = $request->bank_name;
    $catering->bank_holder_name = $request->bank_holder_name;
    $catering->account_number = $request->account_number;
    $catering->ifsc = $request->ifsc;

    // Update Aadhar
    if ($request->hasFile('adhar_card')) {
        $imageName = time() . '_adhar.' . $request->adhar_card->extension();
        $request->adhar_card->move('CateringVendor_images', $imageName);
        $filePath = public_path('CateringVendor_images/' . $catering->adhar_card);
        if (is_file($filePath)) {
            unlink($filePath);
        }
        $catering->adhar_card = $imageName;
    }

    // Update PAN
    if ($request->hasFile('pan_card')) {
        $imageName = time() . '_pan.' . $request->pan_card->extension();
        $request->pan_card->move('CateringVendor_images', $imageName);
        $filePath = public_path('CateringVendor_images/' . $catering->pan_card);
        if (is_file($filePath)) {
            unlink($filePath);
        }
        $catering->pan_card = $imageName;
    }

    // Update QR
    if ($request->hasFile('qr_code')) {
        $imageName = time() . '_qr.' . $request->qr_code->extension();
        $request->qr_code->move('CateringVendor_images', $imageName);
        $filePath = public_path('CateringVendor_images/' . $catering->qr_code);
        if (is_file($filePath)) {
            unlink($filePath);
        }
        $catering->qr_code = $imageName;
    }

    $catering->save();

    return redirect('/catering')->with('success', 'Catering Vendor Updated Successfully');
}


    public function edit($id)
    {
        $catering = User::findOrFail($id);
        return view('admin.CateringCrud.UpdateCatering', compact('catering'));
    }
    public function view($id)
    {
        $catering = User::findOrFail($id);
        return view('admin.CateringCrud.ViewCatering', compact('catering'));
    }
    public function delete($id)
    {
        $catering = User::findOrFail($id);

        // Delete Aadhar Card Image
        if ($catering->adhar_card) {
            $imagePath = public_path('CateringVendor_images/' . $catering->adhar_card);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        // Delete PAN Card Image
        if ($catering->pan_card) {
            $imagePath = public_path('CateringVendor_images/' . $catering->pan_card);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        // Delete QR Code Image
        if ($catering->qr_code) {
            $imagePath = public_path('CateringVendor_images/' . $catering->qr_code);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        // Delete user from database
        $catering->delete();

        return redirect('/catering')->with('success', 'Catering Vendor Deleted Successfully');
    }
}
