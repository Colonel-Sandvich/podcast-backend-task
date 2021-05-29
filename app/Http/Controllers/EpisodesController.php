<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EpisodesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Episode::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $episode = Episode::create([
            'download_url' => request('download_url'),
            'title' => request('title'),
            'description' => request('description'),
            'episode_number' => request('episode_number')
        ]);

        return response()->json($episode, 200);
    }

    public function storeEpisode(Request $request)
    {
        $file = $request->validate([
            'file'  =>  'required|file|mimes:mpeg,mp4,wav|max:200000'
        ]);

        $file = $request->file('file');
    
        $path = Storage::disk('episodes')->putFile('episodes', $file, 'public');
        $url = Storage::disk('episodes')->url($path);

        return response()->json([
            "url" => $url,
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Episode  $episode
     * @return \Illuminate\Http\Response
     */
    public function show(Episode $episode)
    {
        return response()->json($episode, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Episode  $episode
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Episode $episode)
    {
        // $data = $request->validate([
        //     'download_url' => 'url',
        //     'title' => 'required',
        //     'description' => 'required',
        //     'episode_number' => 'required',
        //     'updated_at' => '',
        //     'created_at' => '',
        // ]);

        $episode->update($request->only(['download_url', 'title', 'description', 'episode_number']));

        return response()->json($episode, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Episode  $episode
     * @return \Illuminate\Http\Response
     */
    public function destroy(Episode $episode)
    {
        $episode->delete();

        return response()->json(null, 204);
    }
}
