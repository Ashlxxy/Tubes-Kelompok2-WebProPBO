@extends('layouts.app')

@section('title', $song->title . ' — UKM Band')

@section('content')
<div class="container-xxl py-5 fade-in">
    <div class="row g-4">
        <div class="col-lg-4">
            <img src="{{ asset($song->cover_path) }}" class="w-100 rounded-4 shadow-lg" alt="{{ $song->title }}">
        </div>
        <div class="col-lg-8">
            <div class="card-dark p-4 rounded-4 border-dark-700 h-100 d-flex flex-column justify-content-center">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                    <div>
                        <h1 class="display-6 fw-bold mb-1">{{ $song->title }}</h1>
                        <h4 class="text-accent mb-0">{{ $song->artist }}</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('songs.like', $song->id) }}" method="POST">
                            @csrf
                            @auth
                                @if(Auth::user()->likedSongs->contains($song->id))
                                    <button class="btn btn-accent btn-lg"><i class="bi bi-heart-fill"></i> {{ $song->likes }}</button>
                                @else
                                    <button class="btn btn-outline-accent btn-lg"><i class="bi bi-heart"></i> {{ $song->likes }}</button>
                                @endif
                            @else
                                <button class="btn btn-outline-accent btn-lg"><i class="bi bi-heart"></i> {{ $song->likes }}</button>
                            @endauth
                        </form>
                        @auth
                        <button class="btn btn-outline-accent btn-lg" data-bs-toggle="modal" data-bs-target="#playlistModal"><i class="bi bi-plus-circle"></i> Playlist</button>
                        @endauth
                    </div>
                </div>

                <p class="mt-4 text-dark-200 lead">{{ $song->description }}</p>

                <div class="mt-auto pt-4">
                    <audio controls class="w-100 custom-audio">
                        <source src="{{ route('songs.stream', $song->id) }}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="row mt-5">
        <div class="col-lg-8 offset-lg-4">
            <h4 class="mb-3">Komentar</h4>
            
            @auth
            <form action="{{ route('songs.comments.store', $song->id) }}" method="POST" class="mb-4">
                @csrf
                <div class="input-group">
                    <input type="text" name="content" class="form-control form-control-dark" placeholder="Tulis komentar..." required>
                    <button class="btn btn-accent" type="submit"><i class="bi bi-send"></i></button>
                </div>
            </form>
            @else
            <div class="alert alert-dark mb-4">Silakan login untuk menulis komentar.</div>
            @endauth

            <div class="list-group list-group-flush rounded-3 overflow-hidden">
                @forelse($song->comments as $comment)
                <div class="list-group-item bg-dark-900 border-dark-700 p-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-bold text-white">{{ $comment->user->name }}</span>
                        <small class="text-dark-300">{{ $comment->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-0 text-white">{{ $comment->content }}</p>
                </div>
                @empty
                <div class="text-dark-300 py-3">Belum ada komentar.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Playlist Modal -->
@auth
<div class="modal fade" id="playlistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-dark">
            <div class="modal-header border-dark-700">
                <h5 class="modal-title"><i class="bi bi-music-note-list me-2"></i>Tambahkan ke Playlist</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(Auth::user()->playlists->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-music-player display-4 text-dark-300 mb-3 d-block"></i>
                        <p class="text-dark-200 mb-3">Anda belum memiliki playlist.</p>
                        <form action="{{ route('playlists.store') }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="name" class="form-control form-control-dark" placeholder="Nama Playlist Baru..." required>
                                <button class="btn btn-accent">Buat</button>
                            </div>
                        </form>
                    </div>
                @else
                    <p class="text-dark-300 mb-3 small">Pilih playlist untuk menambahkan lagu ini:</p>
                    <div class="list-group list-group-flush rounded-3 overflow-hidden">
                        @foreach(Auth::user()->playlists as $playlist)
                            <form action="{{ route('playlists.update', $playlist->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="song_id" value="{{ $song->id }}">
                                <button type="submit" class="list-group-item list-group-item-action bg-dark-900 border-dark-700 d-flex justify-content-between align-items-center p-3">
                                    <span class="fw-medium text-white">{{ $playlist->name }}</span>
                                    @if($playlist->songs->contains($song->id))
                                        <span class="badge bg-success"><i class="bi bi-check"></i> Sudah Ada</span>
                                    @else
                                        <span class="badge bg-dark-700"><i class="bi bi-plus"></i> Tambah</span>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                    <div class="mt-4 pt-3 border-top border-dark-700">
                        <p class="text-dark-300 mb-2 small">Atau buat playlist baru:</p>
                        <form action="{{ route('playlists.store') }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="name" class="form-control form-control-dark" placeholder="Nama Playlist Baru..." required>
                                <button class="btn btn-accent">Buat</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endauth
@endsection
