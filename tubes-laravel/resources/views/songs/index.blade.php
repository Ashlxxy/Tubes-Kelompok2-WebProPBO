@extends('layouts.app')

@section('title', 'Daftar Lagu — UKM Band')

@section('content')
<div class="container-xxl py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Semua Lagu</h3>
        <form action="{{ route('songs.index') }}" method="GET" class="d-flex gap-2">
            <input type="text" name="q" class="form-control form-control-dark form-control-sm" placeholder="Cari..." value="{{ request('q') }}">
            <button type="submit" class="btn btn-sm btn-outline-accent"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <div class="row g-3">
        @forelse($songs as $song)
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 fade-in">
            <div class="card song p-2 h-100 hover-scale" onclick="window.location.href='{{ route('songs.show', $song->id) }}'">
                <img src="{{ asset($song->cover_path) }}" class="cover w-100 mb-2 rounded" alt="{{ $song->title }}">
                <div class="d-flex flex-column">
                    <div class="fw-semibold text-truncate">{{ $song->title }}</div>
                    <div class="small text-dark-300 text-truncate">{{ $song->artist }}</div>
                    <div class="mt-2 d-flex align-items-center gap-2">
                        <span class="badge badge-soft"><i class="bi bi-play-fill"></i> {{ $song->plays }}</span>
                        @auth
                            <span class="badge {{ Auth::user()->likedSongs->contains($song->id) ? 'bg-accent' : 'bg-accent-soft' }}">
                                <i class="bi {{ Auth::user()->likedSongs->contains($song->id) ? 'bi-heart-fill' : 'bi-heart' }}"></i> {{ $song->likes }}
                            </span>
                        @else
                            <span class="badge bg-accent-soft"><i class="bi bi-heart-fill"></i> {{ $song->likes }}</span>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center text-dark-300 py-5">
            Tidak ada lagu ditemukan.
        </div>
        @endforelse
    </div>
</div>
@endsection
