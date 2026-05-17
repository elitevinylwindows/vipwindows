@extends('layouts.installer')
@section('title', 'My Customers')

@push('styles')
<style>
    .ic-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    .ic-rail {
        width: 320px; min-width: 320px;
        background: var(--vip-primary); color: #fff;
        display: flex; flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.06);
    }
    .ic-rail-header { padding: 1.25rem 1rem .75rem; }
    .ic-rail-header h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,.5); margin-bottom: .75rem; }
    .ic-rail-search { display: flex; gap: .5rem; }
    .ic-rail-search input {
        flex: 1; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
        color: #fff; border-radius: .375rem; padding: .4rem .75rem; font-size: .85rem;
    }
    .ic-rail-search input::placeholder { color: rgba(255,255,255,.4); }
    .ic-rail-search input:focus { outline: none; border-color: var(--vip-accent); }

    .ic-rail-tabs { display: flex; gap: 0; padding: 0 1rem; margin-top: .75rem; }
    .ic-rail-tabs .tab-btn {
        flex: 1; text-align: center; padding: .4rem .5rem; font-size: .75rem;
        background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
        color: rgba(255,255,255,.6); cursor: pointer; transition: all .15s;
    }
    .ic-rail-tabs .tab-btn:first-child { border-radius: .3rem 0 0 .3rem; }
    .ic-rail-tabs .tab-btn:last-child { border-radius: 0 .3rem .3rem 0; }
    .ic-rail-tabs .tab-btn.active { background: var(--vip-accent); color: #fff; border-color: var(--vip-accent); }

    .ic-rail-list { flex: 1; overflow-y: auto; padding: .5rem; }
    .ic-card {
        background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
        border-radius: .5rem; padding: .75rem 1rem; margin-bottom: .5rem;
        cursor: pointer; transition: all .15s;
    }
    .ic-card:hover { background: rgba(255,255,255,.08); border-color: rgba(201,168,76,.3); }
    .ic-card.active { background: rgba(201,168,76,.12); border-color: var(--vip-accent); }
    .ic-card .c-name { font-weight: 600; font-size: .9rem; color: #fff; }
    .ic-card .c-info { font-size: .75rem; color: rgba(255,255,255,.5); margin-top: 2px; }
    .ic-card .c-type { font-size: .6rem; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; background: rgba(201,168,76,.2); color: var(--vip-accent); }

    .ic-rail-footer {
        padding: .75rem 1rem; border-top: 1px solid rgba(255,255,255,.08);
        font-size: .75rem; color: rgba(255,255,255,.4);
        display: flex; justify-content: space-between;
    }

    .ic-main { flex: 1; overflow-y: auto; background: var(--vip-light); }
    .ic-main-toolbar {
        background: #fff; border-bottom: 1px solid rgba(0,0,0,.06);
        padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
    }
    .ic-main-toolbar h5 { font-size: 1rem; font-weight: 700; margin: 0; }
    .ic-detail-body { padding: 1.5rem; }

    .ic-empty-state {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 60vh; color: rgba(0,0,0,.35);
    }
    .ic-empty-state i { font-size: 3rem; margin-bottom: 1rem; }

    .ic-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .ic-info-card { background: #fff; border-radius: .5rem; padding: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .ic-info-card .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.45); margin-bottom: .25rem; }
    .ic-info-card .value { font-size: .9rem; font-weight: 600; color: #111; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ic-info-card .value.wrap { white-space: normal; overflow: visible; text-overflow: unset; }

    @media (max-width: 991.98px) {
        .ic-container { flex-direction: column; height: auto; }
        .ic-rail { width: 100%; min-width: 100%; max-height: 45vh; }
    }
</style>
@endpush

@section('content')
<div class="ic-container">
    {{-- Left Rail --}}
    <div class="ic-rail">
        <div class="ic-rail-header">
            <h6>My Customers</h6>
            <div class="ic-rail-search">
                <input type="text" id="icSearch" placeholder="Search customers...">
                <button class="btn btn-sm btn-vip" data-bs-toggle="modal" data-bs-target="#addCustomerModal" title="Add Customer">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
            <div class="ic-rail-tabs">
                <div class="tab-btn {{ !request('type') ? 'active' : '' }}" data-type="">All</div>
                <div class="tab-btn {{ request('type') === 'homeowner' ? 'active' : '' }}" data-type="homeowner">Homeowner</div>
                <div class="tab-btn {{ request('type') === 'business' ? 'active' : '' }}" data-type="business">Business</div>
            </div>
        </div>

        <div class="ic-rail-list">
            @forelse($customers as $customer)
                <div class="ic-card" data-id="{{ $customer->id }}" data-search="{{ strtolower($customer->name . ' ' . $customer->email . ' ' . ($customer->phone ?? '') . ' ' . ($customer->city ?? '')) }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="c-name">{{ $customer->name }}</div>
                        <span class="c-type">{{ $customer->customer_type ?? 'homeowner' }}</span>
                    </div>
                    <div class="c-info"><i class="bi bi-envelope me-1"></i>{{ $customer->email }}</div>
                    @if($customer->phone)
                        <div class="c-info"><i class="bi bi-telephone me-1"></i>{{ $customer->phone }}</div>
                    @endif
                </div>
            @empty
                <div class="text-center py-4" style="color:rgba(255,255,255,.4);">
                    <i class="bi bi-people" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0">No customers yet</p>
                </div>
            @endforelse
        </div>

        <div class="ic-rail-footer">
            <span>{{ $customers->total() }} customer{{ $customers->total() !== 1 ? 's' : '' }}</span>
        </div>
    </div>

    {{-- Main Panel --}}
    <div class="ic-main">
        <div class="ic-main-toolbar">
            <h5 id="icDetailTitle">Customer Details</h5>
            <div id="icToolbarActions"></div>
        </div>
        <div class="ic-detail-body" id="icDetailBody">
            <div class="ic-empty-state">
                <i class="bi bi-people"></i>
                <p>Select a customer to view details</p>
            </div>
        </div>
    </div>
</div>

{{-- Add Customer Modal --}}
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('installer.customers.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-person-plus me-1"></i> Add Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-8"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required></div>
                        <div class="col-4"><label class="form-label">Type</label>
                            <select name="customer_type" class="form-select">
                                <option value="homeowner">Homeowner</option>
                                <option value="business">Business</option>
                            </select>
                        </div>
                        <div class="col-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
                        <div class="col-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Address</label><input type="text" name="address" class="form-control"></div>
                        <div class="col-5"><label class="form-label">City</label><input type="text" name="city" class="form-control"></div>
                        <div class="col-4"><label class="form-label">State</label><input type="text" name="state" class="form-control" value="CA"></div>
                        <div class="col-3"><label class="form-label">ZIP</label><input type="text" name="zip" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-vip">Add Customer</button></div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Customer Modal --}}
<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editCustomerForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-pencil me-1"></i> Edit Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-8"><label class="form-label">Full Name *</label><input type="text" name="name" id="ecName" class="form-control" required></div>
                        <div class="col-4"><label class="form-label">Type</label>
                            <select name="customer_type" id="ecType" class="form-select">
                                <option value="homeowner">Homeowner</option>
                                <option value="business">Business</option>
                            </select>
                        </div>
                        <div class="col-6"><label class="form-label">Email *</label><input type="email" name="email" id="ecEmail" class="form-control" required></div>
                        <div class="col-6"><label class="form-label">Phone</label><input type="text" name="phone" id="ecPhone" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Address</label><input type="text" name="address" id="ecAddress" class="form-control"></div>
                        <div class="col-5"><label class="form-label">City</label><input type="text" name="city" id="ecCity" class="form-control"></div>
                        <div class="col-4"><label class="form-label">State</label><input type="text" name="state" id="ecState" class="form-control"></div>
                        <div class="col-3"><label class="form-label">ZIP</label><input type="text" name="zip" id="ecZip" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" id="ecNotes" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-vip">Save Changes</button></div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.ic-card');
    const detailBody = document.getElementById('icDetailBody');
    const detailTitle = document.getElementById('icDetailTitle');
    const toolbarActions = document.getElementById('icToolbarActions');
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    let activeCustomerData = null;

    // Tab filters
    document.querySelectorAll('.ic-rail-tabs .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.type;
            const url = new URL(window.location);
            if (type) url.searchParams.set('type', type);
            else url.searchParams.delete('type');
            window.location = url;
        });
    });

    // Search
    document.getElementById('icSearch').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.ic-card').forEach(card => {
            card.style.display = (!term || card.dataset.search.includes(term)) ? '' : 'none';
        });
    });

    // Load detail
    cards.forEach(card => {
        card.addEventListener('click', function() {
            cards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            loadDetail(this.dataset.id);
        });
    });

    function loadDetail(id) {
        detailBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>';

        fetch(`/installer/customers/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.json())
            .then(data => {
                const c = data.customer;
                activeCustomerData = c;
                detailTitle.textContent = c.name;

                toolbarActions.innerHTML = `
                    <button class="btn btn-sm btn-outline-primary me-2" onclick="editCustomer()"><i class="bi bi-pencil"></i> Edit</button>
                    <form method="POST" action="/installer/customers/${c.id}" style="display:inline" onsubmit="return confirm('Delete this customer?')">
                        <input type="hidden" name="_token" value="${csrf}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                `;

                const address = [c.address, c.city, c.state, c.zip].filter(Boolean).join(', ') || '—';
                const esc = s => { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; };

                detailBody.innerHTML = `
                    <div class="ic-info-grid">
                        <div class="ic-info-card"><div class="label">Email</div><div class="value" title="${esc(c.email) || ''}">${esc(c.email) || '—'}</div></div>
                        <div class="ic-info-card"><div class="label">Phone</div><div class="value">${esc(c.phone) || '—'}</div></div>
                        <div class="ic-info-card"><div class="label">Type</div><div class="value"><span class="badge" style="background:rgba(201,168,76,.15);color:#8b6914;">${(c.customer_type || 'homeowner').charAt(0).toUpperCase() + (c.customer_type || 'homeowner').slice(1)}</span></div></div>
                        <div class="ic-info-card"><div class="label">Quotes</div><div class="value" style="color:var(--vip-accent);">${data.stats?.quotes || 0}</div></div>
                        <div class="ic-info-card"><div class="label">Address</div><div class="value wrap">${esc(c.address || '')}${c.city ? '<br>' + esc(c.city) + ', ' + esc(c.state || '') + ' ' + esc(c.zip || '') : ''}</div></div>
                        <div class="ic-info-card"><div class="label">Joined</div><div class="value">${c.created_at ? new Date(c.created_at).toLocaleDateString('en-US', {year:'numeric', month:'short', day:'numeric'}) : '—'}</div></div>
                    </div>
                    ${c.notes ? `<div class="p-3 bg-white rounded border"><div style="font-size:.7rem;text-transform:uppercase;color:#888;letter-spacing:.5px;margin-bottom:4px">Notes</div><div style="font-size:.85rem">${esc(c.notes)}</div></div>` : ''}
                `;
            })
            .catch(() => {
                detailBody.innerHTML = '<div class="alert alert-danger m-4">Failed to load customer details.</div>';
            });
    }

    window.editCustomer = function() {
        if (!activeCustomerData) return;
        const c = activeCustomerData;
        document.getElementById('editCustomerForm').action = `/installer/customers/${c.id}`;
        document.getElementById('ecName').value = c.name || '';
        document.getElementById('ecEmail').value = c.email || '';
        document.getElementById('ecPhone').value = c.phone || '';
        document.getElementById('ecType').value = c.customer_type || 'homeowner';
        document.getElementById('ecAddress').value = c.address || '';
        document.getElementById('ecCity').value = c.city || '';
        document.getElementById('ecState').value = c.state || '';
        document.getElementById('ecZip').value = c.zip || '';
        document.getElementById('ecNotes').value = c.notes || '';
        new bootstrap.Modal(document.getElementById('editCustomerModal')).show();
    };

    if (cards.length > 0) cards[0].click();
});
</script>
@endpush
