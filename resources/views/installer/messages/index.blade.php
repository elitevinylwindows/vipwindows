@extends('layouts.installer')
@section('title', 'Messages')

@push('styles')
<style>
    .msg-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Left Rail: Conversation List ── */
    .msg-rail {
        width: 320px; min-width: 320px;
        background: var(--vip-primary);
        color: #fff;
        display: flex; flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.06);
    }
    .msg-rail-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(255,255,255,.08);
        display: flex; align-items: center; justify-content: space-between;
    }
    .msg-rail-header h6 { margin: 0; font-size: .85rem; font-weight: 700; color: #fff; }
    .msg-search {
        padding: .5rem 1rem;
        border-bottom: 1px solid rgba(255,255,255,.06);
    }
    .msg-search input {
        width: 100%; padding: .4rem .75rem; font-size: .82rem;
        border: 1px solid rgba(255,255,255,.12); border-radius: .375rem;
        background: rgba(255,255,255,.08); color: #fff;
    }
    .msg-search input::placeholder { color: rgba(255,255,255,.4); }
    .msg-search input:focus { outline: none; border-color: var(--vip-accent); }

    .msg-list { flex: 1; overflow-y: auto; }
    .msg-conv {
        display: flex; align-items: center; gap: .75rem;
        padding: .75rem 1.25rem; cursor: pointer;
        border-bottom: 1px solid rgba(255,255,255,.04);
        transition: background .1s;
    }
    .msg-conv:hover { background: rgba(255,255,255,.06); }
    .msg-conv.active { background: rgba(201,168,76,.15); border-left: 3px solid var(--vip-accent); }
    .msg-conv-avatar {
        width: 38px; height: 38px; border-radius: 50%;
        background: linear-gradient(135deg, var(--vip-accent), #a0832a);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 700; font-size: .85rem; flex-shrink: 0;
    }
    .msg-conv-info { flex: 1; min-width: 0; }
    .msg-conv-name { font-weight: 600; font-size: .85rem; color: #fff; }
    .msg-conv-preview {
        font-size: .75rem; color: rgba(255,255,255,.5);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        margin-top: 1px;
    }
    .msg-conv-meta { flex-shrink: 0; text-align: right; }
    .msg-conv-time { font-size: .65rem; color: rgba(255,255,255,.4); }
    .msg-conv-badge {
        display: inline-block; background: var(--vip-accent); color: #fff;
        font-size: .6rem; font-weight: 700; padding: 2px 6px; border-radius: 10px;
        margin-top: 2px;
    }

    /* ── Right: Chat Area ── */
    .msg-chat { flex: 1; display: flex; flex-direction: column; background: var(--vip-light); }
    .msg-chat-header {
        background: #fff; border-bottom: 1px solid rgba(0,0,0,.06);
        padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
    }
    .msg-chat-header h6 { margin: 0; font-weight: 700; font-size: .95rem; }
    .msg-chat-body {
        flex: 1; overflow-y: auto; padding: 1rem 1.5rem;
        display: flex; flex-direction: column; gap: .5rem;
    }
    .msg-empty-state {
        flex: 1; display: flex; flex-direction: column;
        align-items: center; justify-content: center; color: rgba(0,0,0,.3);
    }
    .msg-empty-state i { font-size: 3rem; margin-bottom: .75rem; }

    /* Bubbles */
    .msg-bubble-wrap { display: flex; align-items: flex-end; gap: .5rem; }
    .msg-bubble-wrap.mine { justify-content: flex-end; }
    .msg-bubble {
        max-width: 65%; padding: .6rem 1rem; border-radius: .75rem;
        font-size: .85rem; line-height: 1.5; position: relative;
    }
    .msg-bubble.theirs {
        background: #fff; color: #333;
        border-bottom-left-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .msg-bubble.mine {
        background: var(--vip-accent); color: #fff;
        border-bottom-right-radius: 4px;
    }
    .msg-bubble .bubble-time { font-size: .6rem; margin-top: 4px; display: block; }
    .msg-bubble.theirs .bubble-time { color: #aaa; }
    .msg-bubble.mine .bubble-time { color: rgba(255,255,255,.6); }

    /* Compose bar */
    .msg-compose {
        background: #fff; border-top: 1px solid rgba(0,0,0,.08);
        padding: .75rem 1.5rem;
        display: flex; gap: .5rem; align-items: flex-end;
    }
    .msg-compose textarea {
        flex: 1; border: 1px solid rgba(0,0,0,.1); border-radius: .5rem;
        padding: .5rem .75rem; font-size: .85rem; resize: none;
        min-height: 40px; max-height: 120px;
    }
    .msg-compose textarea:focus { outline: none; border-color: var(--vip-accent); }
    .msg-compose .btn-send {
        background: var(--vip-accent); color: #fff; border: none;
        width: 40px; height: 40px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        transition: opacity .15s; flex-shrink: 0;
    }
    .msg-compose .btn-send:hover { opacity: .85; }

    .date-divider {
        text-align: center; font-size: .65rem; color: #aaa;
        padding: .5rem 0; text-transform: uppercase; letter-spacing: .5px;
    }

    @media (max-width: 991.98px) {
        .msg-container { flex-direction: column; height: auto; }
        .msg-rail { width: 100%; min-width: 100%; max-height: 40vh; }
    }
</style>
@endpush

@section('content')
<div class="msg-container">
    {{-- Left Rail --}}
    <div class="msg-rail">
        <div class="msg-rail-header">
            <h6><i class="bi bi-chat-dots me-1"></i> Messages</h6>
            <button class="btn btn-sm btn-vip" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                <i class="bi bi-plus-lg me-1"></i>New
            </button>
        </div>
        <div class="msg-search">
            <input type="text" id="msgSearch" placeholder="Search...">
        </div>
        <div class="msg-list">
            @forelse($conversations as $conv)
                <div class="msg-conv" data-id="{{ $conv->id }}"
                     data-search="{{ strtolower($conv->admin->name ?? '') }}">
                    <div class="msg-conv-avatar">
                        {{ strtoupper(substr($conv->admin->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="msg-conv-info">
                        <div class="msg-conv-name">{{ $conv->admin->name ?? 'Admin' }}</div>
                        <div class="msg-conv-preview">{{ Str::limit($conv->latestMessage?->body ?? 'No messages yet', 40) }}</div>
                    </div>
                    <div class="msg-conv-meta">
                        <div class="msg-conv-time">{{ $conv->last_message_at?->diffForHumans(null, true) }}</div>
                        @if($conv->unread_count > 0)
                            <span class="msg-conv-badge">{{ $conv->unread_count }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5" style="color:rgba(255,255,255,.35);">
                    <i class="bi bi-chat-dots" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0 small">No messages yet</p>
                    <p class="small" style="color:rgba(255,255,255,.3);">Admin will reach out when needed</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Chat Area --}}
    <div class="msg-chat">
        <div class="msg-chat-header" id="chatHeader">
            <h6 id="chatTitle">Select a conversation</h6>
            <span id="chatStatus" class="small text-muted"></span>
        </div>
        <div class="msg-chat-body" id="chatBody">
            <div class="msg-empty-state">
                <i class="bi bi-chat-dots"></i>
                <p>Select a conversation to view messages</p>
            </div>
        </div>
        <div class="msg-compose" id="chatCompose" style="display:none;">
            <textarea id="msgInput" placeholder="Type a message..." rows="1"></textarea>
            <button class="btn-send" onclick="sendMessage()" id="btnSend">
                <i class="bi bi-send"></i>
            </button>
        </div>
    </div>
</div>

{{-- New Message Modal --}}
<div class="modal fade" id="newMessageModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header py-2 border-secondary">
                <h6 class="modal-title mb-0"><i class="bi bi-pencil-square me-1"></i> New Message</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small">To (Admin)</label>
                    <select id="newMsgAdmin" class="form-select form-select-sm bg-dark text-white border-secondary">
                        <option value="">Select admin...</option>
                        @foreach($admins as $adm)
                            <option value="{{ $adm->id }}">{{ $adm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label small">Message</label>
                    <textarea id="newMsgBody" class="form-control form-control-sm bg-dark text-white border-secondary" rows="3" placeholder="Type your message..."></textarea>
                </div>
            </div>
            <div class="modal-footer py-2 border-secondary">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-vip" onclick="startNewConversation()">
                    <i class="bi bi-send me-1"></i> Send
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
let currentConvId = null;
let pollingInterval = null;

// Conversation click
document.querySelectorAll('.msg-conv').forEach(conv => {
    conv.addEventListener('click', function() {
        document.querySelectorAll('.msg-conv').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        loadConversation(this.dataset.id);
        const badge = this.querySelector('.msg-conv-badge');
        if (badge) badge.remove();
    });
});

// Search
document.getElementById('msgSearch').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.msg-conv').forEach(c => {
        c.style.display = (!term || c.dataset.search.includes(term)) ? '' : 'none';
    });
});

function loadConversation(id) {
    currentConvId = id;
    const body = document.getElementById('chatBody');
    body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary spinner-border-sm"></div></div>';
    document.getElementById('chatCompose').style.display = 'flex';

    fetch(`/installer/messages/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const conv = data.conversation;
        document.getElementById('chatTitle').textContent = conv.admin?.name || 'Admin';
        renderMessages(data.messages);

        if (pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(() => refreshMessages(id), 8000);
    })
    .catch(() => {
        body.innerHTML = '<div class="alert alert-danger m-3">Failed to load conversation.</div>';
    });
}

function renderMessages(messages) {
    const body = document.getElementById('chatBody');
    let html = '';
    let lastDate = '';

    messages.forEach(m => {
        const msgDate = m.created_at.split(',')[0];
        if (msgDate !== lastDate) {
            html += `<div class="date-divider">${msgDate}</div>`;
            lastDate = msgDate;
        }

        const align = m.is_mine ? 'mine' : 'theirs';
        html += `<div class="msg-bubble-wrap ${align}">
            <div class="msg-bubble ${align}">
                ${escapeHtml(m.body)}
                <span class="bubble-time">${m.created_at}${m.is_mine && m.read_at ? ' · Read' : ''}</span>
            </div>
        </div>`;
    });

    if (!messages.length) {
        html = '<div class="msg-empty-state"><i class="bi bi-chat"></i><p class="small">No messages yet. Say hello!</p></div>';
    }

    body.innerHTML = html;
    body.scrollTop = body.scrollHeight;
}

function refreshMessages(id) {
    if (currentConvId != id) return;
    fetch(`/installer/messages/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => renderMessages(data.messages))
    .catch(() => {});
}

function sendMessage() {
    if (!currentConvId) return;
    const input = document.getElementById('msgInput');
    const body = input.value.trim();
    if (!body) return;

    const btn = document.getElementById('btnSend');
    btn.disabled = true;
    input.disabled = true;

    fetch(`/installer/messages/${currentConvId}/send`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ body })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadConversation(currentConvId);
        } else {
            alert('Failed to send message.');
        }
    })
    .catch(() => alert('Failed to send message.'))
    .finally(() => {
        btn.disabled = false;
        input.disabled = false;
        input.focus();
    });
}

function startNewConversation() {
    const adminId = document.getElementById('newMsgAdmin').value;
    const body = document.getElementById('newMsgBody').value.trim();

    if (!adminId) { alert('Please select an admin.'); return; }
    if (!body) { alert('Please type a message.'); return; }

    fetch('/installer/messages/start', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ admin_id: adminId, body })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('newMessageModal')).hide();
            window.location.reload();
        } else {
            alert('Failed to start conversation.');
        }
    })
    .catch(() => alert('Failed to start conversation.'));
}

// Enter to send
document.getElementById('msgInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

// Auto-grow textarea
document.getElementById('msgInput').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML.replace(/\n/g, '<br>');
}

// Poll for unread count
setInterval(() => {
    fetch('/installer/messages/unread-count', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const badge = document.getElementById('msgUnreadBadge');
        if (badge) {
            badge.textContent = data.count;
            badge.style.display = data.count > 0 ? 'inline-block' : 'none';
        }
    })
    .catch(() => {});
}, 30000);
</script>
@endpush
