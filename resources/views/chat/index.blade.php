@extends('layouts.' . (auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isStaff() ? 'staff' : 'student')))

@section('title', 'Chat')
@section('page-title', 'Chat')

@push('styles')
<style>
    /* Ensure Font Awesome icons display properly */
    .fas, .far, .fab, .fal {
        font-family: "Font Awesome 6 Free" !important;
        font-weight: 900 !important;
        display: inline-block !important;
        font-style: normal !important;
        font-variant: normal !important;
        text-rendering: auto !important;
        line-height: 1 !important;
    }
    .far {
        font-weight: 400 !important;
    }
</style>
@endpush

@section('content')
<div class="chat-container" style="height: calc(100vh - 200px); max-width: 1400px; margin: 0 auto;">
    <div class="row g-0 h-100">
        <!-- Conversations List (Left Sidebar) - WhatsApp Style -->
        <div class="col-12 col-md-4 border-end conversations-sidebar" style="height: 100%; overflow-y: auto; position: relative; background: #ffffff;">
            <div class="d-flex flex-column h-100">
                <!-- Header - WhatsApp Style -->
                <div class="p-3 border-bottom" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 10px 16px !important;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-semibold" style="color: white; font-size: 18px; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                            Chats
                        </h5>
                        <button class="btn btn-sm" id="newChatBtn" data-bs-toggle="modal" data-bs-target="#newChatModal" style="background: transparent; border: none; color: white; font-size: 20px; padding: 4px 8px; cursor: pointer;">
                            <i class="fas fa-edit" style="display: inline-block;"></i>
                        </button>
                    </div>
                    <div class="input-group" style="background: white; border-radius: 8px; overflow: hidden; height: 36px;">
                        <span class="input-group-text bg-white border-0" style="padding: 8px 12px;">
                            <i class="fas fa-search" style="color: #667781; font-size: 14px; display: inline-block;"></i>
                        </span>
                        <input type="text" class="form-control border-0" id="searchConversations" placeholder="Search or start new chat" style="background: white; font-size: 14px; padding: 8px 0;">
                    </div>
                </div>

                <!-- Conversations List -->
                <div class="flex-grow-1 overflow-auto" id="conversationsList">
                    @if(count($conversations) > 0)
                        @foreach($conversations as $conversation)
                        <div class="conversation-item p-3 cursor-pointer" 
                             data-conversation-id="{{ $conversation['id'] }}"
                             data-user-id="{{ $conversation['other_user']['id'] }}"
                             style="cursor: pointer; transition: background 0.2s; border-bottom: 1px solid #e9edef; padding: 10px 16px !important;">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3" style="width: 49px; height: 49px; border-radius: 50%; background: #dfe5e7; display: flex; align-items: center; justify-content: center; color: #54656f; font-weight: 500; font-size: 20px; flex-shrink: 0; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                    {{ strtoupper(substr($conversation['other_user']['name'], 0, 1)) }}
                                </div>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div style="flex: 1; min-width: 0;">
                                            <h6 class="mb-0 fw-normal" style="font-size: 17px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #111b21; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif; font-weight: 400;">{{ $conversation['other_user']['name'] }}</h6>
                                        </div>
                                        <div class="d-flex align-items-center gap-2" style="flex-shrink: 0; margin-left: 8px;">
                                            @if($conversation['last_message'])
                                            <small class="text-muted" style="font-size: 12px; color: #667781; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                                {{ \Carbon\Carbon::parse($conversation['last_message']['created_at'])->format('h:i A') }}
                                            </small>
                                            @endif
                                            @if($conversation['unread_count'] > 0)
                                            <span class="badge rounded-pill" style="background: #25d366; color: white; font-size: 12px; min-width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-weight: 500; padding: 0 6px;">{{ $conversation['unread_count'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        @if($conversation['last_message'])
                                        <p class="mb-0 text-muted" style="font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; color: #667781; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                            {{ Str::limit($conversation['last_message']['message'], 30) }}
                                        </p>
                                        @else
                                        <p class="mb-0 text-muted" style="font-size: 14px; font-style: italic; color: #667781; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif;">No messages yet</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center p-5 text-muted">
                            <i class="fas fa-comments fa-3x mb-3"></i>
                            <p>No conversations yet. Start a new chat!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Chat Area (Right Side) -->
        <div class="col-12 col-md-8 d-flex flex-column chat-area" id="chatArea" style="height: 100%; background: #f0f2f5; position: relative;">
            <div class="text-center p-5 text-muted d-flex flex-column align-items-center justify-content-center" id="emptyChatState" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; height: 100%; width: 100%; background: #f0f2f5; z-index: 1; padding: 40px 20px;">
                <!-- Simple Welcome Screen -->
                <div style="max-width: 600px; margin: 0 auto;">
                    <!-- Title -->
                    <h2 style="color: #111b21; font-weight: 300; font-size: 36px; margin-bottom: 15px; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif; letter-spacing: -0.5px;">
                        <strong style="font-weight: 500;">Welcome to Chat</strong>
                    </h2>
                    
                    <!-- Subtitle -->
                    <p style="color: #667781; font-size: 16px; line-height: 1.6; margin-bottom: 50px; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                        Select a conversation from the list to start chatting
                    </p>
                    
                    <!-- Icons Section -->
                    <div style="display: flex; justify-content: center; align-items: center; gap: 60px; margin-bottom: 40px; flex-wrap: wrap;">
                        <!-- Connect Icon -->
                        <div style="text-align: center;">
                            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                                <i class="fas fa-users" style="font-size: 36px; color: white; display: inline-block;"></i>
                            </div>
                            <p style="color: #111b21; font-size: 14px; font-weight: 500; margin: 0; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif;">Connect</p>
                        </div>
                        
                        <!-- Chat Icon -->
                        <div style="text-align: center;">
                            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                                <i class="fas fa-comments" style="font-size: 36px; color: white; display: inline-block;"></i>
                            </div>
                            <p style="color: #111b21; font-size: 14px; font-weight: 500; margin: 0; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif;">Chat</p>
                        </div>
                        
                        <!-- Secure Icon -->
                        <div style="text-align: center;">
                            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                                <i class="fas fa-shield-alt" style="font-size: 36px; color: white; display: inline-block;"></i>
                            </div>
                            <p style="color: #111b21; font-size: 14px; font-weight: 500; margin: 0; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif;">Secure</p>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <p style="color: #667781; font-size: 14px; line-height: 1.6; margin-bottom: 30px; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif; max-width: 500px; margin-left: auto; margin-right: auto;">
                        Send and receive messages securely. Connect with your team members and manage your library communications all in one place.
                    </p>
                </div>
            </div>

            <!-- Active Chat (Hidden by default - only shows when conversation is selected) -->
            <div id="activeChat" style="display: none !important; visibility: hidden !important; height: 100%; width: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 2;" class="d-flex flex-column">
                <!-- Chat Header - WhatsApp Style -->
                <div class="p-3 chat-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 10px 16px !important; border-bottom: 1px solid rgba(0,0,0,0.08);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm me-2 back-to-conversations" onclick="showConversationsList()" style="background: transparent; border: none; color: white; font-size: 20px; display: none; padding: 8px;">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <div class="avatar me-3" id="chatAvatar" style="width: 40px; height: 40px; border-radius: 50%; background: #dfe5e7; display: flex; align-items: center; justify-content: center; color: #54656f; font-weight: 500; font-size: 18px;">
                                U
                            </div>
                            <div>
                                <h6 class="mb-0 fw-normal" id="chatUserName" style="color: white; font-size: 16px; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif; font-weight: 400;">User Name</h6>
                                <small class="text-muted" id="chatUserRole" style="color: rgba(255,255,255,0.7); font-size: 13px; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif;">Role</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" id="selectModeBtn" onclick="toggleSelectMode()" style="display: none; border: none; background: transparent;">
                                <i class="fas fa-check-square"></i>
                            </button>
                            <div id="selectionActions" style="display: none; align-items: center; gap: 8px;">
                                <button class="btn btn-sm btn-danger" onclick="deleteSelectedMessages()" style="border-radius: 20px; padding: 6px 12px; display: flex; align-items: center; gap: 6px; background: #dc3545; border: none; color: white;">
                                    <i class="fas fa-trash" style="font-size: 14px; display: inline-block;"></i> 
                                    <span>Delete (<span id="selectedCount">0</span>)</span>
                                </button>
                                <button class="btn btn-sm btn-secondary" onclick="cancelSelection()" style="border-radius: 20px; padding: 6px 12px; display: flex; align-items: center; gap: 6px; background: #6c757d; border: none; color: white;">
                                    <i class="fas fa-times" style="font-size: 14px; display: inline-block;"></i> 
                                    <span>Cancel</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-grow-1 p-3 overflow-auto" id="messagesContainer" style="background: #efeae2; background-image: url('data:image/svg+xml,%3Csvg width=\"100\" height=\"100\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cdefs%3E%3Cpattern id=\"grid\" width=\"100\" height=\"100\" patternUnits=\"userSpaceOnUse\"%3E%3Cpath d=\"M 100 0 L 0 0 0 100\" fill=\"none\" stroke=\"%23e0e0e0\" stroke-width=\"1\"/%3E%3C/pattern%3E%3C/defs%3E%3Crect width=\"100\" height=\"100\" fill=\"url(%23grid)\" opacity=\"0.1\"/%3E%3C/svg%3E'); padding: 16px !important;">
                    <div id="messagesList"></div>
                </div>

                <!-- Message Input - WhatsApp Style -->
                <div class="p-3 message-input-area" style="background: #f0f2f5; padding: 8px 16px !important; border-top: 1px solid rgba(0,0,0,0.08);">
                    <form id="messageForm" class="d-flex gap-2 align-items-center">
                        <input type="hidden" id="currentConversationId" value="">
                        <input type="hidden" id="editingMessageId" value="">
                        <div class="flex-grow-1 position-relative" style="background: white; border-radius: 24px; padding: 4px 8px; display: flex; align-items: center;">
                            <button type="button" class="btn btn-sm" id="emojiBtn" onclick="toggleEmojiPicker()" style="background: transparent; border: none; color: #54656f; padding: 8px 12px; cursor: pointer;">
                                <i class="fas fa-smile" style="font-size: 20px; display: inline-block;"></i>
                            </button>
                            <!-- Emoji Picker -->
                            <div id="emojiPicker" style="position: absolute; bottom: 100%; left: 0; margin-bottom: 10px; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); z-index: 1000; display: none; width: 350px; max-height: 400px; overflow-y: auto; padding: 10px;">
                                <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 5px;">
                                    <!-- Common Emojis -->
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😀')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😀</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😂')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😂</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😊')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😊</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😍')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😍</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🤔')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🤔</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😎')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😎</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😢')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😢</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😮')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😮</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('👍')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">👍</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('👎')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">👎</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('❤️')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">❤️</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🔥')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🔥</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🎉')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🎉</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('✅')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">✅</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('❌')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">❌</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🙏')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🙏</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😴')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😴</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😋')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😋</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🤗')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🤗</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😇')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😇</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🥳')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🥳</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😅')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😅</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🤣')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🤣</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😘')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😘</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🥰')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🥰</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😉')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😉</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🙄')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🙄</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('😏')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">😏</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🤝')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🤝</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('💪')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">💪</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('👏')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">👏</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🎊')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🎊</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('⭐')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">⭐</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('💯')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">💯</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🚀')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🚀</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('💡')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">💡</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🎯')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🎯</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('✨')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">✨</button>
                                    <button type="button" class="emoji-btn" onclick="insertEmoji('🎁')" style="background: transparent; border: none; font-size: 24px; padding: 5px; cursor: pointer; border-radius: 4px; transition: background 0.2s;">🎁</button>
                                </div>
                            </div>
                            <input type="text" 
                                   class="form-control border-0" 
                                   id="messageInput" 
                                   placeholder="Type a message" 
                                   autocomplete="off"
                                   maxlength="5000"
                                   style="background: transparent; border: none; box-shadow: none; font-size: 15px; font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 8px 4px;">
                            <button type="button" class="btn btn-sm" style="background: transparent; border: none; color: #54656f; padding: 8px 12px;">
                                <i class="fas fa-paperclip" style="font-size: 20px;"></i>
                            </button>
                            <button type="button" class="btn btn-sm position-absolute" id="cancelEditBtn" onclick="cancelEdit()" style="display: none; right: 50px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #54656f; padding: 4px 8px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <button type="submit" class="btn send-button" id="sendBtn" style="background: #25d366; border: none; border-radius: 50%; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: none;">
                            <i class="fas fa-paper-plane" style="font-size: 18px;"></i>
                        </button>
                    </form>
                    <div class="text-center mt-2" id="editingIndicator" style="display: none;">
                        <small class="text-muted"><i class="fas fa-edit"></i> Editing message...</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Chat Modal -->
