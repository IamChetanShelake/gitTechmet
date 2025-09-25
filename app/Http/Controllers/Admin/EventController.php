<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Hall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::orderBy('event_date', 'desc')->paginate(10);
        return view('admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $halls = Hall::all();
        return view('admin.events.create', compact('halls'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date|after_or_equal:today',
            'event_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();

        // Handle image upload
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('Event_images'), $imageName);
            $data['image'] = $imageName;
        }

        Event::create($data);

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $event = Event::findOrFail($id);
        return view('admin.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $event = Event::findOrFail($id);
        $halls = Hall::all();
        return view('admin.events.edit', compact('event', 'halls'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($event->image && file_exists(public_path('Event_images/' . $event->image))) {
                unlink(public_path('Event_images/' . $event->image));
            }

            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('Event_images'), $imageName);
            $data['image'] = $imageName;
        }

        $event->update($data);

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);

        // Delete image if exists
        if ($event->image && file_exists(public_path('Event_images/' . $event->image))) {
            unlink(public_path('Event_images/' . $event->image));
        }

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event deleted successfully.');
    }
}
