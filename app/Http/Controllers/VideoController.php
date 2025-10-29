<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class VideoController extends Controller
{
    public function index(){
        $videos = Video::all();
        return view('admin.videoCrud.VideoTable',compact('videos'));
    }

    public function addVideo(Request $request){
        $request->validate([
            'title' => 'nullable|string|max:255',
            'youtube_url' => 'required|url',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $video = new Video();
        $video->title = $request->title;
        $video->youtube_url = $request->youtube_url;

        if ($request->hasFile('thumbnail')) {
            $imageName = time() . '.' . $request->thumbnail->extension();
            $request->thumbnail->move('video_thumbnails', $imageName);
            $video->thumbnail = $imageName;
        } else {
            $imageName = '';
        }

        $video->save();

        if($video){
            return redirect('/videos')->with('success','Video has been Added');
        }
        else{
            return redirect('/videos/add')->with('fail','Operation failed');
        }
    }

    public function editVideo($id){
        $video = Video::find($id);
        return view('admin.videoCrud.UpdateVideo',compact('video'));
    }

    public function updateVideo(Request $request,$id){
        $request->validate([
            'title' => 'nullable|string|max:255',
            'youtube_url' => 'required|url',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $video = Video::find($id);

        if ($request->hasFile('thumbnail')) {
            $imageName = time() . '.' . $request->thumbnail->extension();
            $request->thumbnail->move('video_thumbnails', $imageName);

            // Delete old thumbnail
            $filePath = public_path('video_thumbnails/' . $video->thumbnail);
            if (is_file($filePath)) {
                unlink($filePath);
            }
            $video->thumbnail = $imageName;
        }

        $video->title = $request->title;
        $video->youtube_url = $request->youtube_url;

        $video->save();

        if($video){
            return redirect('/videos')->with('success','Video has been updated');
        }
        else{
            return redirect('/videos/add')->with('fail','Operation failed');
        }
    }

    public function ViewVideo($id){
        $video = Video::find($id);
        return view('admin.videoCrud.ViewVideo',compact('video'));
    }

    public function deleteVideo($id){
        $video = Video::findOrFail($id);

        // Delete the thumbnail file
        if ($video->thumbnail) {
            $imagePath = public_path('video_thumbnails/' . $video->thumbnail);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        $video->delete();

        if($video){
            return redirect('/videos')->with('success','Video has been deleted');
        }
        else{
            return redirect('/videos')->with('fail','Operation failed');
        }
    }
}
