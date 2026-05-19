@extends('layouts.app')
@section('title', 'VIP Master')

@push('styles')
<style>
    .vm-wrapper {
        display: flex;
        height: calc(100vh - 120px);
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    /* ── Left Rail ── */
    .vm-sidebar {
        width: 240px;
        min-width: 240px;
        background: var(--vip-primary);
        color: #fff;
        display: flex;
        flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.08);
    }
    .vm-sidebar-header {
        padding: 16px 16px 12px;
        border-bottom: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.15);
    }
    .vm-sidebar-header h6 {
        margin: 0;
        font-size: .85rem;
        font-weight: 700;
        letter-spacing: .5px;
        color: var(--vip-accent);
    }
    .vm-sidebar-body {
        flex: 1;
        overflow-y: auto;
        padding: 8px 0;
    }
    .vm-nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        color: rgba(255,255,255,.7);
        text-decoration: none;
        font-size: .85rem;
        cursor: pointer;
        border-left: 3px solid transparent;
        transition: all .15s;
    }
    .vm-nav-item:hover {
        background: rgba(255,255,255,.08);
        color: #fff;
    }
    .vm-nav-item.active {
        background: rgba(201,168,76,.12);
        border-left-color: var(--vip-accent);
        color: var(--vip-accent);
        font-weight: 600;
    }
    .vm-nav-item i { width: 20px; text-align: center; font-size: .95rem; }
    .vm-nav-item .count {
        margin-left: auto;
        background: rgba(255,255,255,.12);
        border-radius: 10px;
        font-size: .65rem;
        padding: 1px 8px;
        color: rgba(255,255,255,.5);
    }

    /* ── Main Panel ── */
    .vm-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .vm-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        border-bottom: 1px solid #e9ecef;
        background: #fafafa;
        min-height: 52px;
    }
    .vm-toolbar h6 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--vip-primary);
        margin: 0;
    }
    .vm-toolbar .actions { margin-left: auto; display: flex; gap: 6px; }
    .vm-content {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
    }

    /* Empty state */
    .vm-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #999;
    }
    .vm-empty i { font-size: 3rem; color: #ddd; margin-bottom: 16px; }

    /* Table */
    .vm-table { width: 100%; border-collapse: collapse; }
    .vm-table th {
        font-size: .7rem; text-transform: uppercase; letter-spacing: .5px;
        color: rgba(0,0,0,.4); padding: .6rem .75rem;
        border-bottom: 2px solid #e9ecef; background: #fafafa;
    }
    .vm-table td {
        padding: .6rem .75rem; font-size: .85rem;
        border-bottom: 1px solid #f0f0f0; vertical-align: middle;
    }
    .vm-table tr:hover td { background: #f8f9fa; }

    /* Toggle switch */
    .toggle-switch {
        position: relative;
        width: 40px; height: 22px;
        display: inline-block;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; cursor: pointer; inset: 0;
        background: #ccc; border-radius: 22px;
        transition: .2s;
    }
    .toggle-slider:before {
        content: ''; position: absolute;
        height: 16px; width: 16px;
        left: 3px; bottom: 3px;
        background: #fff; border-radius: 50%;
        transition: .2s;
    }
    .toggle-switch input:checked + .toggle-slider { background: var(--vip-accent); }
    .toggle-switch input:checked + .toggle-slider:before { transform: translateX(18px); }

    @media (max-width: 991.98px) {
        .vm-wrapper { flex-direction: column; height: auto; min-height: calc(100vh - 120px); }
        .vm-sidebar { width: 100%; min-width: 100%; max-height: 280px; }
    }
</style>
@endpush

@section('content')
<div class="p-3">
    <div class="vm-wrapper">
        {{-- Left Rail --}}
        <div class="vm-sidebar">
            <div class="vm-sidebar-header">
                <h6><i class="bi bi-star-fill me-1"></i> VIP MASTER</h6>
            </div>
            <div class="vm-sidebar-body">
                <a class="vm-nav-item" data-category="unit" onclick="vmOpen(this)">
                    <i class="bi bi-window"></i> Units
                    <span class="count" id="countUnit">{{ $counts['units'] }}</span>
                </a>
                <a class="vm-nav-item" data-category="frame_type" onclick="vmOpen(this)">
                    <i class="bi bi-aspect-ratio"></i> Frame Type
                    <span class="count" id="countFrameType">{{ $counts['frame_types'] }}</span>
                </a>
                <a class="vm-nav-item" data-category="grid" onclick="vmOpen(this)">
                    <i class="bi bi-grid-3x3"></i> Grids
                    <span class="count" id="countGrid">{{ $counts['grids'] }}</span>
                </a>
                <a class="vm-nav-item" data-category="pattern" onclick="vmOpen(this)">
                    <i class="bi bi-grid-3x3-gap"></i> Pattern
                    <span class="count" id="countPattern">{{ $counts['patterns'] }}</span>
                </a>
            </div>
        </div>

        {{-- Main Panel --}}
        <div class="vm-main">
            <div class="vm-toolbar">
                <h6 id="vmTitle"><i class="bi bi-star-fill me-1" style="color:var(--vip-accent)"></i> VIP Master</h6>
                <div class="actions">
                    <button class="btn btn-sm btn-vip" id="vmAddBtn" style="display:none;" onclick="vmShowAdd()">
                        <i class="bi bi-plus-lg me-1"></i> <span id="vmAddLabel">Add Item</span>
                    </button>
                </div>
            </div>
            <div class="vm-content" id="vmContent">
                <div class="vm-empty">
                    <i class="bi bi-arrow-left-circle"></i>
                    <h5 style="color:#666;">Select a section</h5>
                    <p class="small">Manage units, frame types, grids, and patterns for tech measurements.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="vmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--vip-primary); color:#fff;">
                <h5 class="modal-title" id="vmModalTitle"><i class="bi bi-plus-lg me-2"></i>Add Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="vmEditId">
                <div class="row g-3">
                    <div class="col-8">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" id="vmName" class="form-control" required placeholder="e.g. SH, Retrofit, SDL...">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold">Code</label>
                        <input type="text" id="vmCode" class="form-control" placeholder="Short code">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="vmActive" checked>
                            <label class="form-check-label" for="vmActive">Active (visible in tech measure dropdowns)</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip" onclick="vmSave()"><i class="bi bi-check-lg me-1"></i> <span id="vmSaveLabel">Create</span></button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
let currentCategory = null;
const modal = new bootstrap.Modal(document.getElementById('vmModal'));

const categoryLabels = {
    unit: { title: 'Units', singular: 'Unit', icon: 'bi-window' },
    frame_type: { title: 'Frame Types', singular: 'Frame Type', icon: 'bi-aspect-ratio' },
    grid: { title: 'Grids', singular: 'Grid', icon: 'bi-grid-3x3' },
    pattern: { title: 'Patterns', singular: 'Pattern', icon: 'bi-grid-3x3-gap' },
};

const countIds = {
    unit: 'countUnit',
    frame_type: 'countFrameType',
    grid: 'countGrid',
    pattern: 'countPattern',
};

// Auto-open first section
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const section = params.get('section');
    const navItems = document.querySelectorAll('.vm-nav-item');
    let opened = false;
    if (section) {
        navItems.forEach(el => {
            if (el.dataset.category === section) { vmOpen(el); opened = true; }
        });
    }
    if (!opened && navItems.length) vmOpen(navItems[0]);
});

