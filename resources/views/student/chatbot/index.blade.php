@extends('layouts.student')

@section('title', 'Library Chatbot')
@section('page-title', 'Library Chatbot')

@push('styles')
<style>
    * {
        box-sizing: border-box;
    }

    .chatbot-container {
        width: 100%;
        margin: 0;
        padding: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        overflow: hidden;
    }

    .chatbot-header {
        background: #ffffff;
        color: #2d3748;
        padding: 16px 24px;
        text-align: center;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 0;
        z-index: 10;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
        flex-shrink: 0;
    }

    .chatbot-header h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #1a202c;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .chatbot-header p {
        margin: 6px 0 0 0;
        font-size: 13px;
        color: #718096;
        font-weight: 400;
    }

    .chatbot-messages {
        flex: 1 1 auto;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0;
        background: #ffffff;
        position: relative;
        scroll-behavior: smooth;
        min-height: 0;
        height: 100%;
    }

    .chatbot-messages::-webkit-scrollbar {
        width: 6px;
    }

    .chatbot-messages::-webkit-scrollbar-track {
        background: transparent;
    }

    .chatbot-messages::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 10px;
    }

    .chatbot-messages::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }

    .message {
        display: flex;
        padding: 16px 24px;
        animation: fadeIn 0.3s ease-out;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        min-height: fit-content;
    }

    .message:last-child {
        border-bottom: none;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message.user {
        background: #f7fafc;
    }

    .message.bot {
        background: #ffffff;
    }

    .message-wrapper {
        display: flex;
        gap: 16px;
        max-width: 100%;
        width: 100%;
    }

    .message-avatar {
        width: 32px;
        height: 32px;
        border-radius: 4px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }

    .message.user .message-avatar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .message.bot .message-avatar {
        background: #e2e8f0;
        color: #4a5568;
    }

    .message-content {
        flex: 1;
        min-width: 0;
    }

    .message-bubble {
        word-wrap: break-word;
        position: relative;
        line-height: 1.7;
        font-size: 15px;
        color: #2d3748;
    }

    .message.user .message-bubble {
        color: #1a202c;
    }

    .message.bot .message-bubble {
        color: #2d3748;
    }

    .message-bubble strong {
        color: inherit;
        font-weight: 700;
    }

    .message-bubble pre {
        white-space: pre-wrap;
        font-family: inherit;
        margin: 0;
    }

    .quick-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0;
        padding: 16px 24px;
        background: #f7fafc;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
    }

    .quick-action-btn {
        padding: 8px 14px;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        color: #4a5568;
        transition: all 0.2s;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .quick-action-btn:hover {
        background: #edf2f7;
        border-color: rgba(0, 0, 0, 0.15);
        color: #2d3748;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .quick-action-btn:active {
        transform: translateY(0);
    }

    .chatbot-input-area {
        padding: 0;
        background: #ffffff;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
        flex-shrink: 0;
    }

    .input-group {
        display: flex;
        align-items: flex-end;
        gap: 0;
        padding: 16px 24px;
        background: #ffffff;
    }

    .input-wrapper {
        flex: 1;
        position: relative;
        display: flex;
        align-items: center;
        background: #f7fafc;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        padding: 12px 16px;
        transition: all 0.2s;
    }

    .input-wrapper:focus-within {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: #ffffff;
    }

    .input-group input {
        flex: 1;
        border: none;
        background: transparent;
        font-size: 15px;
        outline: none;
        resize: none;
        max-height: 200px;
        line-height: 1.5;
        color: #1a202c;
        font-family: inherit;
    }

    .input-group input::placeholder {
        color: #a0aec0;
    }

    .input-group button {
        margin-left: 12px;
        padding: 10px 16px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 80px;
    }

    .input-group button:hover {
        background: #5568d3;
    }

    .input-group button:active {
        transform: scale(0.98);
    }

    .input-group button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .typing-indicator {
        display: flex;
        gap: 4px;
        padding: 0;
        align-items: center;
    }

    .typing-indicator span {
        width: 8px;
        height: 8px;
        background: #a0aec0;
        border-radius: 50%;
        animation: typing 1.4s infinite;
    }

    .typing-indicator span:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-indicator span:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing {
        0%, 60%, 100% {
            transform: translateY(0);
            opacity: 0.5;
        }
        30% {
            transform: translateY(-8px);
            opacity: 1;
        }
    }

    .welcome-message {
        padding: 40px 24px;
        color: #4a5568;
        animation: fadeIn 0.4s ease-out;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .welcome-message h5 {
        color: #1a202c;
        margin: 0 0 12px 0;
        font-size: 20px;
        font-weight: 600;
    }

    .welcome-message p {
        font-size: 15px;
        color: #718096;
        margin: 0 0 32px 0;
    }

    .welcome-message .feature-list {
        text-align: left;
        max-width: 600px;
        margin: 0 auto;
        background: #f7fafc;
        padding: 24px;
        border-radius: 12px;
        border: 1px solid rgba(0, 0, 0, 0.08);
    }

    .welcome-message .feature-list h6 {
        color: #1a202c;
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 16px 0;
        text-align: center;
    }

    .feature-list ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .feature-list li {
        margin: 10px 0;
        padding-left: 24px;
        position: relative;
        font-size: 14px;
        color: #4a5568;
        line-height: 1.6;
    }

    .feature-list li:before {
        content: "•";
        position: absolute;
        left: 8px;
        color: #667eea;
        font-weight: bold;
        font-size: 18px;
    }

    .book-info-card {
        background: #f7fafc;
        border-left: 3px solid #667eea;
        padding: 12px 16px;
        margin-top: 12px;
        border-radius: 8px;
    }

    .book-info-card strong {
        color: #1a202c;
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 600;
    }

    .book-info-card a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        font-size: 13px;
        transition: color 0.2s;
        display: inline-block;
        margin-right: 12px;
    }

    .book-info-card a:hover {
        color: #5568d3;
        text-decoration: underline;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .chatbot-container {
            height: calc(100vh - 80px) !important;
        }

        .chatbot-header {
            padding: 16px 20px;
        }

        .chatbot-header h4 {
            font-size: 16px;
        }

        .message {
            padding: 16px;
        }

        .message-wrapper {
            gap: 12px;
        }

        .message-avatar {
            width: 28px;
            height: 28px;
            font-size: 12px;
        }

        .message-bubble {
            font-size: 14px;
        }

        .input-group {
            padding: 12px 16px;
        }

        .quick-actions {
            padding: 12px 16px;
        }

        .quick-action-btn {
            padding: 6px 12px;
            font-size: 12px;
        }

        .welcome-message {
            padding: 32px 16px;
        }
    }
</style>
@endpush

@section('content')
<div class="chatbot-container" style="height: calc(100vh - 120px); margin: 0; padding: 0;">
    <div class="chatbot-header">
        <h4>Library Assistant</h4>
        <p>Ask me anything about library services</p>
    </div>

    <div class="chatbot-messages" id="chatbotMessages">
        <div class="welcome-message">
            <h5>👋 Welcome to Library Chatbot!</h5>
            <p>I'm here to help you with all your library needs.</p>
            <div class="feature-list">
                <h6 style="margin-bottom: 15px; color: #333;">I can help you with:</h6>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li>📚 My issued books count</li>
                    <li>🔍 Check book availability (by title, author, ISBN)</li>
                    <li>📖 Guide on issue/return process</li>
                    <li>📋 Check reservation status</li>
                    <li>💰 View overdue fines information & Online payments</li>
                    <li>💻 E-resource access guidance</li>
                    <li>🎓 Course-specific book recommendations (LMS)</li>
                    <li>❓ FAQs about library rules, timings, membership</li>
                </ul>
            </div>
            <p style="margin-top: 20px;">Type your question below or use the quick actions!</p>
        </div>
    </div>

    <div class="chatbot-input-area">
        <div class="quick-actions mb-3">
            <button class="quick-action-btn" onclick="sendQuickMessage('My issued books')">
                <span>📚 My Books</span>
            </button>
            <button class="quick-action-btn" onclick="sendQuickMessage('Check book availability')">
                <span>🔍 Book Availability</span>
            </button>
            <button class="quick-action-btn" onclick="sendQuickMessage('How to issue a book?')">
                <span>📖 Issue Process</span>
            </button>
            <button class="quick-action-btn" onclick="sendQuickMessage('My reservations')">
                <span>📋 My Reservations</span>
            </button>
            <button class="quick-action-btn" onclick="sendQuickMessage('My fines')">
                <span>💰 My Fines</span>
            </button>
            <button class="quick-action-btn" onclick="sendQuickMessage('Library timings')">
                <span>🕐 Library Timings</span>
            </button>
        </div>
        <div class="input-group">
            <div class="input-wrapper">
                <input type="text" id="messageInput" placeholder="Message Library Assistant..." onkeypress="handleKeyPress(event)">
            </div>
            <button type="button" id="sendBtn" onclick="sendMessage()">
                Send
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function handleKeyPress(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }

    function sendQuickMessage(message) {
        document.getElementById('messageInput').value = message;
        sendMessage();
    }

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        
        if (!message) return;
        
        // Add user message to chat
        addMessage(message, 'user');
        
        // Clear input
        input.value = '';
        
        // Disable send button
        const sendBtn = document.getElementById('sendBtn');
        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending...';
        
        // Show typing indicator
        showTypingIndicator();
        
        // Send request
        fetch('{{ route("student.chatbot.query") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            hideTypingIndicator();
            
            if (data.success) {
                addMessage(data.response, 'bot', data.type, data.data);
            } else {
                addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            }
        })
        .catch(error => {
            hideTypingIndicator();
            addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            console.error('Error:', error);
        })
        .finally(() => {
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send';
            input.focus();
        });
    }

    function addMessage(text, sender, type = 'text', data = null) {
        const messagesContainer = document.getElementById('chatbotMessages');
        
        // Remove welcome message if exists
        const welcomeMsg = messagesContainer.querySelector('.welcome-message');
        if (welcomeMsg) {
            welcomeMsg.remove();
        }
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;
        
        const messageWrapper = document.createElement('div');
        messageWrapper.className = 'message-wrapper';
        
        // Avatar
        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        if (sender === 'user') {
            avatar.textContent = '{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}';
        } else {
            avatar.textContent = 'AI';
        }
        
        // Content wrapper
        const content = document.createElement('div');
        content.className = 'message-content';
        
        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';
        
        // Format message text (convert markdown-like formatting)
        let formattedText = text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');
        
        bubble.innerHTML = formattedText;
        
        // Add book info card if available
        if (type === 'book_info' && data) {
            const card = document.createElement('div');
            card.className = 'book-info-card';
            const bookUrl = `{{ url('/student/books') }}/${data.book_id}`;
            const booksIndexUrl = `{{ route('student.books.index') }}`;
            card.innerHTML = `
                <strong>Quick Actions:</strong>
                <a href="${bookUrl}" target="_blank">View Book Details</a>
                <a href="${booksIndexUrl}" target="_blank">Browse All Books</a>
            `;
            bubble.appendChild(card);
        }
        
        content.appendChild(bubble);
        messageWrapper.appendChild(avatar);
        messageWrapper.appendChild(content);
        messageDiv.appendChild(messageWrapper);
        messagesContainer.appendChild(messageDiv);
        
        // Scroll to bottom
        setTimeout(() => {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }, 100);
    }

    function showTypingIndicator() {
        const messagesContainer = document.getElementById('chatbotMessages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message bot';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = `
            <div class="message-wrapper">
                <div class="message-avatar">AI</div>
                <div class="message-content">
                    <div class="message-bubble">
                        <div class="typing-indicator">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function hideTypingIndicator() {
        const typingIndicator = document.getElementById('typingIndicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    // Focus input on load
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('messageInput').focus();
    });
</script>
@endpush

