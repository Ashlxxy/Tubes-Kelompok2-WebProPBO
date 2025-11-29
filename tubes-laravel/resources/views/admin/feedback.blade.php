@extends('layouts.app')

@section('title', 'Feedback Masuk — UKM Band')

@section('content')
<div class="container-xxl py-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Feedback Masuk</h3>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="list-group list-group-flush rounded-4 overflow-hidden">
        @forelse($feedbacks as $feedback)
        <div class="list-group-item list-group-item-dark p-4">
            <div class="d-flex justify-content-between mb-2">
                <h5 class="mb-0">{{ $feedback->name }}</h5>
                <small class="text-dark-300">{{ $feedback->created_at->format('d M Y, H:i') }}</small>
            </div>
            <div class="small text-accent mb-2">{{ $feedback->email }}</div>
            <p class="mb-0 text-dark-200">{{ $feedback->message }}</p>
        </div>
        @empty
        <div class="text-center text-dark-300 py-5">Belum ada feedback masuk.</div>
        @endforelse
    </div>
</div>
@endsection
