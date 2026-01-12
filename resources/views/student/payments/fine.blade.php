@extends('layouts.student')

@section('title', 'Pay Fine Online')
@section('page-title', 'Pay Fine Online')

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endpush

@section('content')
<div class="card-modern">
    <div class="card-header-modern">
        <h5>
            <i class="fas fa-credit-card text-primary"></i>
            Pay Fine Online
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Payment Gateway:</strong> Razorpay (Secure Payment)
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="mb-3">Fine Details</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Book:</strong></td>
                                <td>{{ $fine->borrow->book->title ?? 'N/A' }}</td>
                            </tr>
                            @if($totalPaid > 0)
                            <tr>
                                <td><strong>Already Paid:</strong></td>
                                <td><span class="text-success">₹{{ number_format($totalPaid, 2) }}</span>
                                    @if($lastPaidDate)
                                        <br><small class="text-muted">(Paid till: {{ $lastPaidDate->format('M d, Y') }})</small>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Pending Fine:</strong></td>
                                <td><span class="text-danger fs-5">₹{{ number_format($remainingAmount, 2) }}</span>
                                    @if($pendingDays > 0)
                                        <br><small class="text-muted">({{ $pendingDays }} day(s) pending)</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Fine Rate:</strong></td>
                                <td>₹{{ number_format($finePerDay, 2) }} per day</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Payment Options -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="mb-3">Select Payment Option</h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Pay for how many days?</label>
                            <div class="btn-group w-100" role="group" id="daysSelector">
                                @for($i = 1; $i <= min($pendingDays, 10); $i++)
                                    @php
                                        $dayAmount = $i * $finePerDay;
                                        $isDisabled = $dayAmount > $remainingAmount;
                                    @endphp
                                    <input type="radio" class="btn-check" name="payment_days" id="day{{ $i }}" value="{{ $i }}" 
                                           data-amount="{{ $dayAmount }}" 
                                           {{ $i == 1 ? 'checked' : '' }}
                                           {{ $isDisabled ? 'disabled' : '' }}>
                                    <label class="btn btn-outline-primary" for="day{{ $i }}">
                                        {{ $i }} Day{{ $i > 1 ? 's' : '' }}<br>
                                        <small>₹{{ number_format($dayAmount, 2) }}</small>
                                    </label>
                                @endfor
                                
                                @if($pendingDays > 10 || $remainingAmount > ($pendingDays * $finePerDay))
                                    <input type="radio" class="btn-check" name="payment_days" id="dayFull" value="full" 
                                           data-amount="{{ $remainingAmount }}" 
                                           {{ $pendingDays <= 10 ? 'checked' : '' }}>
                                    <label class="btn btn-outline-success" for="dayFull">
                                        Full Amount<br>
                                        <small>₹{{ number_format($remainingAmount, 2) }}</small>
                                    </label>
                                @endif
                            </div>
                        </div>
                        
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Selected Amount:</strong> 
                            <span id="selectedAmount" class="fs-5 fw-bold text-primary">₹{{ number_format($finePerDay, 2) }}</span>
                            <span id="selectedDays" class="text-muted">(1 day)</span>
                        </div>
                    </div>
                </div>

                <form id="paymentForm">
                    @csrf
                    <input type="hidden" name="payment_type" value="fine">
                    <input type="hidden" name="paymentable_type" value="App\Models\Fine">
                    <input type="hidden" name="paymentable_id" value="{{ $fine->id }}">
                    <input type="hidden" name="amount" id="paymentAmount" value="{{ $finePerDay }}">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" id="payButton">
                            <i class="fas fa-credit-card me-2"></i>
                            Pay <span id="payButtonAmount">₹{{ number_format($finePerDay, 2) }}</span> Now
                        </button>
                        <a href="{{ route('student.fines.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Fines
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const finePerDay = {{ $finePerDay }};
const remainingAmount = {{ $remainingAmount }};
const pendingDays = {{ $pendingDays }};

// Update payment amount when days are selected
document.querySelectorAll('input[name="payment_days"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const selectedAmount = parseFloat(this.dataset.amount);
        const selectedDays = this.value === 'full' ? 'Full Amount' : (this.value + ' day' + (this.value > 1 ? 's' : ''));
        
        // Update hidden input
        document.getElementById('paymentAmount').value = selectedAmount;
        
        // Update display
        document.getElementById('selectedAmount').textContent = '₹' + selectedAmount.toFixed(2);
        document.getElementById('selectedDays').textContent = '(' + selectedDays + ')';
        
        // Update button
        document.getElementById('payButtonAmount').textContent = '₹' + selectedAmount.toFixed(2);
    });
});

// Initialize with first option
const firstOption = document.querySelector('input[name="payment_days"]:checked');
if (firstOption) {
    const selectedAmount = parseFloat(firstOption.dataset.amount);
    const selectedDays = firstOption.value === 'full' ? 'Full Amount' : (firstOption.value + ' day' + (firstOption.value > 1 ? 's' : ''));
    document.getElementById('selectedAmount').textContent = '₹' + selectedAmount.toFixed(2);
    document.getElementById('selectedDays').textContent = '(' + selectedDays + ')';
    document.getElementById('payButtonAmount').textContent = '₹' + selectedAmount.toFixed(2);
}

document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const button = document.getElementById('payButton');
    const selectedAmount = parseFloat(document.getElementById('paymentAmount').value);
    const buttonText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    
    try {
        const formData = new FormData(this);
        formData.set('amount', selectedAmount);
        
        const response = await fetch('{{ route("student.payments.create-order") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const options = {
                key: data.key,
                amount: data.amount,
                currency: data.currency,
                name: data.name,
                description: data.description + ' - ' + selectedAmount + ' INR',
                order_id: data.order_id,
                prefill: data.prefill,
                handler: function(response) {
                    // Submit success callback
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("student.payments.success") }}';
                    
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);
                    
                    const paymentId = document.createElement('input');
                    paymentId.type = 'hidden';
                    paymentId.name = 'payment_id';
                    paymentId.value = '{{ $fine->id }}';
                    form.appendChild(paymentId);
                    
                    const orderId = document.createElement('input');
                    orderId.type = 'hidden';
                    orderId.name = 'order_id';
                    orderId.value = data.order_id;
                    form.appendChild(orderId);
                    
                    const razorpayPaymentId = document.createElement('input');
                    razorpayPaymentId.type = 'hidden';
                    razorpayPaymentId.name = 'razorpay_payment_id';
                    razorpayPaymentId.value = response.razorpay_payment_id;
                    form.appendChild(razorpayPaymentId);
                    
                    const signature = document.createElement('input');
                    signature.type = 'hidden';
                    signature.name = 'razorpay_signature';
                    signature.value = response.razorpay_signature;
                    form.appendChild(signature);
                    
                    document.body.appendChild(form);
                    form.submit();
                },
                modal: {
                    ondismiss: function() {
                        button.disabled = false;
                        button.innerHTML = buttonText;
                    }
                }
            };
            
            const razorpay = new Razorpay(options);
            razorpay.open();
        } else {
            alert('Error: ' + (data.message || 'Failed to create payment order'));
            button.disabled = false;
            button.innerHTML = buttonText;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        button.disabled = false;
        button.innerHTML = buttonText;
    }
});
</script>
@endsection

