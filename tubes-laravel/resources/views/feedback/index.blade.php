@extends('layouts.app')

@section('title', 'Feedback — UKM Band')

@section('content')
<div class="container d-flex justify-content-center align-items-center min-vh-100" style="margin-top: -60px;">
    <div class="row w-100 justify-content-center">
        <div class="col-lg-10">
            <h3 class="mb-4">Kirim Feedback</h3>
            <div class="row g-0 rounded-4 overflow-hidden shadow-lg">
                <!-- Form Section -->
                <div class="col-md-7 card-dark p-5">
                    <form action="{{ route('feedback.store') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-dark-200">Nama</label>
                                <input type="text" name="name" class="form-control form-control-dark" value="{{ Auth::user()->name }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-dark-200">Email</label>
                                <input type="email" name="email" class="form-control form-control-dark" value="{{ Auth::user()->email }}" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-dark-200">Pesan</label>
                            <textarea name="message" class="form-control form-control-dark" rows="5" required placeholder="Tulis masukan Anda di sini..."></textarea>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-accent px-4">Kirim</button>
                        </div>
                    </form>
                </div>

                <!-- Contact Info Section -->
                <div class="col-md-5 bg-dark-900 p-5 border-start border-dark-700 d-flex flex-column justify-content-center">
                    <h4 class="mb-4"><i class="bi bi-telephone me-2"></i>Contact Us</h4>
                    <p class="text-dark-200 mb-4">Penyedia Platform Website</p>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-envelope me-3 text-dark-300"></i>
                            <span>support@platformweb.example</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-telephone me-3 text-dark-300"></i>
                            <span>+62 812 3456 7890</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-geo-alt me-3 text-dark-300"></i>
                            <span>Jl. Contoh No. 123, Bandung</span>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-dark-800 rounded text-dark-300 small">
                        Peta/Embed provider (opsional)
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h5 class="mb-3">Feedback Terkirim (mock)</h5>
                <div class="text-dark-300">Belum ada feedback.</div>
            </div>
        </div>
    </div>
</div>
@endsection
