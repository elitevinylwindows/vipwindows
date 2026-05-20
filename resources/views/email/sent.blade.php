@extends('layouts.app')
@section('title', 'Email')

@push('styles')
<style>
    .eml-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Left Rail ── */
    .eml-rail {
        width: 340px; min-width: 340px;
        background: #fff; border-right: 1px solid rgba(0,0,0,.08);
        display: flex; flex-direction: column;
    }
    .eml-rail-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,.06);
        display: flex; align-items: center; justify-content: space-between;
    }
    .eml-rail-header h6 { margin: 0; font-size: .85rem; font-weight: 700; }
    .eml-search { padding: .5rem 1rem; border-bottom: 1px solid rgba(0,0,0,.06); }
    .eml-search input {
        width: 100%; padding: .4rem .75rem; font-size: .82rem;
        border: 1px solid rgba(0,0,0,.1); border-radius: .375rem; background: #fafaf7;
    }
    .eml-search input:focus { outline: none; border-color: var(--vip-accent); }
    .eml-list { flex: 1; overflow-y: auto; }
    .eml-card {
        display: flex; align-items: flex-start; gap: .75rem;
        padding: .75rem 1.25rem; cursor: pointer;
        border-bottom: 1px solid rgba(0,0,0,.04); transition: background .1s;
    }
    .eml-card:hover { background: rgba(201,168,76,.04); }
    .eml-card.active { background: rgba(201,168,76,.1); border-left: 3px solid var(--vip-accent); }
    .eml-card-icon {
        width: 36px; height: 36px; border-radius: 50%;
        background: linear-gradient(135deg, var(--vip-accent), #a0832a);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: .8rem; flex-shrink: 0; margin-top: 2px;
    }
    .eml-card-info { flex: 1; min-width: 0; }
    .eml-card-to { font-weight: 600; font-size: .82rem; color: #111; }
    .eml-card-subj {
        font-size: .78rem; color: #555;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px;
    }
    .eml-card-preview {
        font-size: .7rem; color: #aaa;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;
    }
    .eml-card-time { font-size: .6rem; color: #bbb; flex-shrink: 0; margin-top: 3px; }

    /* ── Detail Panel ── */
    .eml-main { flex: 1; overflow-y: auto; background: var(--vip-light); }
    .eml-toolbar {
        background: #fff; border-bottom: 1px solid rgba(0,0,0,.06);
        padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
    }
    .eml-toolbar h5 { font-size: 1rem; font-weight: 700; margin: 0; }
    .eml-detail-body { padding: 1.5rem; }
    .eml-empty-state {
        display: flex; flex-direction: column; align-items: center;
        justify-content: center; height: 60vh; color: rgba(0,0,0,.35);
    }
    .eml-empty-state i { font-size: 3rem; margin-bottom: 1rem; }
    .eml-header-card {
        background: #fff; border-radius: .5rem; padding: 1rem 1.25rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 1rem;
    }
    .eml-body-card {
        background: #fff; border-radius: .5rem; padding: 1.5rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.06); font-size: .9rem; line-height: 1.7;
    }
    .eml-meta-row { display: flex; align-items: center; gap: .5rem; margin-bottom: .35rem; font-size: .82rem; }
    .eml-meta-label { font-weight: 600; min-width: 60px; color: #555; }
    .eml-meta-val { color: #333; }

    .eml-rail-footer { padding: .75rem 1rem; border-top: 1px solid rgba(0,0,0,.06); text-align: center; }

    @media (max-width: 991.98px) {
        .eml-container { flex-direction: column; height: auto; }
        .eml-rail { width: 100%; min-width: 100%; max-height: 40vh; }
    }
</style>
@endpush

@section('content')
<div class="eml-container">
    {{-- Left Rail --}}
    <div class="eml-rail">
        <div class="eml-rail-header">
            <h6><i class="bi bi-envelope me-1"></i> Sent Emails</h6>
            <button class="btn btn-sm btn-vip" data-bs-toggle="modal" data-bs-target="#composeModal">
                <i class="bi bi-pencil-square me-1"></i>Compose
            </button>
        </div>
        <div class="eml-search">
            <input type="text" id="emlSearch" placeholder="Search emails...">
        </div>
        <div class="eml-list" id="emlList">
            @forelse($emails as $email)
                <div class="eml-card" data-id="{{ $email->id }}"
                     data-search="{{ strtolower($email->to . ' ' . $email->subject . ' ' . ($email->cc ?? '')) }}">
                    <div class="eml-card-icon"><i class="bi bi-send"></i></div>
                    <div class="eml-card-info">
                        <div class="eml-card-to">{{ $email->to }}</div>
                        <div class="eml-card-subj">{{ $email->subject }}</div>
                        <div class="eml-card-preview">{{ Str::limit(strip_tags($email->body), 50) }}</div>
                    </div>
                    <div class="eml-card-time">{{ $email->created_at->diffForHumans(null, true) }}</div>
                </div>
            @empty
                <div class="text-center py-5" style="color:rgba(0,0,0,.3);">
                    <i class="bi bi-envelope" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0 small">No emails sent yet</p>
                    <p class="small text-muted">Click "Compose" to send your first email</p>
                </div>
            @endforelse
        </div>
        @if($emails->hasPages())
            <div class="eml-rail-footer">{{ $emails->links() }}</div>
        @endif
    </div>

    {{-- Detail Panel --}}
    <div class="eml-main">
        <div class="eml-toolbar">
            <h5 id="emlDetailTitle">Email</h5>
            <div id="emlToolbarActions"></div>
        </div>
        <div class="eml-detail-body" id="emlDetailBody">
            <div class="eml-empty-state">
                <i class="bi bi-envelope-open"></i>
                <p>Select an email to view details</p>
            </div>
        </div>
    </div>
</div>

{{-- Compose Email Modal --}}
<div class="modal fade" id="composeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.email.send') }}">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title mb-0"><i class="bi bi-pencil-square me-1"></i> Compose Email</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">To <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="email" name="to" class="form-control" placeholder="recipient@example.com" required>
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-lines-fill"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" style="max-height:300px; overflow-y:auto;">
                                @foreach($customers as $c)
                                    <li><a class="dropdown-item customer-pick" href="#" data-email="{{ $c->email }}">{{ $c->name }} — {{ $c->email }}</a></li>
                                @endforeach
                                @if($customers->isEmpty())
                                    <li><span class="dropdown-item text-muted">No customers yet</span></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">CC <span class="text-muted">(optional, comma-separated)</span></label>
                        <input type="text" name="cc" class="form-control" placeholder="cc1@example.com, cc2@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" placeholder="Email subject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control" rows="8" placeholder="Type your message here..."></textarea>
                        <div class="form-text">HTML formatting is supported.</div>
                    </div>
                    <div>
                        <label class="form-label text-muted small">Quick Templates</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-secondary template-btn"
                                    data-subject="Your VIP Windows Quote"
                                    data-body="Dear Customer,<br><br>Thank you for your interest in VIP Windows. Please find your quote details below.<br><br>If you have any questions, feel free to reach out to us at (562) 368-0313.<br><br>Best regards,<br>VIP Windows Team">
                                <i class="bi bi-file-text me-1"></i> Quote Follow-up
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary template-btn"
                                    data-subject="Installation Scheduling — VIP Windows"
                                    data-body="Dear Customer,<br><br>We'd like to schedule your window installation. Please let us know your preferred dates and times, and we'll do our best to accommodate.<br><br>You can also book directly through our website or call us at (562) 368-0313.<br><br>Thank you,<br>VIP Windows Team">
                                <i class="bi bi-calendar me-1"></i> Schedule Installation
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary template-btn"
                                    data-subject="Virtual Consultation Invitation — VIP Windows"
                                    data-body="Dear Customer,<br><br>We'd like to invite you to a virtual consultation where we can review your windows, take measurements, and provide a detailed estimate — all from the comfort of your home.<br><br>Please reply with your preferred date and time, and we'll send a meeting link.<br><br>Best regards,<br>VIP Windows Team">
                                <i class="bi bi-camera-video me-1"></i> Consultation Invite
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-vip"><i class="bi bi-send me-1"></i> Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Store all email data for detail rendering
const emailsData = @json($emails->items());

// Email card click
document.querySelectorAll('.eml-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.eml-card').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        loadEmailDetail(this.dataset.id);
    });
});

// Search filter
document.getElementById('emlSearch').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.eml-card').forEach(c => {
        c.style.display = (!term || c.dataset.search.includes(term)) ? '' : 'none';
    });
});

