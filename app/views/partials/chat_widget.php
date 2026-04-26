<?php
use App\Lib\Auth;
use App\Models\Chat;

// Widget chat custom maison.
// Masqué si :
//   - la vue pose $hide_chat = true (pages auth notamment)
//   - l'URL matche un pattern à exclure (mon-compte, auteur, admin sauf /admin/chat)
if (!empty($hide_chat ?? false)) {
    return;
}

$vcPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$vcHidePrefixes = ['/lire/', '/mon-compte', '/auteur'];
foreach ($vcHidePrefixes as $vcPrefix) {
    if (str_starts_with($vcPath, $vcPrefix)) { return; }
}
// /admin/* masqué SAUF /admin/chat (où l'admin gère les conversations)
if (str_starts_with($vcPath, '/admin') && !str_starts_with($vcPath, '/admin/chat')) {
    return;
}

$vcUser = Auth::check() ? Auth::user() : null;
$vcOfficeHours = Chat::isOfficeHours();
$vcCsrf = csrf_token();
$vcUserPrenom = $vcUser->prenom ?? null;
?>
<div id="vc-chat-root"
     data-csrf="<?= e($vcCsrf) ?>"
     data-office-hours="<?= $vcOfficeHours ? '1' : '0' ?>"
     data-user-prenom="<?= e($vcUserPrenom ?? '') ?>"
     data-logged-in="<?= $vcUser ? '1' : '0' ?>">

    <button type="button" id="vc-chat-toggle" aria-label="Ouvrir le chat" class="vc-chat-toggle">
        <svg id="vc-chat-icon-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <svg id="vc-chat-icon-close" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:none">
            <line x1="6" y1="6" x2="18" y2="18"/>
            <line x1="18" y1="6" x2="6" y2="18"/>
        </svg>
        <span id="vc-chat-badge" class="vc-chat-badge" hidden>1</span>
    </button>

    <div id="vc-chat-panel" class="vc-chat-panel" hidden>
        <header class="vc-chat-header">
            <div class="vc-chat-header-info">
                <div class="vc-chat-title">Variable Chat</div>
                <div class="vc-chat-status">
                    <span class="vc-chat-status-dot vc-chat-status-<?= $vcOfficeHours ? 'on' : 'off' ?>"></span>
                    <span><?= $vcOfficeHours ? 'En ligne' : 'Hors heures (lun-sam 8h-19h)' ?></span>
                </div>
            </div>
            <button type="button" id="vc-chat-close" class="vc-chat-close-btn" aria-label="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                    <line x1="6" y1="6" x2="18" y2="18"/>
                    <line x1="18" y1="6" x2="6" y2="18"/>
                </svg>
            </button>
        </header>

        <div id="vc-chat-messages" class="vc-chat-messages" role="log" aria-live="polite"></div>

        <div id="vc-chat-quick" class="vc-chat-quick"></div>

        <div id="vc-chat-email-form" class="vc-chat-email-form" hidden>
            <p class="vc-chat-email-label">Laisse-nous ton email, on revient vers toi rapidement.</p>
            <input type="email" id="vc-chat-email-input" placeholder="ton@email.com" required>
            <input type="text" id="vc-chat-name-input" placeholder="Ton prénom (optionnel)">
            <button type="button" id="vc-chat-email-submit">Envoyer mon email</button>
        </div>

        <form id="vc-chat-form" class="vc-chat-input-bar" autocomplete="off">
            <input type="text" id="vc-chat-text" placeholder="Écris ton message…" maxlength="2000" autocomplete="off">
            <button type="submit" id="vc-chat-send" aria-label="Envoyer">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </form>
    </div>
</div>

<style>
/* ---------- Variable Chat Widget — styles isolés (préfixe vc-chat-) ---------- */
.vc-chat-toggle {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #F59E0B;
    color: #0B0B0F;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 24px rgba(245, 158, 11, 0.35), 0 2px 8px rgba(0, 0, 0, 0.4);
    z-index: 9998;
    transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
}
.vc-chat-toggle:hover { transform: translateY(-2px) scale(1.03); background: #FBBF24; }
.vc-chat-toggle svg { width: 26px; height: 26px; }
.vc-chat-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    background: #ef4444;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #0B0B0F;
}

