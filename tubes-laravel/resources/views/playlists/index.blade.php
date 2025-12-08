@extends('layouts.app')

@section('title', 'Playlist Saya — UKM Band')

@section('content')
<div class="container-xxl py-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Playlist Saya</h3>
        <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#createPlaylistModal"><i class="bi bi-plus-lg"></i> Buat Playlist</button>
    </div>

    <div class="row g-4">
        @forelse($playlists as $playlist)
        <div class="col-12">
            <div class="card-dark p-4 rounded-4 border-dark-700">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">{{ $playlist->name }}</h4>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editPlaylistModal{{ $playlist->id }}"><i class="bi bi-pencil"></i></button>
                        <form action="{{ route('playlists.destroy', $playlist->id) }}" method="POST" onsubmit="return confirm('Anda yakin menghapus Playlist ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
                <div class="row g-3">
                    @forelse($playlist->songs as $song)
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="card song p-2 h-100 position-relative group-hover">
                            <img src="{{ asset($song->cover_path) }}" class="cover w-100 mb-2 rounded" alt="{{ $song->title }}">
                            <div class="fw-semibold text-truncate">{{ $song->title }}</div>
                            <div class="small text-dark-300 text-truncate">{{ $song->artist }}</div>
                            
                            <form action="{{ route('playlists.update', $playlist->id) }}" method="POST" class="position-absolute top-0 end-0 m-1">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="remove_song_id" value="{{ $song->id }}">
                                <button class="btn btn-sm btn-danger rounded-circle p-1" style="width:24px;height:24px;line-height:1;"><i class="bi bi-x"></i></button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-dark-300">Playlist ini masih kosong.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editPlaylistModal{{ $playlist->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content card-dark">
                    <div class="modal-header border-dark-700">
                        <h5 class="modal-title">Ubah Nama Playlist</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('playlists.update', $playlist->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <input type="text" name="name" class="form-control form-control-dark" value="{{ $playlist->name }}" required>
                        </div>
                        <div class="modal-footer border-dark-700">
                            <button type="submit" class="btn btn-accent">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @empty
        <div class="col-12 text-center text-dark-300 py-5">
            Belum ada playlist, buat playlist sekarang!
        </div>
        @endforelse
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createPlaylistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-dark">
            <div class="modal-header border-dark-700">
                <h5 class="modal-title">Buat Playlist Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('playlists.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="text" name="name" class="form-control form-control-dark" placeholder="Nama Playlist" required>
                </div>
                <div class="modal-footer border-dark-700">
                    <button type="submit" class="btn btn-accent">Buat</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
