@extends('layouts.installer')
@section('title', 'New Quote')

@push('styles')
<style>
    .iq-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    .iq-rail {
        width: 320px; min-width: 320px;
        background: var(--vip-primary);
        color: #fff;
        display: flex; flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.06);
    }
    .iq-rail-header { padding: 1.25rem 1rem .75rem; }
    .iq-rail-header h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,.5); margin-bottom: .75rem; }
    .iq-rail-search { display: flex; gap: .5rem; }
    .iq-rail-search input {
        flex: 1; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
        color: #fff; border-radius: .375rem; padding: .4rem .75rem; font-size: .85rem;
    }
    .iq-rail-search input::placeholder { color: rgba(255,255,255,.4); }
    .iq-rail-search input:focus { outline: none; border-color: var(--vip-accent); }

    .iq-rail-tabs { display: flex; gap: 0; padding: 0 1rem; margin-top: .75rem; }
    .iq-rail-tabs .tab-btn {
        flex: 1; text-align: center; padding: .4rem .5rem; font-size: .75rem;
        background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
        color: rgba(255,255,255,.6); cursor: pointer; transition: all .15s;
    }
    .iq-rail-tabs .tab-btn:first-child { border-radius: .3rem 0 0 .3rem; }
    .iq-rail-tabs .tab-btn:last-child { border-radius: 0 .3rem .3rem 0; }
    .iq-rail-tabs .tab-btn.active { background: var(--vip-accent); color: #fff; border-color: var(--vip-accent); }

    .iq-rail-list { flex: 1; overflow-y: auto; padding: .5rem; }
    .iq-card {
        background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
        border-radius: .5rem; padding: .75rem 1rem; margin-bottom: .5rem;
        cursor: pointer; transition: all .15s;
    }
    .iq-card:hover { background: rgba(255,255,255,.08); border-color: rgba(201,168,76,.3); }
    .iq-card.active { background: rgba(201,168,76,.12); border-color: var(--vip-accent); }
    .iq-card .q-number { font-weight: 600; font-size: .9rem; color: #fff; }
    .iq-card .q-customer { font-size: .78rem; color: rgba(255,255,255,.55); margin-top: 2px; }
    .iq-card .q-meta { display: flex; justify-content: space-between; align-items: center; margin-top: .35rem; }
    .iq-card .q-date { font-size: .7rem; color: rgba(255,255,255,.4); }
    .iq-card .q-badge { font-size: .6rem; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; }
    .q-badge-draft { background: rgba(108,117,125,.25); color: #adb5bd; }
    .q-badge-sent { background: rgba(40,167,69,.25); color: #7ddf9b; }

    .iq-rail-footer {
        padding: .75rem 1rem; border-top: 1px solid rgba(255,255,255,.08);
        font-size: .75rem; color: rgba(255,255,255,.4);
        display: flex; justify-content: space-between;
    }

    /* ── Main Panel ────────────────────────────── */
    .iq-main { flex: 1; overflow-y: auto; background: var(--vip-light); }
    .iq-main-toolbar {
        background: #fff; border-bottom: 1px solid rgba(0,0,0,.06);
        padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
    }
    .iq-main-toolbar h5 { font-size: 1rem; font-weight: 700; margin: 0; }
    .iq-detail-body { padding: 1.5rem; }

    .form-card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.08); border-radius: .5rem; }
    .form-card .card-header { background: #fff; border-bottom: 1px solid rgba(0,0,0,.06); font-weight: 600; }

    @media (max-width: 991.98px) {
        .iq-container { flex-direction: column; height: auto; }
        .iq-rail { width: 100%; min-width: 100%; max-height: 45vh; }
    }
</style>
@endpush

@section('content')
<div class="iq-container">
    {{-- Left Rail (existing quotes) --}}
    <div class="iq-rail">
        <div class="iq-rail-header">
            <h6>My Quotes</h6>
            <div class="iq-rail-search">
                <input type="text" id="iqSearch" placeholder="Search quotes...">
                <a href="{{ route('installer.quotes.create') }}" class="btn btn-sm btn-vip" title="New Quote">
                    <i class="bi bi-plus-lg"></i>
                </a>
            </div>
        </div>

        <div class="iq-rail-list">
            @forelse($quotes ?? [] as $quote)
                <a href="{{ route('installer.quotes.index') }}?select={{ $quote->id }}" class="iq-card text-decoration-none" style="display:block;">
                    <div class="q-number">{{ $quote->quote_number }}</div>
                    <div class="q-customer"><i class="bi bi-person me-1"></i>{{ $quote->billing_name ?: 'No customer' }}</div>
                    <div class="q-meta">
                        <span class="q-date">{{ $quote->created_at?->format('M d, Y') }}</span>
                        <span class="q-badge {{ $quote->status === 'sent' ? 'q-badge-sent' : 'q-badge-draft' }}">{{ ucfirst($quote->status) }}</span>
                    </div>
                </a>
            @empty
                <div class="text-center py-4" style="color:rgba(255,255,255,.4);">
                    <i class="bi bi-calculator" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0">No quotes yet</p>
                </div>
            @endforelse
        </div>

        <div class="iq-rail-footer">
            <span>{{ isset($quotes) ? $quotes->count() : 0 }} quote(s)</span>
            <span>Creating new</span>
        </div>
    </div>

    {{-- Main Panel: New Quote Form --}}
    <div class="iq-main">
        <div class="iq-main-toolbar">
            <h5><i class="bi bi-plus-circle me-2"></i>New Quote</h5>
            <a href="{{ route('installer.quotes.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
        <div class="iq-detail-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('installer.quotes.store') }}">
                @csrf
                <div class="row g-4">
                    {{-- Customer Info --}}
                    <div class="col-lg-8">
                        <div class="card form-card">
                            <div class="card-header py-3"><i class="bi bi-person me-2"></i>Customer Information</div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                        <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required list="customerList">
                                        <datalist id="customerList">
                                            @foreach($customers as $c)
                                                <option value="{{ $c->name }}">{{ $c->email }}</option>
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Address</label>
                                        <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">State</label>
                                        <input type="text" name="state" class="form-control" value="{{ old('state') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">ZIP</label>
                                        <input type="text" name="zip" class="form-control" value="{{ old('zip') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quote Settings --}}
                    <div class="col-lg-4">
                        <div class="card form-card">
                            <div class="card-header py-3"><i class="bi bi-gear me-2"></i>Settings</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Valid For (days)</label>
                                    <input type="number" name="valid_days" class="form-control" value="{{ old('valid_days', 30) }}" min="1" max="365">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="4" placeholder="Internal notes...">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-vip w-100 py-2 fw-semibold mt-3">
                            <i class="bi bi-check-circle me-1"></i> Create Quote
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('iqSearch').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.iq-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = (!term || text.includes(term)) ? '' : 'none';
    });
});
</script>
@endpush