function vmOpen(el) {
    document.querySelectorAll('.vm-nav-item').forEach(a => a.classList.remove('active'));
    el.classList.add('active');

    currentCategory = el.dataset.category;
    const info = categoryLabels[currentCategory];

    document.getElementById('vmTitle').innerHTML = `<i class="bi ${info.icon} me-1" style="color:var(--vip-accent)"></i> ${info.title}`;
    document.getElementById('vmAddBtn').style.display = '';
    document.getElementById('vmAddLabel').textContent = `Add ${info.singular}`;

    // Update URL
    const u = new URL(window.location);
    u.searchParams.set('section', currentCategory);
    history.replaceState(null, '', u);

    vmLoadList();
}

function vmLoadList() {
    const content = document.getElementById('vmContent');
    content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>';

    fetch(`/admin/vip-master/${currentCategory}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const items = data.items || [];
        const info = categoryLabels[currentCategory];

        // Update count badge
        const countEl = document.getElementById(countIds[currentCategory]);
        if (countEl) countEl.textContent = items.length;

        if (!items.length) {
            content.innerHTML = `
                <div class="vm-empty">
                    <i class="bi ${info.icon}"></i>
                    <h5 style="color:#666;">No ${info.title.toLowerCase()} yet</h5>
                    <p class="small">Click <strong>Add ${info.singular}</strong> to create one.</p>
                </div>`;
            return;
        }

        let html = `<table class="vm-table">
            <thead><tr>
                <th style="width:40px;">#</th>
                <th>Name</th>
                <th>Code</th>
                <th class="text-center" style="width:80px;">Active</th>
                <th class="text-end" style="width:100px;">Actions</th>
            </tr></thead>
            <tbody>`;

        items.forEach((item, i) => {
            html += `<tr data-id="${item.id}">
                <td class="text-muted">${i + 1}</td>
                <td class="fw-semibold">${escHtml(item.name)}</td>
                <td class="text-muted">${item.code || '—'}</td>
                <td class="text-center">
                    <label class="toggle-switch">
                        <input type="checkbox" ${item.is_active ? 'checked' : ''} onchange="vmToggle(${item.id})">
                        <span class="toggle-slider"></span>
                    </label>
                </td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-dark me-1" onclick='vmEdit(${JSON.stringify(item)})' title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="vmDelete(${item.id})" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
        });

        html += '</tbody></table>';
        content.innerHTML = html;
    })
    .catch(() => {
        content.innerHTML = '<div class="alert alert-danger m-3">Failed to load items.</div>';
    });
}

