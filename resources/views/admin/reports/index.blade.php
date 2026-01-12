@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')
<div class="row g-3">
    <!-- Total Books Report -->
    <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
        <a href="{{ route('admin.reports.total-books') }}" class="text-decoration-none">
            <div class="card-modern h-100 border-start border-primary border-4 hover-shadow">
                <div class="card-body p-4">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h5 class="fw-bold text-dark mb-2">Total Books Report</h5>
                            <p class="text-muted small mb-0">View all books with details</p>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-book text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Book Issue Report -->
    <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
        <a href="{{ route('admin.reports.book-issue') }}" class="text-decoration-none">
            <div class="card-modern h-100 border-start border-success border-4 hover-shadow">
                <div class="card-body p-4">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h5 class="fw-bold text-dark mb-2">Book Issue Report</h5>
                            <p class="text-muted small mb-0">View all book issues and returns</p>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-exchange-alt text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Overdue Report -->
    <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
        <a href="{{ route('admin.reports.overdue') }}" class="text-decoration-none">
            <div class="card-modern h-100 border-start border-danger border-4 hover-shadow">
                <div class="card-body p-4">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h5 class="fw-bold text-dark mb-2">Overdue Report</h5>
                            <p class="text-muted small mb-0">View all overdue books</p>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Fine Report -->
    <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
        <a href="{{ route('admin.reports.fines') }}" class="text-decoration-none">
            <div class="card-modern h-100 border-start border-warning border-4 hover-shadow">
                <div class="card-body p-4">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h5 class="fw-bold text-dark mb-2">Fine Report</h5>
                            <p class="text-muted small mb-0">View all fines and payments</p>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-dollar-sign text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Student-wise Report -->
    <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
        <a href="{{ route('admin.reports.student-wise') }}" class="text-decoration-none">
            <div class="card-modern h-100 border-start border-info border-4 hover-shadow">
                <div class="card-body p-4">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h5 class="fw-bold text-dark mb-2">Student-wise Report</h5>
                            <p class="text-muted small mb-0">View student borrowing history</p>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-users text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection

@push('styles')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15) !important;
    }
</style>
@endpush
