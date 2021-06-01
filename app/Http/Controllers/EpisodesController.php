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
        return Episode::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'download_url' => 'required|url',
            'title' => 'required|max:255',
            'description' => 'required|max:5000',
            'episode_number' => 'required|integer',
        ]);

        return Episode::create($data);
    }

    /**
     * Store an uploaded audio file in file storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeEpisode(Request $request)
    {
        $file = $request->validate([
            'file'  =>  'required|file|mimes:mpeg,mp4,wav|max:200000'
        ]);

        $file = $request->file('file');
    
        $path = Storage::putFile('episodes', $file, 'public');
        $url = Storage::url($path);

        return response()->json([
            "url" => $url,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Episode  $episode
     * @return \Illuminate\Http\Response
     */
    public function show(Episode $episode)
    {
        return $episode;
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
        $data = $request->validate([
            'download_url' => 'url',
            'title' => 'max:255',
            'description' => 'max:5000',
            'episode_number' => 'integer',
        ]);

        $episode->update($data);

        return $episode->fresh();
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

        return response()->noContent();
    }
}
