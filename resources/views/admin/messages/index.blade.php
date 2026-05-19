@extends('layouts.app')
@section('title', 'Messages')

@push('styles')
<style>
    .msg-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Left Rail ── */
    .msg-rail {
        width: 320px; min-width: 320px;
        background: #fff; border-right: 1px solid rgba(0,0,0,.08);
        display: flex; flex-direction: column;
    }
    .msg-rail-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,.06);
        display: flex; align-items: center; justify-content: space-between;
    }
    .msg-rail-header h6 { margin: 0; font-size: .85rem; font-weight: 700; }
    .msg-search { padding: .5rem 1rem; border-bottom: 1px solid rgba(0,0,0,.06); }
    .msg-search input {
        width: 100%; padding: .4rem .75rem; font-size: .82rem;
        border: 1px solid rgba(0,0,0,.1); border-radius: .375rem; background: #fafaf7;
    }
    .msg-search input:focus { outline: none; border-color: var(--vip-accent); }

    .msg-list { flex: 1; overflow-y: auto; }
    .msg-conv {
        display: flex; align-items: center; gap: .75rem;
        padding: .75rem 1.25rem; cursor: pointer;
        border-bottom: 1px solid rgba(0,0,0,.04); transition: background .1s;
    }
    .msg-conv:hover { background: rgba(201,168,76,.04); }
    .msg-conv.active { background: rgba(201,168,76,.1); border-left: 3px solid var(--vip-accent); }
    .msg-conv-avatar {
        width: 40px; height: 40px; border-radius: 50%;
        background: linear-gradient(135deg, var(--vip-accent), #a0832a);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 700; font-size: .9rem; flex-shrink: 0;
    }
    .msg-conv-info { flex: 1; min-width: 0; }
    .msg-conv-name { font-weight: 600; font-size: .85rem; color: #111; }
    .msg-conv-preview {
        font-size: .75rem; color: #888;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px;
    }
    .msg-conv-meta { flex-shrink: 0; text-align: right; }
    .msg-conv-time { font-size: .65rem; color: #aaa; }
    .msg-conv-badge {
        display: inline-block; background: var(--vip-accent); color: #fff;
        font-size: .6rem; font-weight: 700; padding: 2px 6px; border-radius: 10px; margin-top: 2px;
    }

    /* ── Chat Area ── */
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
    .msg-bubble-wrap { display: flex; align-items: flex-end; gap: .5rem; max-width: 80%; }
    .msg-bubble-wrap.mine { margin-left: auto; }
    .msg-bubble {
        padding: .6rem 1rem; border-radius: .75rem;
        font-size: .85rem; line-height: 1.5; position: relative; word-break: break-word;
    }
    .msg-bubble.theirs {
        background: #fff; color: #333; border-bottom-left-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .msg-bubble.mine {
        background: var(--vip-accent); color: #fff; border-bottom-right-radius: 4px;
    }
    .msg-bubble .bubble-time { font-size: .6rem; margin-top: 4px; display: block; }
    .msg-bubble.theirs .bubble-time { color: #aaa; }
    .msg-bubble.mine .bubble-time { color: rgba(255,255,255,.6); }
    .msg-bubble .bubble-sender { font-size: .65rem; font-weight: 700; margin-bottom: 2px; display: block; }
    .msg-bubble.theirs .bubble-sender { color: var(--vip-accent); }
    .msg-bubble.mine .bubble-sender { display: none; }

    /* Attachments */
    .msg-attachment { margin-top: .25rem; }
    .msg-attachment img {
        max-width: 260px; max-height: 200px; border-radius: .5rem; cursor: pointer;
        display: block; margin-top: .25rem;
    }
    .msg-attachment-file {
        display: flex; align-items: center; gap: .5rem; padding: .4rem .6rem;
        border-radius: .375rem; margin-top: .25rem; font-size: .8rem; text-decoration: none;
    }
    .msg-bubble.theirs .msg-attachment-file { background: rgba(0,0,0,.05); color: #333; }
    .msg-bubble.mine .msg-attachment-file { background: rgba(255,255,255,.2); color: #fff; }
    .msg-attachment-file i { font-size: 1.2rem; }
    .msg-attachment-file .file-info { min-width: 0; }
    .msg-attachment-file .file-name { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; max-width: 180px; }
    .msg-attachment-file .file-size { font-size: .65rem; opacity: .7; }

    /* Voice note */
    .voice-player {
        display: flex; align-items: center; gap: .5rem;
        padding: .4rem .6rem; border-radius: 1rem; margin-top: .25rem; min-width: 200px;
    }
    .msg-bubble.theirs .voice-player { background: rgba(0,0,0,.05); }
    .msg-bubble.mine .voice-player { background: rgba(255,255,255,.2); }
    .voice-player .play-btn {
        width: 32px; height: 32px; border-radius: 50%; border: none;
        display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0;
    }
    .msg-bubble.theirs .voice-player .play-btn { background: var(--vip-accent); color: #fff; }
    .msg-bubble.mine .voice-player .play-btn { background: #fff; color: var(--vip-accent); }
    .voice-player .voice-wave { flex: 1; height: 24px; display: flex; align-items: center; gap: 1px; }
    .voice-player .voice-bar { width: 3px; border-radius: 2px; min-height: 4px; }
    .msg-bubble.theirs .voice-player .voice-bar { background: rgba(0,0,0,.2); }
    .msg-bubble.mine .voice-player .voice-bar { background: rgba(255,255,255,.4); }
    .voice-player .voice-duration { font-size: .7rem; flex-shrink: 0; min-width: 32px; }

    /* Delete button */
    .msg-delete { opacity: 0; transition: opacity .15s; position: absolute; top: -8px; right: -8px; }
    .msg-bubble-wrap:hover .msg-delete { opacity: 1; }
    .msg-delete button {
        width: 20px; height: 20px; border-radius: 50%; border: none; font-size: .55rem;
        background: #dc3545; color: #fff; display: flex; align-items: center; justify-content: center;
        cursor: pointer;
    }

    /* Compose bar */
    .msg-compose {
        background: #fff; border-top: 1px solid rgba(0,0,0,.08);
        padding: .75rem 1.5rem;
    }
    .compose-toolbar { display: flex; gap: .5rem; align-items: flex-end; }
    .compose-toolbar textarea {
        flex: 1; border: 1px solid rgba(0,0,0,.1); border-radius: .5rem;
        padding: .5rem .75rem; font-size: .85rem; resize: none;
        min-height: 40px; max-height: 120px;
    }
    .compose-toolbar textarea:focus { outline: none; border-color: var(--vip-accent); }
    .compose-actions { display: flex; gap: .35rem; flex-shrink: 0; }
    .compose-actions .btn-icon {
        width: 40px; height: 40px; border-radius: 50%; border: 1px solid rgba(0,0,0,.1);
        background: #fff; color: #666; display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all .15s; font-size: 1rem;
    }
    .compose-actions .btn-icon:hover { border-color: var(--vip-accent); color: var(--vip-accent); }
    .compose-actions .btn-send {
        width: 40px; height: 40px; border-radius: 50%; border: none;
        background: var(--vip-accent); color: #fff;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: opacity .15s;
    }
    .compose-actions .btn-send:hover { opacity: .85; }

    /* Recording indicator */
    .recording-bar {
        display: none; align-items: center; gap: .75rem; padding: .5rem 0;
    }
    .recording-bar.active { display: flex; }
    .recording-dot { width: 10px; height: 10px; border-radius: 50%; background: #dc3545; animation: pulse 1s infinite; }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .3; } }
    .recording-timer { font-size: .85rem; font-weight: 600; color: #dc3545; font-variant-numeric: tabular-nums; }
    .recording-cancel { font-size: .8rem; color: #888; cursor: pointer; }
    .recording-cancel:hover { color: #dc3545; }

    /* Attachment preview */
    .attach-preview {
        display: none; padding: .5rem 0; gap: .5rem; align-items: center;
    }
    .attach-preview.active { display: flex; }
    .attach-preview .file-chip {
        display: flex; align-items: center; gap: .4rem; padding: .25rem .6rem;
        background: rgba(201,168,76,.1); border-radius: .375rem; font-size: .78rem;
    }
    .attach-preview .file-chip .remove { cursor: pointer; color: #dc3545; font-weight: 700; }

    .date-divider {
        text-align: center; font-size: .65rem; color: #aaa;
        padding: .5rem 0; text-transform: uppercase; letter-spacing: .5px;
    }

    /* Image lightbox */
    .img-lightbox {
        display: none; position: fixed; inset: 0; background: rgba(0,0,0,.85);
        z-index: 9999; align-items: center; justify-content: center; cursor: pointer;
    }
    .img-lightbox.active { display: flex; }
    .img-lightbox img { max-width: 90%; max-height: 90%; border-radius: .5rem; }

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
            <input type="text" id="msgSearch" placeholder="Search conversations...">
        </div>
        <div class="msg-list" id="convList">
            @forelse($conversations as $conv)
                <div class="msg-conv" data-id="{{ $conv->id }}"
                     data-search="{{ strtolower($conv->installer->name ?? '') }}">
                    <div class="msg-conv-avatar">
                        {{ strtoupper(substr($conv->installer->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="msg-conv-info">
                        <div class="msg-conv-name">{{ $conv->installer->name ?? 'Unknown' }}</div>
                        <div class="msg-conv-preview">
                            @if($conv->latestMessage?->message_type === 'voice')
                                <i class="bi bi-mic-fill"></i> Voice message
                            @elseif($conv->latestMessage?->message_type === 'file')
                                <i class="bi bi-paperclip"></i> {{ $conv->latestMessage?->attachment_name ?? 'File' }}
                            @else
                                {{ Str::limit($conv->latestMessage?->body ?? 'No messages yet', 40) }}
                            @endif
                        </div>
                    </div>
                    <div class="msg-conv-meta">
                        <div class="msg-conv-time">{{ $conv->last_message_at?->diffForHumans(null, true) }}</div>
                        @if($conv->unread_count > 0)
                            <span class="msg-conv-badge">{{ $conv->unread_count }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5" style="color:rgba(0,0,0,.3);">
                    <i class="bi bi-chat-dots" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0 small">No conversations yet</p>
                    <p class="small text-muted">Click "New" to start messaging</p>
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
                <p>Select a conversation or start a new one</p>
            </div>
        </div>
        <div class="msg-compose" id="chatCompose" style="display:none;">
            <div class="attach-preview" id="attachPreview">
                <div class="file-chip">
                    <i class="bi bi-paperclip"></i>
                    <span id="attachFileName">file.jpg</span>
                    <span class="remove" onclick="clearAttachment()">&times;</span>
                </div>
            </div>
            <div class="recording-bar" id="recordingBar">
                <div class="recording-dot"></div>
                <span class="recording-timer" id="recordingTimer">0:00</span>
                <span class="recording-cancel" onclick="cancelRecording()"><i class="bi bi-x-lg me-1"></i>Cancel</span>
                <button class="btn btn-sm btn-success ms-auto" onclick="stopAndSendRecording()"><i class="bi bi-send me-1"></i>Send</button>
            </div>
            <div class="compose-toolbar" id="composeToolbar">
                <textarea id="msgInput" placeholder="Type a message..." rows="1"></textarea>
                <div class="compose-actions">
                    <input type="file" id="fileInput" style="display:none" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt" onchange="handleFileSelect(this)">
                    <button class="btn-icon" onclick="document.getElementById('fileInput').click()" title="Attach file">
                        <i class="bi bi-paperclip"></i>
                    </button>
                    <button class="btn-icon" id="btnMic" onclick="toggleRecording()" title="Voice note">
                        <i class="bi bi-mic"></i>
                    </button>
                    <button class="btn-send" onclick="sendMessage()" id="btnSend" title="Send">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Image Lightbox --}}
<div class="img-lightbox" id="imgLightbox" onclick="this.classList.remove('active')">
    <img id="lightboxImg" src="" alt="">
</div>

{{-- New Message Modal --}}
<div class="modal fade" id="newMessageModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-pencil-square me-1"></i> New Message</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">To (Installer)</label>
                    <select id="newMsgInstaller" class="form-select form-select-sm">
                        <option value="">Select installer...</option>
                        @foreach($installers as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label">Message</label>
                    <textarea id="newMsgBody" class="form-control form-control-sm" rows="3" placeholder="Type your message..."></textarea>
                </div>
            </div>
            <div class="modal-footer py-2">
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
const basePath = '/admin/messages';
let currentConvId = null;
let pollingInterval = null;
let selectedFile = null;

// ── Recording state ──
let mediaRecorder = null;
let audioChunks = [];
let recordingStartTime = null;
let recordingTimerInterval = null;

// ── Conversation click ──
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
    document.getElementById('chatCompose').style.display = 'block';
    clearAttachment();

    fetch(`${basePath}/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('chatTitle').textContent = data.conversation.installer?.name || 'Installer';
        renderMessages(data.messages);
        if (pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(() => refreshMessages(id), 6000);
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
        html += renderBubble(m);
    });

    if (!messages.length) {
        html = '<div class="msg-empty-state"><i class="bi bi-chat"></i><p class="small">No messages yet. Say hello!</p></div>';
    }

    body.innerHTML = html;
    body.scrollTop = body.scrollHeight;
}

function renderBubble(m) {
    const align = m.is_mine ? 'mine' : 'theirs';
    let content = '';

    // Text body
    if (m.body) {
        content += escapeHtml(m.body);
    }

    // Voice note
    if (m.message_type === 'voice' && m.attachment_url) {
        content += renderVoicePlayer(m.attachment_url, m.id);
    }
    // Image
    else if (m.message_type === 'file' && m.is_image && m.attachment_url) {
        content += `<div class="msg-attachment"><img src="${m.attachment_url}" onclick="openLightbox('${m.attachment_url}')" alt="Image"></div>`;
    }
    // File
    else if (m.message_type === 'file' && m.attachment_url) {
        content += `<div class="msg-attachment">
            <a href="${m.attachment_url}" target="_blank" class="msg-attachment-file" download>
                <i class="bi bi-file-earmark"></i>
                <div class="file-info">
                    <span class="file-name">${escapeHtml(m.attachment_name || 'File')}</span>
                    <span class="file-size">${m.attachment_size}</span>
                </div>
                <i class="bi bi-download"></i>
            </a>
        </div>`;
    }

    const deleteBtn = m.is_mine ? `<div class="msg-delete"><button onclick="deleteMessage(${m.id})" title="Delete"><i class="bi bi-trash3"></i></button></div>` : '';

    return `<div class="msg-bubble-wrap ${align}">
        <div class="msg-bubble ${align}" style="position:relative;">
            <span class="bubble-sender">${escapeHtml(m.sender_name)}</span>
            ${content}
            <span class="bubble-time">${m.created_at}${m.is_mine && m.read_at ? ' · Read' : ''}</span>
            ${deleteBtn}
        </div>
    </div>`;
}

function renderVoicePlayer(url, id) {
    const bars = Array.from({length: 30}, () => {
        const h = Math.floor(Math.random() * 16) + 4;
        return `<div class="voice-bar" style="height:${h}px;"></div>`;
    }).join('');

    return `<div class="voice-player">
        <button class="play-btn" onclick="playVoice(this, '${url}')" data-playing="false">
            <i class="bi bi-play-fill"></i>
        </button>
        <div class="voice-wave">${bars}</div>
        <span class="voice-duration">--:--</span>
    </div>`;
}

function playVoice(btn, url) {
    const isPlaying = btn.dataset.playing === 'true';
    // Stop all other audio
    document.querySelectorAll('audio.voice-audio').forEach(a => { a.pause(); a.remove(); });
    document.querySelectorAll('.play-btn').forEach(b => { b.dataset.playing = 'false'; b.innerHTML = '<i class="bi bi-play-fill"></i>'; });

    if (isPlaying) return;

    const audio = new Audio();
    audio.preload = 'auto';
    audio.src = url;
    audio.className = 'voice-audio';
    audio.style.display = 'none';
    document.body.appendChild(audio);

    btn.dataset.playing = 'true';
    btn.innerHTML = '<i class="bi bi-pause-fill"></i>';

    const durSpan = btn.closest('.voice-player').querySelector('.voice-duration');

    audio.addEventListener('loadedmetadata', () => {
        const dur = Math.round(audio.duration);
        durSpan.textContent = Math.floor(dur/60) + ':' + String(dur%60).padStart(2,'0');
    });
    audio.addEventListener('ended', () => {
        btn.dataset.playing = 'false';
        btn.innerHTML = '<i class="bi bi-play-fill"></i>';
        audio.remove();
    });
    audio.addEventListener('error', () => {
        btn.dataset.playing = 'false';
        btn.innerHTML = '<i class="bi bi-play-fill"></i>';
        durSpan.textContent = 'Error';
        audio.remove();
        window.open(url, '_blank');
    });
    audio.play().catch(err => {
        console.error('Voice playback failed:', err);
        btn.dataset.playing = 'false';
        btn.innerHTML = '<i class="bi bi-play-fill"></i>';
        window.open(url, '_blank');
    });
}

function refreshMessages(id) {
    if (currentConvId != id) return;
    fetch(`${basePath}/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => renderMessages(data.messages))
    .catch(() => {});
}

// ── Send message ──
function sendMessage() {
    if (!currentConvId) return;
    const input = document.getElementById('msgInput');
    const body = input.value.trim();

    if (!body && !selectedFile) return;

    const formData = new FormData();
    if (body) formData.append('body', body);
    if (selectedFile) formData.append('attachment', selectedFile);

    const btn = document.getElementById('btnSend');
    btn.disabled = true;

    fetch(`${basePath}/${currentConvId}/send`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            clearAttachment();
            loadConversation(currentConvId);
        } else {
            alert(data.error || 'Failed to send.');
        }
    })
    .catch(() => alert('Failed to send message.'))
    .finally(() => { btn.disabled = false; input.focus(); });
}

// ── File attachment ──
function handleFileSelect(input) {
    if (!input.files.length) return;
    selectedFile = input.files[0];
    document.getElementById('attachFileName').textContent = selectedFile.name;
    document.getElementById('attachPreview').classList.add('active');
    input.value = '';
}

function clearAttachment() {
    selectedFile = null;
    document.getElementById('attachPreview').classList.remove('active');
}

// ── Voice recording ──
async function toggleRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        stopAndSendRecording();
        return;
    }

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        audioChunks = [];

        // Pick a supported audio format
        let mimeType = '';
        for (const mt of ['audio/webm;codecs=opus', 'audio/webm', 'audio/ogg;codecs=opus', 'audio/mp4', 'audio/wav']) {
            if (MediaRecorder.isTypeSupported(mt)) { mimeType = mt; break; }
        }
        const options = mimeType ? { mimeType } : {};
        mediaRecorder = new MediaRecorder(stream, options);

        mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };

        mediaRecorder.onstop = () => {
            stream.getTracks().forEach(t => t.stop());
        };

        mediaRecorder.start();
        recordingStartTime = Date.now();
        document.getElementById('recordingBar').classList.add('active');
        document.getElementById('composeToolbar').style.display = 'none';
        document.getElementById('btnMic').style.color = '#dc3545';

        recordingTimerInterval = setInterval(() => {
            const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
            document.getElementById('recordingTimer').textContent =
                Math.floor(elapsed/60) + ':' + String(elapsed%60).padStart(2,'0');
        }, 200);
    } catch (err) {
        alert('Microphone access denied. Please allow microphone access to send voice notes.');
    }
}

function cancelRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
    }
    clearInterval(recordingTimerInterval);
    document.getElementById('recordingBar').classList.remove('active');
    document.getElementById('composeToolbar').style.display = 'flex';
    document.getElementById('btnMic').style.color = '';
    audioChunks = [];
    mediaRecorder = null;
}

function stopAndSendRecording() {
    if (!mediaRecorder || mediaRecorder.state !== 'recording') return;

    mediaRecorder.onstop = () => {
        mediaRecorder.stream.getTracks().forEach(t => t.stop());
        clearInterval(recordingTimerInterval);
        document.getElementById('recordingBar').classList.remove('active');
        document.getElementById('composeToolbar').style.display = 'flex';
        document.getElementById('btnMic').style.color = '';

        const actualMime = mediaRecorder.mimeType || 'audio/webm';
        const ext = actualMime.includes('mp4') ? 'mp4' : actualMime.includes('ogg') ? 'ogg' : actualMime.includes('wav') ? 'wav' : 'webm';
        const blob = new Blob(audioChunks, { type: actualMime });
        const formData = new FormData();
        formData.append('voice_note', blob, `voice_note.${ext}`);
        formData.append('body', '');

        fetch(`${basePath}/${currentConvId}/send`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) loadConversation(currentConvId);
        })
        .catch(() => alert('Failed to send voice note.'));

        audioChunks = [];
        mediaRecorder = null;
    };

    mediaRecorder.stop();
}

// ── Delete message ──
function deleteMessage(messageId) {
    if (!confirm('Delete this message?')) return;
    fetch(`${basePath}/${currentConvId}/message/${messageId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) loadConversation(currentConvId); })
    .catch(() => alert('Failed to delete.'));
}

// ── New conversation ──
function startNewConversation() {
    const installerId = document.getElementById('newMsgInstaller').value;
    const body = document.getElementById('newMsgBody').value.trim();
    if (!installerId) { alert('Please select an installer.'); return; }
    if (!body) { alert('Please type a message.'); return; }

    fetch(`${basePath}/start`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ installer_id: installerId, body })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('newMessageModal')).hide();
            window.location.reload();
        } else { alert('Failed to start conversation.'); }
    })
    .catch(() => alert('Failed to start conversation.'));
}

// ── Keyboard ──
document.getElementById('msgInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
});

document.getElementById('msgInput').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

function openLightbox(url) {
    document.getElementById('lightboxImg').src = url;
    document.getElementById('imgLightbox').classList.add('active');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML.replace(/\n/g, '<br>');
}

// Poll for unread badge
setInterval(() => {
    fetch(`${basePath}/unread-count`, {
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