function loadEmailDetail(id) {
    const email = emailsData.find(e => e.id == id);
    if (!email) return;

    const body = document.getElementById('emlDetailBody');
    const title = document.getElementById('emlDetailTitle');
    const toolbar = document.getElementById('emlToolbarActions');

    title.textContent = email.subject || '(No Subject)';

    toolbar.innerHTML = `
        <button class="btn btn-sm btn-vip" onclick="replyToEmail('${escHtml(email.to)}')" title="Reply">
            <i class="bi bi-reply me-1"></i>Reply
        </button>
    `;

    const sentDate = new Date(email.created_at).toLocaleString('en-US', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
        hour: 'numeric', minute: '2-digit', hour12: true
    });

    body.innerHTML = `
        <div class="eml-header-card">
            <div class="eml-meta-row">
                <span class="eml-meta-label">To:</span>
                <span class="eml-meta-val">${escHtml(email.to)}</span>
            </div>
            ${email.cc ? '<div class="eml-meta-row"><span class="eml-meta-label">CC:</span><span class="eml-meta-val">' + escHtml(email.cc) + '</span></div>' : ''}
            <div class="eml-meta-row">
                <span class="eml-meta-label">Subject:</span>
                <span class="eml-meta-val fw-semibold">${escHtml(email.subject)}</span>
            </div>
            <div class="eml-meta-row">
                <span class="eml-meta-label">Sent:</span>
                <span class="eml-meta-val" style="color:#888;">${sentDate}</span>
            </div>
        </div>
        <div class="eml-body-card">
            ${email.body}
        </div>
    `;
}

function replyToEmail(toAddress) {
    const input = document.querySelector('#composeModal input[name="to"]');
    if (input) input.value = toAddress;
    new bootstrap.Modal(document.getElementById('composeModal')).show();
}

function escHtml(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// Customer pick in compose modal
document.querySelectorAll('.customer-pick').forEach(el => {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('#composeModal input[name="to"]').value = this.dataset.email;
    });
});

// Template buttons
document.querySelectorAll('.template-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelector('#composeModal input[name="subject"]').value = this.dataset.subject;
        document.querySelector('#composeModal textarea[name="body"]').value = this.dataset.body;
    });
});

// Auto-open compose if ?to= param
@if(request('to'))
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('#composeModal input[name="to"]').value = '{{ request("to") }}';
        new bootstrap.Modal(document.getElementById('composeModal')).show();
    });
@endif
</script>
@endpush
