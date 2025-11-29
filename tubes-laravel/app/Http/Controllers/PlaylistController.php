<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Auth::user()->playlists()->with('songs')->get();
        return view('playlists.index', compact('playlists'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        Auth::user()->playlists()->create(['name' => $request->name]);
        return back()->with('success', 'Playlist created.');
    }

    public function update(Request $request, Playlist $playlist)
    {
        if ($playlist->user_id !== Auth::id()) abort(403);
        
        if ($request->has('song_id')) {
            if ($playlist->songs()->where('song_id', $request->song_id)->exists()) {
                return back()->with('error', 'Lagu sudah ada di playlist.');
            }
            $playlist->songs()->attach($request->song_id);
            return back()->with('success', 'Lagu berhasil ditambahkan ke playlist.');
        }
        
        if ($request->has('remove_song_id')) {
            $playlist->songs()->detach($request->remove_song_id);
            return back()->with('success', 'Song removed from playlist.');
        }

        $playlist->update($request->only('name'));
        return back()->with('success', 'Playlist updated.');
    }

    public function destroy(Playlist $playlist)
    {
        if ($playlist->user_id !== Auth::id()) abort(403);
        $playlist->delete();
        return back()->with('success', 'Playlist deleted.');
    }
}