function vmShowAdd() {
    const info = categoryLabels[currentCategory];
    document.getElementById('vmModalTitle').innerHTML = `<i class="bi bi-plus-lg me-2"></i>Add ${info.singular}`;
    document.getElementById('vmSaveLabel').textContent = 'Create';
    document.getElementById('vmEditId').value = '';
    document.getElementById('vmName').value = '';
    document.getElementById('vmCode').value = '';
    document.getElementById('vmActive').checked = true;
    modal.show();
}

function vmEdit(item) {
    const info = categoryLabels[currentCategory];
    document.getElementById('vmModalTitle').innerHTML = `<i class="bi bi-pencil me-2"></i>Edit ${info.singular}`;
    document.getElementById('vmSaveLabel').textContent = 'Update';
    document.getElementById('vmEditId').value = item.id;
    document.getElementById('vmName').value = item.name;
    document.getElementById('vmCode').value = item.code || '';
    document.getElementById('vmActive').checked = !!item.is_active;
    modal.show();
}

function vmSave() {
    const editId = document.getElementById('vmEditId').value;
    const name = document.getElementById('vmName').value.trim();
    const code = document.getElementById('vmCode').value.trim();
    const isActive = document.getElementById('vmActive').checked ? 1 : 0;

    if (!name) { alert('Name is required.'); return; }

    const url = editId
        ? `/admin/vip-master/${currentCategory}/${editId}`
        : `/admin/vip-master/${currentCategory}`;
    const method = editId ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, code: code || null, is_active: isActive })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            modal.hide();
            vmLoadList();
        } else {
            alert('Failed to save.');
        }
    })
    .catch(() => alert('Failed to save.'));
}

function vmToggle(id) {
    fetch(`/admin/vip-master/${currentCategory}/${id}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .catch(() => alert('Failed to toggle.'));
}

function vmDelete(id) {
    if (!confirm('Delete this item? This cannot be undone.')) return;

    fetch(`/admin/vip-master/${currentCategory}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) vmLoadList(); })
    .catch(() => alert('Failed to delete.'));
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
</script>
@endpush
