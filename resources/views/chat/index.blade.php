@extends('layouts.reos')

@section('title', 'Team Chat – REOS Real Estate Operating System')

@section('content')
<div class="h-[calc(100vh-80px)] flex flex-col" x-data="chatApp()" x-init="initChat()">
    <!-- Top Header Bar -->
    <div class="mb-4 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="page-heading flex items-center gap-2">
                <i class="fa-solid fa-[#059669] fa-comments text-emerald-600"></i>
                <span>Team & Broker Chat</span>
            </h1>
            <p class="body-text">Direct 1-to-1 messaging and team group discussions</p>
        </div>
        <div class="flex items-center space-x-2">
            <button @click="showDirectModal = true" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200 transition flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i>
                <span>New Direct Chat</span>
            </button>
            <button @click="showGroupModal = true" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
                <i class="fa-solid fa-users-gear"></i>
                <span>Create Group Chat</span>
            </button>
        </div>
    </div>

    <!-- Dual Pane Chat Container -->
    <div class="flex-1 bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden flex flex-col md:flex-row min-h-0">
        <!-- Left Pane: Conversations List -->
        <div class="w-full md:w-80 lg:w-96 border-r border-slate-200 flex flex-col bg-slate-50/50 min-h-0">
            <!-- Search & Filters -->
            <div class="p-4 border-b border-slate-200 space-y-3 bg-white">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" placeholder="Search chats..."
                        class="w-full bg-slate-100 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs font-semibold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                </div>
                <div class="flex items-center space-x-1 p-1 bg-slate-100 rounded-xl text-[11px] font-bold">
                    <button @click="filterType = 'all'" :class="filterType === 'all' ? 'bg-white text-emerald-700 shadow-2xs' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-1 rounded-lg transition text-center">All</button>
                    <button @click="filterType = 'direct'" :class="filterType === 'direct' ? 'bg-white text-emerald-700 shadow-2xs' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-1 rounded-lg transition text-center">Direct</button>
                    <button @click="filterType = 'group'" :class="filterType === 'group' ? 'bg-white text-emerald-700 shadow-2xs' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-1 rounded-lg transition text-center">Groups</button>
                </div>
            </div>

            <!-- Conversations Scroll Area -->
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
                <template x-for="conv in filteredConversations" :key="conv.id">
                    <div @click="selectConversation(conv.id)"
                        :class="activeChatId === conv.id ? 'bg-emerald-50/70 border-l-4 border-emerald-600' : 'hover:bg-slate-100/60'"
                        class="p-3.5 cursor-pointer transition flex items-center space-x-3">
                        
                        <!-- Avatar -->
                        <div class="relative flex-shrink-0">
                            <template x-if="conv.type === 'group'">
                                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm border border-indigo-200">
                                    <i class="fa-solid fa-users text-xs"></i>
                                </div>
                            </template>
                            <template x-if="conv.type === 'direct'">
                                <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm border border-emerald-200">
                                    <span x-text="conv.name.charAt(0).toUpperCase()"></span>
                                </div>
                            </template>
                            <template x-if="conv.unread_count > 0">
                                <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-extrabold w-4 h-4 rounded-full flex items-center justify-center border border-white"
                                    x-text="conv.unread_count"></span>
                            </template>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-800 truncate" x-text="conv.name"></h4>
                                <span class="text-[10px] text-slate-400 font-medium" x-text="conv.last_message_time"></span>
                            </div>
                            <div class="flex items-center justify-between mt-0.5">
                                <p class="text-[11px] text-slate-500 truncate" x-text="conv.last_message"></p>
                                <template x-if="conv.other_user_role">
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-200/70 text-slate-600 font-semibold" x-text="conv.other_user_role"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="filteredConversations.length === 0" class="p-8 text-center text-slate-400 space-y-2">
                    <i class="fa-solid fa-comments text-3xl opacity-40"></i>
                    <p class="text-xs font-medium">No conversations found</p>
                </div>
            </div>
        </div>

        <!-- Right Pane: Active Messaging Stream -->
        <div class="flex-1 flex flex-col bg-white min-h-0">
            <template x-if="activeChat">
                <div class="flex-1 flex flex-col min-h-0">
                    <!-- Chat Header -->
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/40">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-sm border"
                                :class="activeChat.type === 'group' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200'">
                                <span x-text="activeChat.type === 'group' ? '👥' : activeChat.name.charAt(0).toUpperCase()"></span>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900" x-text="activeChat.name"></h3>
                                <p class="text-[11px] text-slate-500 font-medium" x-text="activeChat.type === 'group' ? (activeChat.participants.length + ' members in group') : 'Direct Chat'"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Stream -->
                    <div id="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3 bg-[#F8FAFC]/50">
                        <template x-for="msg in messages" :key="msg.id">
                            <div :class="msg.is_mine ? 'flex justify-end' : 'flex justify-start'" class="space-y-1">
                                <div class="max-w-[80%] md:max-w-[65%] space-y-1">
                                    <template x-if="!msg.is_mine && activeChat.type === 'group'">
                                        <span class="text-[10px] font-bold text-slate-500 block px-1" x-text="msg.sender_name"></span>
                                    </template>

                                    <div :class="msg.is_mine ? 'bg-emerald-600 text-white rounded-2xl rounded-tr-xs' : 'bg-white text-slate-800 border border-slate-200 rounded-2xl rounded-tl-xs shadow-2xs'"
                                        class="p-3 text-xs leading-relaxed font-medium space-y-2">
                                        
                                        <template x-if="msg.message">
                                            <p x-text="msg.message" class="whitespace-pre-wrap"></p>
                                        </template>

                                        <template x-if="msg.attachment_url">
                                            <a :href="msg.attachment_url" target="_blank" class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-xl text-xs font-bold transition"
                                                :class="msg.is_mine ? 'bg-white/20 text-white hover:bg-white/30' : 'bg-slate-100 text-emerald-700 hover:bg-slate-200'">
                                                <i class="fa-solid fa-paperclip"></i>
                                                <span>View Attachment</span>
                                            </a>
                                        </template>

                                        <span class="block text-[9px] text-right opacity-70 font-mono" x-text="msg.created_at"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Composer Form -->
                    <div class="p-3 border-t border-slate-200 bg-white">
                        <form @submit.prevent="sendMessage()" class="flex items-center space-x-2">
                            <label class="p-2.5 text-slate-400 hover:text-emerald-600 cursor-pointer transition rounded-xl hover:bg-slate-100">
                                <i class="fa-solid fa-paperclip text-sm"></i>
                                <input type="file" @change="handleFileUpload($event)" class="hidden">
                            </label>
                            <input type="text" x-model="newMessage" placeholder="Type your message..."
                                class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                            <button type="submit" :disabled="isSending || (!newMessage && !attachmentFile)"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-extrabold text-xs rounded-xl shadow-xs transition flex items-center space-x-1 cursor-pointer">
                                <span>Send</span>
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                            </button>
                        </form>
                        <template x-if="attachmentFile">
                            <div class="mt-2 text-[11px] text-emerald-700 font-semibold flex items-center justify-between px-2 py-1 bg-emerald-50 rounded-lg border border-emerald-200">
                                <span>Attached: <strong x-text="attachmentFile.name"></strong></span>
                                <button type="button" @click="attachmentFile = null" class="text-rose-600 font-bold ml-2">Remove</button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="!activeChat">
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-slate-400 space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center text-slate-300">
                        <i class="fa-solid fa-comments text-3xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Select a Conversation</h3>
                    <p class="text-xs max-w-xs text-slate-500">Choose a team member or group from the list on the left to start chatting.</p>
                </div>
            </template>
        </div>
    </div>

    <!-- Modal: New Direct Chat -->
    <div x-show="showDirectModal" x-cloak class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-md w-full border border-slate-200 shadow-2xl space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-emerald-600"></i>
                    <span>Start Direct Chat</span>
                </h3>
                <button @click="showDirectModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="max-h-60 overflow-y-auto divide-y divide-slate-100">
                @foreach($availableUsers as $user)
                    <div @click="startDirectChat({{ $user->id }})" class="p-3 hover:bg-emerald-50 rounded-xl cursor-pointer flex items-center justify-between transition">
                        <div>
                            <p class="text-xs font-bold text-slate-800">{{ $user->name }}</p>
                            <p class="text-[10px] text-slate-500">{{ $user->role ? $user->role->name : 'User' }} • {{ $user->email }}</p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modal: Create Group Chat -->
    <div x-show="showGroupModal" x-cloak class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-md w-full border border-slate-200 shadow-2xl space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-users-gear text-indigo-600"></i>
                    <span>Create Group Chat</span>
                </h3>
                <button @click="showGroupModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form @submit.prevent="createGroupChat()" class="space-y-4">
                <div>
                    <label class="form-label">Group Name</label>
                    <input type="text" x-model="groupName" required placeholder="e.g. Sales Team Alpha" class="form-input">
                </div>
                <div>
                    <label class="form-label">Select Participants</label>
                    <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-xl p-2 space-y-1 bg-slate-50">
                        @foreach($availableUsers as $user)
                            <label class="flex items-center space-x-2 p-2 hover:bg-white rounded-lg cursor-pointer transition">
                                <input type="checkbox" :value="{{ $user->id }}" x-model="selectedGroupUsers" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                                <span class="text-xs font-bold text-slate-800">{{ $user->name }} <span class="text-[10px] text-slate-400">({{ $user->role ? $user->role->name : 'User' }})</span></span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showGroupModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" :disabled="!groupName || selectedGroupUsers.length === 0" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-xs">Create Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function chatApp() {
    return {
        conversations: [],
        searchQuery: '',
        filterType: 'all',
        activeChatId: null,
        activeChat: null,
        newMessage: '',
        attachmentFile: null,
        isSending: false,
        showDirectModal: false,
        showGroupModal: false,
        groupName: '',
        selectedGroupUsers: [],
        pollTimer: null,

        initChat() {
            this.fetchConversations();
            this.pollTimer = setInterval(() => {
                this.fetchConversations();
                if (this.activeChatId) {
                    this.fetchMessages(this.activeChatId, false);
                }
            }, 3000);
        },

        fetchConversations() {
            fetch('{{ route("chat.conversations") }}')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.conversations = data.conversations;
                    }
                });
        },

        get filteredConversations() {
            return this.conversations.filter(c => {
                const matchesType = this.filterType === 'all' || c.type === this.filterType;
                const matchesSearch = !this.searchQuery || c.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                return matchesType && matchesSearch;
            });
        },

        selectConversation(chatId) {
            this.activeChatId = chatId;
            this.fetchMessages(chatId, true);
        },

        fetchMessages(chatId, scroll = false) {
            fetch(`/chat/${chatId}/messages`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.activeChat = data.chat;
                        this.messages = data.messages;
                        if (scroll) {
                            this.$nextTick(() => {
                                const container = document.getElementById('messagesContainer');
                                if (container) container.scrollTop = container.scrollHeight;
                            });
                        }
                    }
                });
        },

        handleFileUpload(event) {
            this.attachmentFile = event.target.files[0] || null;
        },

        sendMessage() {
            if (this.isSending || (!this.newMessage && !this.attachmentFile)) return;
            this.isSending = true;

            const formData = new FormData();
            if (this.newMessage) formData.append('message', this.newMessage);
            if (this.attachmentFile) formData.append('attachment', this.attachmentFile);

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(`/chat/${this.activeChatId}/send`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.message) {
                    if (!this.messages.some(m => m.id === data.message.id)) {
                        this.messages.push(data.message);
                    }
                    this.newMessage = '';
                    this.attachmentFile = null;
                    this.fetchConversations();
                    this.$nextTick(() => {
                        const container = document.getElementById('messagesContainer');
                        if (container) container.scrollTop = container.scrollHeight;
                    });
                }
            })
            .finally(() => {
                this.isSending = false;
            });
        },

        startDirectChat(userId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('{{ route("chat.direct") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ user_id: userId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.showDirectModal = false;
                    this.fetchConversations();
                    this.selectConversation(data.chat_id);
                }
            });
        },

        createGroupChat() {
            if (!this.groupName || this.selectedGroupUsers.length === 0) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('{{ route("chat.group") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    name: this.groupName,
                    participant_ids: this.selectedGroupUsers
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.showGroupModal = false;
                    this.groupName = '';
                    this.selectedGroupUsers = [];
                    this.fetchConversations();
                    this.selectConversation(data.chat_id);
                }
            });
        }
    }
}
</script>
@endsection