<div class="modal fade" id="newChatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Start New Chat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control" id="searchUsers" placeholder="Search users...">
                </div>
                <div id="usersList" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center p-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .chat-container {
            height: 100vh !important;
            margin: 0 !important;
            max-width: 100% !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        
        .chat-container .row {
            height: 100% !important;
            margin: 0 !important;
            overflow: hidden !important;
        }
        
        .conversations-sidebar {
            position: fixed !important;
            top: 0;
            left: 0;
            width: 100% !important;
            height: 100vh !important;
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }
        
        .conversations-sidebar.hidden {
            transform: translateX(-100%);
        }
        
        .chat-area {
            width: 100% !important;
            height: 100vh !important;
            position: fixed !important;
            top: 0;
            left: 0;
            z-index: 999;
            display: none;
        }
        
        .chat-area.active {
            display: flex !important;
        }
        
        .message-bubble {
            max-width: 85% !important;
        }
        
        /* Back button for mobile */
        .back-to-conversations {
            display: block !important;
        }
        
        /* Prevent body scroll on mobile */
        body {
            overflow: hidden !important;
        }
    }
    
    @media (min-width: 769px) {
        .conversations-sidebar.hidden {
            transform: none;
        }
        .back-to-conversations {
            display: none !important;
        }
    }
    
    /* WhatsApp-like Chat Styles */
    * {
        font-family: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif !important;
    }
    
    .conversation-item.active {
        background-color: #f0f2f5 !important;
    }
    .conversation-item:hover {
        background-color: #f5f6f6 !important;
    }
    
    .chat-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
    
    .back-to-conversations {
        display: none;
    }
    
    /* Message Bubbles - WhatsApp Style */
    .message-bubble {
        max-width: 65%;
        padding: 6px 9px;
        border-radius: 7.5px;
        margin-bottom: 1px;
        word-wrap: break-word;
        position: relative;
        box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
        font-size: 14px;
        line-height: 1.4;
    }
    
    .message-bubble.sent {
        background-color: #d9fdd3;
        margin-left: auto;
        margin-right: 0;
        border-bottom-right-radius: 0;
        box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
    }
    
    .message-bubble.received {
        background-color: #ffffff;
        margin-right: auto;
        margin-left: 0;
        border-bottom-left-radius: 0;
        box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
    }
    
    /* Message Time and Status */
    .message-time {
        font-size: 11px;
        color: rgba(0,0,0,0.45);
        margin-top: 4px;
        display: inline-block;
    }
    
    .message-bubble.sent .message-time {
        color: rgba(0,0,0,0.45);
    }
    
    .message-bubble.received .message-time {
        color: rgba(0,0,0,0.45);
    }
    
    /* Read Receipt Icons - WhatsApp Style */
    .read-receipt {
        font-size: 16px;
        margin-left: 4px;
        display: inline-block;
    }
    
    .read-receipt.single {
        color: #8696a0;
    }
    
    .read-receipt.double {
        color: #8696a0;
    }
    
    .read-receipt.double.read {
        color: #53bdeb;
    }
    
    /* Edited Label */
    .message-edited {
        font-size: 11px;
        color: rgba(0,0,0,0.45);
        font-style: italic;
        margin-left: 4px;
    }
    
    /* Messages Container - WhatsApp Background */
    #messagesContainer {
        scroll-behavior: smooth;
        background-color: #efeae2;
        background-image: url('data:image/svg+xml,%3Csvg width=\"100\" height=\"100\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cdefs%3E%3Cpattern id=\"grid\" width=\"100\" height=\"100\" patternUnits=\"userSpaceOnUse\"%3E%3Cpath d=\"M 100 0 L 0 0 0 100\" fill=\"none\" stroke=\"%23e0e0e0\" stroke-width=\"1\"/%3E%3C/pattern%3E%3C/defs%3E%3Crect width=\"100\" height=\"100\" fill=\"url(%23grid)\" opacity=\"0.1\"/%3E%3C/svg%3E');
        padding: 16px !important;
    }
    
    /* Message Actions - Show on hover */
    .message-bubble {
        position: relative;
    }
    
    .message-bubble:hover .message-actions {
        opacity: 1;
    }
    
    .message-actions {
        opacity: 0;
        transition: opacity 0.2s;
        display: flex;
        gap: 4px;
        flex-shrink: 0;
        align-items: center;
    }
    
    .message-bubble:hover .message-actions {
        opacity: 1;
    }
    
    .message-actions button {
        padding: 4px;
        border: none;
        background: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .message-actions button:hover {
        transform: scale(1.1);
    }
    
    .message-actions button:hover {
        background: #f0f0f0;
    }
    
    /* Selection Mode */
    .message-checkbox {
        position: absolute;
        top: 8px;
        left: 8px;
        opacity: 0;
        transition: opacity 0.2s;
        z-index: 10;
    }
    
    .selection-mode .message-checkbox {
        opacity: 1;
    }
    
    .selection-mode .message-actions {
        display: none;
    }
    
    /* Chat Header - WhatsApp Style */
    .chat-header {
        background: #f0f2f5;
        border-bottom: 1px solid #e4e6eb;
    }
    
    /* Input Area - WhatsApp Style */
    .message-input-area {
        background: #f0f2f5;
        border-top: 1px solid #e4e6eb;
    }
    
    .message-input-area .form-control {
        background: white;
        border: none;
        border-radius: 21px;
        padding: 9px 20px;
    }
    
    .message-input-area .form-control:focus {
        box-shadow: none;
        border: none;
    }
    
    .send-button {
        background: #25d366;
        border: none;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    .send-button:hover {
        background: #20ba5a;
        color: white;
    }
    
    /* Conversation List - WhatsApp Style */
    .conversation-item {
        border-bottom: 1px solid #e4e6eb;
        transition: background 0.2s;
    }
    
    /* Avatar Styles */
    .avatar {
        flex-shrink: 0;
    }
    
    /* Emoji Picker Styles */
    .emoji-btn:hover {
        background: #f0f0f0 !important;
    }
    
    #emojiPicker {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
    }
    
    #emojiPicker::-webkit-scrollbar {
        width: 6px;
    }
    
    #emojiPicker::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 10px;
    }
    
    #emojiPicker::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 10px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