.vc-chat-panel {
    position: fixed;
    bottom: 96px;
    right: 24px;
    width: 380px;
    height: 540px;
    max-height: calc(100vh - 120px);
    background: #141419;
    border: 1px solid #2A2A35;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.55);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 9999;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    color: #fff;
    animation: vc-chat-slide-up 0.22s ease-out;
}
@keyframes vc-chat-slide-up {
    from { opacity: 0; transform: translateY(16px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.vc-chat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    background: #1C1C24;
    border-bottom: 1px solid #2A2A35;
}
.vc-chat-title { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 15px; color: #fff; }
.vc-chat-status { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #A0A0B0; margin-top: 2px; }
.vc-chat-status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.vc-chat-status-on  { background: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.18); }
.vc-chat-status-off { background: #6b7280; }
.vc-chat-close-btn {
    background: transparent; border: none; color: #A0A0B0; cursor: pointer;
    padding: 6px; border-radius: 6px; display: flex; align-items: center; transition: color 0.15s ease, background 0.15s ease;
}
.vc-chat-close-btn:hover { color: #fff; background: rgba(255, 255, 255, 0.06); }

.vc-chat-messages {
    flex: 1; overflow-y: auto; padding: 16px;
    display: flex; flex-direction: column; gap: 10px;
    scrollbar-width: thin; scrollbar-color: #2A2A35 transparent;
}
.vc-chat-messages::-webkit-scrollbar { width: 6px; }
.vc-chat-messages::-webkit-scrollbar-track { background: transparent; }
.vc-chat-messages::-webkit-scrollbar-thumb { background: #2A2A35; border-radius: 3px; }

.vc-chat-bubble {
    max-width: 80%;
    padding: 10px 13px;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.45;
    word-wrap: break-word;
    animation: vc-chat-bubble-in 0.18s ease-out;
}
@keyframes vc-chat-bubble-in { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
.vc-chat-bubble a { color: #FBBF24; text-decoration: underline; }
.vc-chat-bubble strong { font-weight: 600; color: #fff; }
.vc-chat-bubble-bot, .vc-chat-bubble-admin {
    align-self: flex-start;
    background: #1C1C24;
    color: #E5E5EE;
    border-bottom-left-radius: 4px;
    border: 1px solid #2A2A35;
}
.vc-chat-bubble-admin { border-color: rgba(245, 158, 11, 0.25); }
.vc-chat-bubble-user, .vc-chat-bubble-visiteur {
    align-self: flex-end;
    background: #F59E0B;
    color: #0B0B0F;
    border-bottom-right-radius: 4px;
    font-weight: 500;
}
.vc-chat-bubble-user a, .vc-chat-bubble-visiteur a { color: #0B0B0F; font-weight: 600; }

.vc-chat-typing {
    align-self: flex-start;
    background: #1C1C24;
    border: 1px solid #2A2A35;
    border-radius: 12px;
    border-bottom-left-radius: 4px;
    padding: 10px 14px;
    display: flex; gap: 4px;
}
.vc-chat-typing span {
    width: 6px; height: 6px; border-radius: 50%; background: #A0A0B0;
    animation: vc-chat-typing-bounce 1.2s infinite;
}
.vc-chat-typing span:nth-child(2) { animation-delay: 0.15s; }
.vc-chat-typing span:nth-child(3) { animation-delay: 0.3s; }
@keyframes vc-chat-typing-bounce {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30% { transform: translateY(-5px); opacity: 1; }
}

.vc-chat-quick { padding: 0 16px 8px; display: flex; flex-wrap: wrap; gap: 6px; }
.vc-chat-quick-btn {
    background: #1C1C24;
    color: #E5E5EE;
    border: 1px solid #2A2A35;
    padding: 7px 11px;
    border-radius: 999px;
    font-size: 12px;
    cursor: pointer;
    transition: border-color 0.15s ease, color 0.15s ease;
    font-family: inherit;
}
.vc-chat-quick-btn:hover { border-color: #F59E0B; color: #F59E0B; }

.vc-chat-input-bar {
    display: flex; align-items: center; gap: 8px;
    padding: 12px; border-top: 1px solid #2A2A35; background: #141419;
}
.vc-chat-input-bar input[type="text"] {
    flex: 1; background: #1C1C24; color: #fff; border: 1px solid #2A2A35;
    border-radius: 10px; padding: 9px 12px; font-size: 14px; outline: none;
    transition: border-color 0.15s ease;
}
.vc-chat-input-bar input[type="text"]:focus { border-color: #F59E0B; }
.vc-chat-input-bar input[type="text"]::placeholder { color: #6B6B7D; }
.vc-chat-input-bar button {
    background: #F59E0B; color: #0B0B0F; border: none;
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background 0.15s ease;
}
.vc-chat-input-bar button:hover { background: #FBBF24; }
.vc-chat-input-bar button:disabled { opacity: 0.5; cursor: not-allowed; }

.vc-chat-email-form {
    padding: 12px 16px;
    border-top: 1px solid #2A2A35;
    background: rgba(245, 158, 11, 0.06);
    display: flex; flex-direction: column; gap: 8px;
}
.vc-chat-email-label { font-size: 12px; color: #A0A0B0; margin: 0 0 2px; }
.vc-chat-email-form input {
    background: #141419; color: #fff; border: 1px solid #2A2A35;
    border-radius: 8px; padding: 8px 11px; font-size: 13px; outline: none; font-family: inherit;
}
.vc-chat-email-form input:focus { border-color: #F59E0B; }
.vc-chat-email-form button {
    background: #F59E0B; color: #0B0B0F; border: none;
    border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: inherit; transition: background 0.15s ease;
}
.vc-chat-email-form button:hover { background: #FBBF24; }

@media (max-width: 480px) {
    .vc-chat-panel {
        width: 100vw;
        height: 100vh;
        max-height: 100vh;
        bottom: 0;
        right: 0;
        border-radius: 0;
        border: none;
    }
    .vc-chat-toggle { bottom: 16px; right: 16px; }
}
</style>

<script>
(function () {
    'use strict';

    var root = document.getElementById('vc-chat-root');
    if (!root) return;

    var CSRF = root.dataset.csrf;
    var OFFICE_HOURS = root.dataset.officeHours === '1';
    var USER_PRENOM = root.dataset.userPrenom || '';
    var LOGGED_IN = root.dataset.loggedIn === '1';
    var STORAGE_SESSION_KEY = 'variable_chat_session';
    var STORAGE_CONV_KEY = 'variable_chat_conversation';
    var STORAGE_OPENED_KEY = 'variable_chat_opened_once';

    var btnToggle = document.getElementById('vc-chat-toggle');
    var iconOpen = document.getElementById('vc-chat-icon-open');
    var iconClose = document.getElementById('vc-chat-icon-close');
    var panel = document.getElementById('vc-chat-panel');
    var btnClose = document.getElementById('vc-chat-close');
    var messages = document.getElementById('vc-chat-messages');
    var quick = document.getElementById('vc-chat-quick');
    var form = document.getElementById('vc-chat-form');
    var input = document.getElementById('vc-chat-text');
    var sendBtn = document.getElementById('vc-chat-send');
    var emailForm = document.getElementById('vc-chat-email-form');
    var emailInput = document.getElementById('vc-chat-email-input');
    var nameInput = document.getElementById('vc-chat-name-input');
    var emailSubmit = document.getElementById('vc-chat-email-submit');

    var sessionId = getOrCreateSessionId();
    var conversationId = getStoredConversationId();
    var isSending = false;
    var historyLoaded = false;

    // ---------- Helpers session/storage ----------
    function getOrCreateSessionId() {
        var existing = localStorage.getItem(STORAGE_SESSION_KEY);
        if (existing && existing.length <= 64) return existing;
        var sid = generateId();
        localStorage.setItem(STORAGE_SESSION_KEY, sid);
        return sid;
    }
    function generateId() {
        if (window.crypto && crypto.getRandomValues) {
            var arr = new Uint8Array(24);
            crypto.getRandomValues(arr);
            return Array.from(arr).map(function (b) { return b.toString(16).padStart(2, '0'); }).join('');
        }
        return Math.random().toString(36).slice(2) + Date.now().toString(36);
    }
    function getStoredConversationId() {
        var v = localStorage.getItem(STORAGE_CONV_KEY);
        return v ? parseInt(v, 10) : null;
    }
    function setStoredConversationId(id) {
        if (id) localStorage.setItem(STORAGE_CONV_KEY, String(id));
    }

    // ---------- Rendering ----------
    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    // Le bot envoie du HTML simple (whitelist d'origine serveur).
    // Pour les visiteurs/users, on échappe systématiquement.
    function renderMessage(senderType, content, options) {
        options = options || {};
        var div = document.createElement('div');
        div.className = 'vc-chat-bubble vc-chat-bubble-' + senderType;
        if (options.isBot || options.isAdmin) {
            div.innerHTML = content;
        } else {
            div.textContent = content;
        }
        messages.appendChild(div);
        scrollToBottom();
    }
    function showTyping() {
        var t = document.createElement('div');
        t.className = 'vc-chat-typing';
        t.id = 'vc-chat-typing-indicator';
        t.innerHTML = '<span></span><span></span><span></span>';
        messages.appendChild(t);
        scrollToBottom();
    }
    function hideTyping() {
        var t = document.getElementById('vc-chat-typing-indicator');
        if (t) t.remove();
    }
    function scrollToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }
    function clearQuick() { quick.innerHTML = ''; }
    function showQuickReplies(items) {
        clearQuick();
        items.forEach(function (label) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'vc-chat-quick-btn';
            b.textContent = label;
            b.addEventListener('click', function () {
                input.value = label;
                form.dispatchEvent(new Event('submit'));
            });
            quick.appendChild(b);
        });
    }

    // ---------- Welcome ----------
    function showWelcome() {
        if (LOGGED_IN && USER_PRENOM) {
            renderMessage('bot', 'Salut <strong>' + escapeHtml(USER_PRENOM) + '</strong> ! 👋 Comment puis-je t\'aider aujourd\'hui ?', { isBot: true });
        } else {
            renderMessage('bot', 'Salut ! Je suis l\'assistant Variable. Pose-moi ta question, je peux répondre tout de suite à plein de choses ! 📚', { isBot: true });
        }
        showQuickReplies([
            'Comment fonctionne l\'abonnement ?',
            'Je veux publier mon livre',
            'Problème avec mon compte'
        ]);
    }

    // ---------- API calls ----------
    function apiSend(message) {
        var body = new URLSearchParams();
        body.set('message', message);
        body.set('session_id', sessionId);
        if (conversationId) body.set('conversation_id', String(conversationId));
        body.set('_csrf_token', CSRF);
        return fetch('/chat/send', {
            method: 'POST',
            headers: { 'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: body
        }).then(function (r) { return r.json(); });
    }
    function apiLeaveEmail(email, name) {
        var body = new URLSearchParams();
        body.set('session_id', sessionId);
        body.set('email', email);
        if (name) body.set('name', name);
        body.set('_csrf_token', CSRF);
        return fetch('/chat/leave-email', {
            method: 'POST',
            headers: { 'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: body
        }).then(function (r) { return r.json(); });
    }
    function apiLoadHistory() {
        if (!conversationId) return Promise.resolve(null);
        return fetch('/chat/conversation/' + conversationId + '?session_id=' + encodeURIComponent(sessionId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.ok ? r.json() : null; }).catch(function () { return null; });
    }

    // ---------- Send flow ----------
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (isSending) return;
        var msg = input.value.trim();
        if (!msg) return;

        isSending = true;
        sendBtn.disabled = true;
        input.value = '';
        clearQuick();
        renderMessage(LOGGED_IN ? 'user' : 'visiteur', msg, {});
        showTyping();

        apiSend(msg).then(function (data) {
            hideTyping();
            isSending = false;
            sendBtn.disabled = false;
            input.focus();

            if (!data || data.error) {
                renderMessage('bot', 'Oups, une erreur est survenue. Réessaie dans un instant.', { isBot: true });
                return;
            }

            if (data.conversation_id) {
                conversationId = data.conversation_id;
                setStoredConversationId(conversationId);
            }

            if (data.bot_message && data.bot_message.content) {
                setTimeout(function () {
                    renderMessage('bot', data.bot_message.content, { isBot: true });
                    if (data.ask_email && !LOGGED_IN) {
                        emailForm.hidden = false;
                        scrollToBottom();
                    }
                }, 350);
            }
        }).catch(function () {
            hideTyping();
            isSending = false;
            sendBtn.disabled = false;
            renderMessage('bot', 'Oups, problème de connexion. Vérifie ton internet et réessaie.', { isBot: true });
        });
    });

    emailSubmit.addEventListener('click', function () {
        var email = (emailInput.value || '').trim();
        var name = (nameInput.value || '').trim();
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            emailInput.focus();
            return;
        }
        emailSubmit.disabled = true;
        apiLeaveEmail(email, name).then(function (data) {
            emailSubmit.disabled = false;
            if (data && data.ok) {
                emailForm.hidden = true;
                emailInput.value = '';
                nameInput.value = '';
                renderMessage('bot', 'Merci ! On reviendra vers toi à <strong>' + escapeHtml(email) + '</strong>. ✅', { isBot: true });
            } else {
                renderMessage('bot', 'Désolé, l\'enregistrement a échoué. Vérifie ton email et réessaie.', { isBot: true });
            }
        }).catch(function () {
            emailSubmit.disabled = false;
        });
    });

    // ---------- Open / close ----------
    function openPanel() {
        panel.hidden = false;
        iconOpen.style.display = 'none';
        iconClose.style.display = 'block';
        if (!historyLoaded) {
            historyLoaded = true;
            apiLoadHistory().then(function (data) {
                if (data && data.ok && data.messages && data.messages.length > 0) {
                    data.messages.forEach(function (m) {
                        renderMessage(m.sender_type, m.content, {
                            isBot: m.sender_type === 'bot',
                            isAdmin: m.sender_type === 'admin'
                        });
                    });
                    if (!localStorage.getItem(STORAGE_OPENED_KEY)) {
                        localStorage.setItem(STORAGE_OPENED_KEY, '1');
                    }
                } else {
                    showWelcome();
                    localStorage.setItem(STORAGE_OPENED_KEY, '1');
                }
            });
        }
        setTimeout(function () { input.focus(); }, 200);
    }
    function closePanel() {
        panel.hidden = true;
        iconOpen.style.display = 'block';
        iconClose.style.display = 'none';
    }
    btnToggle.addEventListener('click', function () {
        panel.hidden ? openPanel() : closePanel();
    });
    btnClose.addEventListener('click', closePanel);

    // Fermer au Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hidden) closePanel();
    });
})();
</script>
