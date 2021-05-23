<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use Illuminate\Http\Request;

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

        $episode->update($request->all());

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