<script>
    let currentConversationId = null;
    let currentUserId = {{ auth()->id() }};
    let echo = null;

    // Initialize Laravel Echo for real-time updates
    document.addEventListener('DOMContentLoaded', function() {
        // Show empty state on initial load (WhatsApp Web welcome screen)
        showEmptyState();
        
        // Load chattable users
        loadChattableUsers();

        // Setup conversation click handlers
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', function() {
                const conversationId = this.dataset.conversationId;
                const userId = this.dataset.userId;
                if (conversationId && userId) {
                    loadConversation(conversationId, userId);
                    // Hide sidebar on mobile when conversation is clicked
                    hideConversationsList();
                }
            });
        });
        
        // Check if conversation ID is in URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const conversationParam = urlParams.get('conversation');
        if (conversationParam) {
            // Find the conversation item and click it
            const conversationItem = document.querySelector(`[data-conversation-id="${conversationParam}"]`);
            if (conversationItem) {
                conversationItem.click();
            } else {
                // If conversation not in list, load it directly
                setTimeout(() => {
                    const item = document.querySelector(`[data-conversation-id="${conversationParam}"]`);
                    if (item) {
                        item.click();
                    } else {
                        showEmptyState();
                    }
                }, 500);
            }
        } else {
            // No conversation in URL, ensure empty state is shown
            showEmptyState();
        }

        // Message form submission
        document.getElementById('messageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessage();
        });

        // Search conversations
        document.getElementById('searchConversations').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.conversation-item').forEach(item => {
                const userName = item.querySelector('h6').textContent.toLowerCase();
                if (userName.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Initialize Echo (if broadcasting is configured)
        // Note: For production, configure Pusher or Laravel Reverb in .env
        // For now, we'll use polling as fallback
        const pusherKey = '{{ config("broadcasting.connections.pusher.key", "") }}';
        if (pusherKey && typeof Echo !== 'undefined') {
            try {
                echo = new Echo({
                    broadcaster: 'pusher',
                    key: pusherKey,
                    cluster: '{{ config("broadcasting.connections.pusher.options.cluster", "mt1") }}',
                    forceTLS: true,
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    }
                });
                console.log('Echo initialized with WebSockets');
            } catch (e) {
                console.log('Echo initialization failed, using polling instead:', e);
            }
        } else {
            console.log('Broadcasting not configured, using polling for real-time updates');
        }
    });

    function loadChattableUsers() {
        fetch('{{ route("chat.users") }}')
            .then(response => response.json())
            .then(users => {
                const usersList = document.getElementById('usersList');
                if (users.length === 0) {
                    usersList.innerHTML = '<div class="text-center p-3 text-muted">No users available to chat</div>';
                    return;
                }
                
                usersList.innerHTML = users.map(user => `
                    <div class="user-item p-3 border-bottom cursor-pointer" 
                         data-user-id="${user.id}"
                         style="cursor: pointer; transition: background 0.2s;"
                         onmouseover="this.style.background='#f8f9fa'" 
                         onmouseout="this.style.background='white'">
                        <div class="d-flex align-items-center">
                            <div class="avatar me-3" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                ${user.name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <h6 class="mb-0">${user.name}</h6>
                                <small class="text-muted">${user.email} • ${user.role}</small>
                            </div>
                        </div>
                    </div>
                `).join('');

                // Add click handlers
                document.querySelectorAll('.user-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const userId = this.dataset.userId;
                        startNewConversation(userId);
                        bootstrap.Modal.getInstance(document.getElementById('newChatModal')).hide();
                    });
                });
            })
            .catch(error => {
                console.error('Error loading users:', error);
            });
    }

    function startNewConversation(userId) {
        fetch(`{{ url('/chat/conversation') }}/${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                loadConversation(data.conversation_id, userId);
            })
            .catch(error => {
                console.error('Error starting conversation:', error);
            });
    }

    // Show empty state (WhatsApp Web welcome screen)
    function showEmptyState() {
        const emptyState = document.getElementById('emptyChatState');
        const activeChat = document.getElementById('activeChat');
        
        // Ensure empty state is visible
        if (emptyState) {
            emptyState.style.display = 'flex';
            emptyState.style.visibility = 'visible';
            emptyState.style.opacity = '1';
        }
        
        // Ensure active chat is completely hidden
        if (activeChat) {
            activeChat.style.display = 'none';
            activeChat.style.visibility = 'hidden';
        }
        
        // Reset current conversation
        currentConversationId = null;
        const currentConvInput = document.getElementById('currentConversationId');
        if (currentConvInput) {
            currentConvInput.value = '';
        }
        
        // Clear messages
        const messagesList = document.getElementById('messagesList');
        if (messagesList) {
            messagesList.innerHTML = '';
        }
        
        // Hide back button
        const backBtn = document.querySelector('.back-to-conversations');
        if (backBtn) {
            backBtn.style.display = 'none';
        }
        
        // Hide message input form
        const messageForm = document.getElementById('messageForm');
        if (messageForm) {
            messageForm.style.display = 'none';
        }
        
        // Remove active state from all conversation items
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.remove('active');
        });
    }
    
    // Mobile view functions
    function showConversationsList() {
        const sidebar = document.querySelector('.conversations-sidebar');
        const chatArea = document.getElementById('chatArea');
        if (sidebar) {
            sidebar.classList.remove('hidden');
        }
        if (chatArea) {
            chatArea.classList.remove('active');
        }
        showEmptyState();
    }
    
    function hideConversationsList() {
        if (window.innerWidth <= 768) {
            const sidebar = document.querySelector('.conversations-sidebar');
            const chatArea = document.getElementById('chatArea');
            if (sidebar) {
                sidebar.classList.add('hidden');
            }
            if (chatArea) {
                chatArea.classList.add('active');
            }
        }
    }

    function loadConversation(conversationId, userId) {
        if (!conversationId) {
            // If no conversation ID, show empty state
            showEmptyState();
            return;
        }
        
        currentConversationId = conversationId;
        document.getElementById('currentConversationId').value = conversationId;

        // Hide conversations list on mobile
        hideConversationsList();

        // Update active state
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.conversationId == conversationId) {
                item.classList.add('active');
            }
        });

        // Hide empty state and show active chat
        const emptyState = document.getElementById('emptyChatState');
        const activeChat = document.getElementById('activeChat');
        
        // Hide empty state completely
        if (emptyState) {
            emptyState.style.display = 'none';
            emptyState.style.visibility = 'hidden';
            emptyState.style.opacity = '0';
        }
        
        // Show active chat
        if (activeChat) {
            activeChat.style.display = 'flex';
            activeChat.style.visibility = 'visible';
            activeChat.style.opacity = '1';
        }
        
        // Show message input form
        const messageForm = document.getElementById('messageForm');
        if (messageForm) {
            messageForm.style.display = 'flex';
        }
        
        // Show back button on mobile
        const backBtn = document.querySelector('.back-to-conversations');
        if (backBtn && window.innerWidth <= 768) {
            backBtn.style.display = 'block';
        }

        // Load messages
        loadMessages(conversationId);

        // Setup real-time listener
        if (echo) {
            echo.private(`chat.${conversationId}`)
                .listen('.message.sent', (e) => {
                    if (e.message.sender_id !== currentUserId) {
                        appendMessage(e.message);
                    } else {
                        // Update read receipt for sent message
                        updateMessageReadReceipt(e.message.id, e.message.delivery_status || 'sent');
                    }
                });
        } else {
            // Fallback: Poll for new messages every 2 seconds
            if (window.messagePollInterval) {
                clearInterval(window.messagePollInterval);
            }
            window.messagePollInterval = setInterval(() => {
                loadMessages(conversationId, true);
            }, 2000);
        }
    }

    function loadMessages(conversationId, silent = false) {
        fetch(`{{ url('/chat/messages') }}/${conversationId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                // Update chat header
                document.getElementById('chatUserName').textContent = data.other_user.name;
                document.getElementById('chatUserRole').textContent = data.other_user.role;
                document.getElementById('chatAvatar').textContent = data.other_user.name.charAt(0).toUpperCase();

                // Update messages
                if (!silent) {
                    const messagesList = document.getElementById('messagesList');
                    messagesList.innerHTML = data.messages.map(msg => createMessageHTML(msg)).join('');
                    scrollToBottom();
                    
                    // Show select mode button if there are messages
                    if (data.messages.length > 0) {
                        document.getElementById('selectModeBtn').style.display = 'block';
                        document.getElementById('selectModeBtnSidebar').style.display = 'block';
                    }
                    
                    // Update chat unread badge if unread_count is provided
                    if (data.unread_count !== undefined) {
                        updateChatUnreadBadge(data.unread_count);
                    }
                } else {
                    // Only append new messages and update read receipts
                    const lastMessageId = document.querySelector('.message-bubble:last-child')?.dataset.messageId;
                    const newMessages = data.messages.filter(msg => !lastMessageId || msg.id > lastMessageId);
                    newMessages.forEach(msg => {
                        appendMessage(msg);
                    });
                    
                    // Update read receipts for existing messages
                    data.messages.forEach(msg => {
                        if (msg.sender_id == currentUserId) {
                            updateMessageReadReceipt(msg.id, msg.delivery_status);
                        }
                    });
                    
                    // Update chat unread badge if unread_count is provided
                    if (data.unread_count !== undefined) {
                        updateChatUnreadBadge(data.unread_count);
                    }
                }
            })
            .catch(error => {
                console.error('Error loading messages:', error);
            });
    }

    function sendMessage() {
        const conversationId = document.getElementById('currentConversationId').value;
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();
        const editingId = document.getElementById('editingMessageId').value;

        if (!message) return;
        
        // If editing a message
        if (editingId) {
            fetch(`{{ url('/chat/messages') }}/${editingId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: message })
            })
            .then(async response => {
                const data = await response.json();
                // Check if response is ok
                if (!response.ok) {
                    // Handle validation errors
                    if (response.status === 422 && data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join(', ');
                        throw new Error(errorMessages || 'Validation error');
                    }
                    throw new Error(data.error || data.message || 'Error updating message');
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    cancelEdit();
                    if (currentConversationId) {
                        loadMessages(currentConversationId, false);
                    }
                } else {
                    if (data.error && data.error.includes('15 minutes')) {
                        alert('This message can only be edited within 15 minutes of sending.');
                        cancelEdit();
                        // Reload messages to update can_edit status
                        if (currentConversationId) {
                            loadMessages(currentConversationId, false);
                        }
                    } else {
                        alert(data.error || 'Error updating message');
                    }
                }
            })
            .catch(error => {
                console.error('Error updating message:', error);
                alert(error.message || 'Error updating message');
                // Reset edit state on error
                cancelEdit();
            });
            return;
        }

        // Send new message
        if (!conversationId) return;

        fetch(`{{ url('/chat/messages') }}/${conversationId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                appendMessage(data.message);
                messageInput.value = '';
                scrollToBottom();
            } else {
                alert('Error sending message');
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Error sending message');
        });
    }

    function appendMessage(message) {
        const messagesList = document.getElementById('messagesList');
        messagesList.innerHTML += createMessageHTML(message);
        scrollToBottom();
    }

    let isSelectionMode = false;
    let selectedMessages = new Set();
    let editingMessageId = null;

    function createMessageHTML(message) {
        const isMine = message.sender_id == currentUserId || message.is_mine;
        const deliveryStatus = message.delivery_status || 'sent';
        const isEdited = message.is_edited || (message.updated_at && message.updated_at !== message.created_at);
        const canEdit = message.can_edit !== undefined ? message.can_edit : true;
        
        // Read receipt icons - WhatsApp style
        let readReceipt = '';
        if (isMine) {
            if (deliveryStatus === 'read') {
                readReceipt = '<span class="read-receipt double read"><i class="fas fa-check-double"></i></span>';
            } else if (deliveryStatus === 'delivered') {
                readReceipt = '<span class="read-receipt double"><i class="fas fa-check-double"></i></span>';
            } else {
                readReceipt = '<span class="read-receipt single"><i class="fas fa-check"></i></span>';
            }
        }
        
        // Selection checkbox
        const checkbox = isSelectionMode ? `
            <input type="checkbox" class="message-checkbox" 
                   data-message-id="${message.id}" 
                   onchange="toggleMessageSelection(${message.id})"
                   ${selectedMessages.has(message.id) ? 'checked' : ''}>
        ` : '';
        
        // Edit/Delete actions (only for own messages)
        const actions = isMine && !isSelectionMode ? `
            <div class="message-actions">
                ${canEdit ? `<button class="btn btn-sm" onclick="editMessage(${message.id}, '${escapeHtml(message.message).replace(/'/g, "\\'")}')" title="Edit" style="padding: 4px 8px; background: white; border: none; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <i class="fas fa-edit" style="font-size: 12px; color: #667eea;"></i>
                </button>` : ''}
                <button class="btn btn-sm" onclick="deleteSingleMessage(${message.id})" title="Delete" style="padding: 4px 8px; background: white; border: none; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-left: 4px;">
                    <i class="fas fa-trash" style="font-size: 12px; color: #dc3545;"></i>
                </button>
            </div>
        ` : '';
        
        return `
            <div class="message-bubble ${isMine ? 'sent' : 'received'}" data-message-id="${message.id}" data-delivery-status="${deliveryStatus}" data-can-edit="${canEdit}" style="display: flex; flex-direction: column; align-items: ${isMine ? 'flex-end' : 'flex-start'}; margin-bottom: 2px;">
                ${checkbox}
                <div style="display: flex; align-items: flex-start; gap: 6px; width: 100%;">
                    ${actions}
                    <div style="flex: 1; min-width: 0;">
                        <div style="margin-bottom: 2px; word-wrap: break-word;">${escapeHtml(message.message)}</div>
                        <div style="display: flex; align-items: center; justify-content: ${isMine ? 'flex-end' : 'flex-start'}; gap: 4px; margin-top: 2px;">
                            <span class="message-time" style="font-size: 11px;">${message.time || message.created_at}</span>
                            ${isEdited ? '<span class="message-edited" style="font-size: 11px;">edited</span>' : ''}
                            ${readReceipt}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    function updateMessageReadReceipt(messageId, deliveryStatus) {
        const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
        if (messageElement && messageElement.classList.contains('sent')) {
            const timeContainer = messageElement.querySelector('.message-time').parentElement;
            let readReceipt = '';
            
            if (deliveryStatus === 'read') {
                readReceipt = '<span class="read-receipt double read"><i class="fas fa-check-double"></i></span>';
            } else if (deliveryStatus === 'delivered') {
                readReceipt = '<span class="read-receipt double"><i class="fas fa-check-double"></i></span>';
            } else {
                readReceipt = '<span class="read-receipt single"><i class="fas fa-check"></i></span>';
            }
            
            // Remove existing read receipt and add new one
            const existingReceipt = timeContainer.querySelector('.read-receipt');
            if (existingReceipt) {
                existingReceipt.outerHTML = readReceipt;
            } else {
                timeContainer.insertAdjacentHTML('beforeend', readReceipt);
            }
            
            messageElement.setAttribute('data-delivery-status', deliveryStatus);
        }
    }

    function scrollToBottom() {
        const container = document.getElementById('messagesContainer');
        container.scrollTop = container.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function updateChatUnreadBadge(count) {
        const badge = document.getElementById('chatUnreadBadge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    function toggleSelectMode() {
        isSelectionMode = !isSelectionMode;
        selectedMessages.clear();
        
        if (isSelectionMode) {
            document.getElementById('messagesContainer').classList.add('selection-mode');
            document.getElementById('selectModeBtn').style.display = 'none';
            document.getElementById('selectionActions').style.display = 'flex';
            document.getElementById('messageForm').style.display = 'none';
        } else {
            document.getElementById('messagesContainer').classList.remove('selection-mode');
            document.getElementById('selectModeBtn').style.display = 'block';
            document.getElementById('selectionActions').style.display = 'none';
            document.getElementById('messageForm').style.display = 'flex';
        }
        
        updateSelectedCount();
        // Re-render messages to show/hide checkboxes
        if (currentConversationId) {
            loadMessages(currentConversationId, false);
        }
    }
    
    function cancelSelection() {
        isSelectionMode = false;
        selectedMessages.clear();
        document.getElementById('messagesContainer').classList.remove('selection-mode');
        document.getElementById('selectModeBtn').style.display = 'block';
        document.getElementById('selectionActions').style.display = 'none';
        document.getElementById('messageForm').style.display = 'flex';
        updateSelectedCount();
        if (currentConversationId) {
            loadMessages(currentConversationId, false);
        }
    }
    
    function toggleMessageSelection(messageId) {
        if (selectedMessages.has(messageId)) {
            selectedMessages.delete(messageId);
        } else {
            selectedMessages.add(messageId);
        }
        updateSelectedCount();
    }
    
    function updateSelectedCount() {
        const count = selectedMessages.size;
        document.getElementById('selectedCount').textContent = count;
    }
    
    function deleteSingleMessage(messageId) {
        if (!confirm('Are you sure you want to delete this message?')) {
            return;
        }
        
        fetch('{{ route("chat.delete-messages") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message_ids: [messageId] })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove message from DOM
                const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
                if (messageElement) {
                    messageElement.remove();
                }
                // Reload messages to ensure consistency
                if (currentConversationId) {
                    loadMessages(currentConversationId, false);
                }
            } else {
                alert('Error deleting message');
            }
        })
        .catch(error => {
            console.error('Error deleting message:', error);
            alert('Error deleting message');
        });
    }
    
    function deleteSelectedMessages() {
        if (selectedMessages.size === 0) {
            alert('Please select at least one message to delete');
            return;
        }
        
        if (!confirm(`Are you sure you want to delete ${selectedMessages.size} message(s)?`)) {
            return;
        }
        
        const messageIds = Array.from(selectedMessages);
        
        fetch('{{ route("chat.delete-messages") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message_ids: messageIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectedMessages.clear();
                cancelSelection();
                if (currentConversationId) {
                    loadMessages(currentConversationId, false);
                }
            } else {
                alert('Error deleting messages');
            }
        })
        .catch(error => {
            console.error('Error deleting messages:', error);
            alert('Error deleting messages');
        });
    }
    
    function editMessage(messageId, currentMessage) {
        const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
        const canEdit = messageElement ? messageElement.dataset.canEdit === 'true' : true;
        
        if (!canEdit) {
            alert('This message can only be edited within 15 minutes of sending.');
            return;
        }
        
        editingMessageId = messageId;
        document.getElementById('messageInput').value = currentMessage;
        document.getElementById('editingMessageId').value = messageId;
        document.getElementById('editingIndicator').style.display = 'block';
        document.getElementById('cancelEditBtn').style.display = 'block';
        document.getElementById('messageInput').focus();
    }
    
    function cancelEdit() {
        editingMessageId = null;
        document.getElementById('editingMessageId').value = '';
        document.getElementById('editingIndicator').style.display = 'none';
        document.getElementById('cancelEditBtn').style.display = 'none';
        document.getElementById('messageInput').value = '';
        document.getElementById('messageInput').placeholder = 'Type a message';
        // Remove sendBtnText reference if element doesn't exist
        const sendBtnText = document.getElementById('sendBtnText');
        if (sendBtnText) {
            sendBtnText.textContent = 'Send';
        }
    }
    
    // Emoji Picker Functions
    function toggleEmojiPicker() {
        const emojiPicker = document.getElementById('emojiPicker');
        if (emojiPicker) {
            const isVisible = emojiPicker.style.display === 'block';
            emojiPicker.style.display = isVisible ? 'none' : 'block';
        }
    }
    
    function insertEmoji(emoji) {
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            const cursorPos = messageInput.selectionStart || messageInput.value.length;
            const textBefore = messageInput.value.substring(0, cursorPos);
            const textAfter = messageInput.value.substring(cursorPos);
            messageInput.value = textBefore + emoji + textAfter;
            messageInput.focus();
            // Set cursor position after emoji
            messageInput.setSelectionRange(cursorPos + emoji.length, cursorPos + emoji.length);
        }
        // Hide emoji picker after selection
        const emojiPicker = document.getElementById('emojiPicker');
        if (emojiPicker) {
            emojiPicker.style.display = 'none';
        }
    }
    
    // Close emoji picker when clicking outside
    document.addEventListener('click', function(e) {
        const emojiBtn = document.getElementById('emojiBtn');
        const emojiPicker = document.getElementById('emojiPicker');
        if (emojiPicker && emojiBtn && !emojiBtn.contains(e.target) && !emojiPicker.contains(e.target)) {
            emojiPicker.style.display = 'none';
        }
    });
    
</script>
@endpush
@endsection

