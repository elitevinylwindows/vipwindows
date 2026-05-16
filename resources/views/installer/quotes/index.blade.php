@extends('layouts.installer')
@section('title', 'My Quotes')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-calculator me-2"></i>My Quotes</h4>
        <a href="{{ route('admin.quotes.create') }}" class="btn btn-vip">
            <i class="bi bi-plus-circle me-1"></i> New Quote
        </a>
    </div>

    {{-- Status filter --}}
    <div class="mb-4">
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('installer.quotes.index') }}" class="btn {{ $status === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">All</a>
            <a href="{{ route('installer.quotes.index', ['status' => 'draft']) }}" class="btn {{ $status === 'draft' ? 'btn-dark' : 'btn-outline-dark' }}">Draft</a>
            <a href="{{ route('installer.quotes.index', ['status' => 'sent']) }}" class="btn {{ $status === 'sent' ? 'btn-dark' : 'btn-outline-dark' }}">Sent</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($quotes->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calculator fs-1 d-block mb-2"></i>
                    No quotes yet. Create your first one.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Quote #</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotes as $quote)
                                <tr>
                                    <td class="fw-semibold">{{ $quote->quote_number }}</td>
                                    <td>{{ $quote->billing_name ?: $quote->customer_number ?: '—' }}</td>
                                    <td>{{ $quote->items->count() }}</td>
                                    <td>
                                        @if($quote->status === 'draft')
                                            <span class="badge bg-secondary">Draft</span>
                                        @elseif($quote->status === 'sent')
                                            <span class="badge bg-success">Sent</span>
                                        @else
                                            <span class="badge bg-info">{{ ucfirst($quote->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $quote->created_at?->format('M d, Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.quotes.edit', $quote->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#sendQuote{{ $quote->id }}" title="Send to Customer">
                                            <i class="bi bi-send"></i>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Send modal --}}
                                <div class="modal fade" id="sendQuote{{ $quote->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Send Quote #{{ $quote->quote_number }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Customer Email</label>
                                                    <input type="email" class="form-control send-email-input" placeholder="customer@email.com" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-vip send-quote-btn" data-quote-id="{{ $quote->id }}">
                                                    <i class="bi bi-send me-1"></i> Send Quote
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $quotes->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.send-quote-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const modal = this.closest('.modal');
        const email = modal.querySelector('.send-email-input').value;
        if (!email) return;

        const quoteId = this.dataset.quoteId;
        fetch(`/admin/quotes/${quoteId}/send`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ email })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(modal).hide();
                location.reload();
            } else {
                alert(data.message || 'Failed to send');
            }
        });
    });
});
</script>
@endpush
@endsection
