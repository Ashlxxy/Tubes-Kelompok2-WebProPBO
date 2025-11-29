<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SongController extends Controller
{
    public function index(Request $request)
    {
        $query = Song::query();

        if ($request->has('q')) {
            $q = $request->q;
            $query->where('title', 'like', "%{$q}%")
                  ->orWhere('artist', 'like', "%{$q}%");
        }

        $songs = $query->latest()->get();

        return view('songs.index', compact('songs'));
    }

    public function show(Song $song)
    {
        // Increment plays
        $song->increment('plays');

        // Record history if logged in
        if (Auth::check()) {
            History::create([
                'user_id' => Auth::id(),
                'song_id' => $song->id,
                'played_at' => now(),
            ]);
        }

        return view('songs.show', compact('song'));
    }

    public function like(Song $song)
    {
        $user = Auth::user();

        if ($user->likedSongs()->where('song_id', $song->id)->exists()) {
            $user->likedSongs()->detach($song->id);
            $song->decrement('likes');
            $status = 'unliked';
        } else {
            $user->likedSongs()->attach($song->id);
            $song->increment('likes');
            $status = 'liked';
        }

        return back()->with('success', $status === 'liked' ? 'Liked!' : 'Unliked!');
    }

    public function storeComment(Request $request, Song $song)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $song->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return back()->with('success', 'Komentar terkirim!');
    }
    
    public function stream(Song $song)
    {
        $path = public_path($song->file_path);

        if (!file_exists($path)) {
            abort(404);
        }

        $fileSize = filesize($path);
        $length = $fileSize;
        $start = 0;
        $end = $fileSize - 1;

        $headers = [
            'Content-Type' => 'audio/mpeg',
            'Accept-Ranges' => 'bytes',
        ];

        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end = $end;

            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (strpos($range, ',') !== false) {
                $headers['Content-Range'] = "bytes $start-$end/$fileSize";
                return response()->file($path, $headers);
            }

            if ($range == '-') {
                $c_start = $fileSize - substr($range, 1);
            } else {
                $range = explode('-', $range);
                $c_start = $range[0];
                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $end;
            }

            $c_end = ($c_end > $end) ? $end : $c_end;
            if ($c_start > $c_end || $c_start > $fileSize - 1 || $c_end >= $fileSize) {
                return response('', 416);
            }

            $start = $c_start;
            $end = $c_end;
            $length = $end - $start + 1;

            $headers['Content-Length'] = $length;
            $headers['Content-Range'] = "bytes $start-$end/$fileSize";

            $stream = fopen($path, 'rb');
            fseek($stream, $start);
            $content = fread($stream, $length);
            fclose($stream);

            return response($content, 206, $headers);
        }

        $headers['Content-Length'] = $length;
        return response()->file($path, $headers);
    }
}
