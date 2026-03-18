<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>decentID - Editor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white min-h-screen py-10 px-4">
    <div class="max-w-2xl mx-auto border-4 border-black">
        <nav class="bg-gray-100 border-b-4 border-black p-3 flex items-center gap-4 font-mono text-sm uppercase">
            <a href="/" class="font-bold hover:underline" data-i18n="viewer" data-tooltip="tip_nav_viewer" data-tooltip-pos="bottom">Viewer</a>
            <a href="/editor" class="font-bold hover:underline" data-i18n="editor" data-tooltip="tip_nav_editor" data-tooltip-pos="bottom">Editor</a>
            <a href="/teams" class="font-bold hover:underline" data-i18n="teams" data-tooltip="tip_nav_teams" data-tooltip-pos="bottom">Teams</a>
            <a href="/register" class="font-bold hover:underline" data-i18n="register" data-tooltip="tip_nav_register" data-tooltip-pos="bottom">Register</a>
            <select id="ui-locale-select" class="ml-auto border-[2px] border-black px-2 py-1 font-mono text-xs bg-white cursor-pointer" data-tooltip="tip_ui_language" data-tooltip-pos="bottom">
                <option value="en">EN</option>
                <option value="ar">AR</option>
                <option value="fr">FR</option>
                <option value="es">ES</option>
                <option value="de">DE</option>
                <option value="zh">ZH</option>
                <option value="ja">JA</option>
            </select>
        </nav>

        <!-- Header -->
        <h1 class="bg-black text-white p-4 text-xl font-bold uppercase font-mono" data-i18n="profile_editor">
            DECENTID EDITOR
        </h1>

        <!-- Not Logged In -->
        <div id="not-logged-in" class="hidden p-4 space-y-4">
            <div class="border-[3px] border-black p-4 font-mono text-center bg-white">
                <p data-i18n="must_login_edit">You must be logged in to edit your profile.</p>
                <div class="flex gap-3 justify-center mt-3">
                    <a href="/" class="inline-block bg-black text-white p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-white hover:text-black cursor-pointer" data-i18n="login">
                        LOGIN
                    </a>
                    <a href="/register" class="inline-block bg-white text-black p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-black hover:text-white cursor-pointer" data-i18n="register">
                        REGISTER
                    </a>
                </div>
            </div>
        </div>

        <!-- Editor Content (shown when logged in) -->
        <div id="editor-content" class="hidden">
            <!-- Auth Status -->
            <div class="p-4 border-b-4 border-black flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img id="user-photo" src="" alt="Profile Photo" class="w-12 h-12 border-[3px] border-black object-cover">
                    <span class="font-mono">
                        Logged in as: <strong id="logged-in-user"></strong>
                    </span>
                </div>
                <button id="logout-btn" class="bg-white text-black p-2 px-4 font-mono font-bold uppercase border-[3px] border-black hover:bg-black hover:text-white cursor-pointer text-xs" data-i18n="logout" data-tooltip="tip_logout">
                    LOGOUT
                </button>
            </div>

            <!-- Profile Photo (universal) -->
            <div class="p-4 border-b-4 border-black space-y-3">
                <label class="block font-mono font-bold uppercase text-sm" data-i18n="profile_photo">PROFILE PHOTO</label>
                <div class="flex items-center gap-4">
                    <img id="photo-preview" src="" alt="Profile Photo" class="w-20 h-20 border-[3px] border-black object-cover">
                    <div class="flex-1 space-y-2">
                        <input type="file" id="photo-upload" accept=".jpg,.jpeg,.png,.gif,.webp" class="hidden"
                               onchange="document.getElementById('photo-filename').textContent = this.files[0]?.name || ''">
                        <div class="flex gap-2">
                            <button onclick="document.getElementById('photo-upload').click()"
                                    class="bg-white text-black px-4 py-2 font-mono font-bold uppercase text-xs border-[3px] border-black hover:bg-black hover:text-white cursor-pointer"
                                    data-i18n="choose_image" data-tooltip="tip_upload_photo">CHOOSE IMAGE</button>
                            <button id="photo-upload-btn" onclick="uploadPhoto()"
                                    class="bg-black text-white px-4 py-2 font-mono font-bold uppercase text-xs border-[3px] border-black hover:bg-white hover:text-black cursor-pointer"
                                    data-i18n="upload_photo" data-tooltip="tip_upload_photo">SAVE PHOTO</button>
                        </div>
                        <span id="photo-filename" class="font-mono text-xs text-gray-600 truncate"></span>
                    </div>
                </div>
            </div>

            <!-- Context & Language Selector -->
            <div class="p-4 border-b-4 border-black space-y-4">
                <div>
                    <label class="block font-mono font-bold uppercase text-sm mb-2" data-i18n="select_context_label">SELECT CONTEXT</label>
                    <select id="context-select" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none rounded-none bg-white appearance-none cursor-pointer" data-tooltip="tip_editor_context_select">
                        <option value="">-- Select Context --</option>
                    </select>
                </div>
                <div>
                    <label class="block font-mono font-bold uppercase text-sm mb-2" data-i18n="data_language">DATA LANGUAGE</label>
                    <select id="data-locale-select" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none rounded-none bg-white appearance-none cursor-pointer" data-tooltip="tip_data_language">
                        <option value="en">English (en)</option>
                        <option value="ar">Arabic (ar)</option>
                        <option value="fr">French (fr)</option>
                        <option value="es">Spanish (es)</option>
                        <option value="de">German (de)</option>
                        <option value="zh">Chinese (zh)</option>
                        <option value="ja">Japanese (ja)</option>
                    </select>
                </div>
                <button id="load-context-btn" class="w-full bg-black text-white p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-white hover:text-black cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" disabled data-i18n="load_context" data-tooltip="tip_load_context">
                    LOAD CONTEXT
                </button>
            </div>

            <!-- Profile Attributes Editor -->
            <div id="attributes-section" class="hidden">
                <div class="bg-black text-white p-3 flex items-center justify-between">
                    <h2 class="text-lg font-bold uppercase font-mono" data-i18n="attributes">ATTRIBUTES</h2>
                    <span id="context-name-display" class="font-mono text-sm"></span>
                </div>

                <div id="attributes-container" class="divide-y-2 divide-black">
                    <!-- Attributes will be inserted here -->
                </div>

                <!-- Add Attribute -->
                <div class="p-4 border-t-4 border-black space-y-3">
                    <label class="block font-mono font-bold uppercase text-sm" data-i18n="add_attribute">ADD ATTRIBUTE</label>
                    <div class="flex gap-2">
                        <select id="add-attribute-select" class="flex-1 border-[3px] border-black p-2 font-mono text-sm focus:outline-none focus:ring-0 rounded-none bg-white" data-tooltip="tip_add_attr_select">
                            <option value="">-- Select --</option>
                        </select>
                        <button id="add-attribute-btn" class="bg-black text-white p-2 px-4 font-mono font-bold uppercase border-[3px] border-black hover:bg-white hover:text-black cursor-pointer text-xs" data-i18n="add" data-tooltip="tip_add_attr_btn">
                            ADD
                        </button>
                    </div>
                </div>

                <!-- Save / Status -->
                <div class="p-4 border-t-4 border-black space-y-3">
                    <div id="save-status" class="hidden border-[3px] border-black p-3 font-mono text-center bg-white text-sm"></div>
                    <button id="save-btn" class="w-full bg-black text-white p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-white hover:text-black cursor-pointer" data-i18n="save_changes" data-tooltip="tip_save_changes">
                        SAVE CHANGES
                    </button>
                </div>

                <!-- Deactivate Context -->
                <div class="p-4 border-t-4 border-black">
                    <button id="deactivate-btn" class="w-full bg-white text-black p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-black hover:text-white cursor-pointer" data-i18n="deactivate_context" data-tooltip="tip_deactivate_context">
                        DEACTIVATE THIS CONTEXT
                    </button>
                </div>
            </div>

            <!-- Create Context Section -->
            <div class="border-t-4 border-black">
                <h2 class="bg-black text-white p-3 text-lg font-bold uppercase font-mono" data-i18n="create_new_context">
                    CREATE NEW CONTEXT
                </h2>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block font-mono font-bold uppercase text-sm mb-2" data-i18n="context_name">CONTEXT NAME</label>
                        <input type="text" id="new-context-name" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="Professional" data-tooltip="tip_context_name">
                    </div>
                    <div>
                        <label class="block font-mono font-bold uppercase text-sm mb-2" data-i18n="context_slug">CONTEXT SLUG</label>
                        <input type="text" id="new-context-slug" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="professional" data-tooltip="tip_context_slug">
                    </div>
                    <div id="create-context-error" class="hidden border-[3px] border-black p-3 font-mono text-center bg-white text-sm"></div>
                    <button id="create-context-btn" class="w-full bg-black text-white p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-white hover:text-black cursor-pointer" data-i18n="create_context" data-tooltip="tip_create_context">
                        CREATE CONTEXT
                    </button>
                </div>
            </div>

            <!-- Create Attribute Section -->
            <div class="border-t-4 border-black">
                <h2 class="bg-black text-white p-3 text-lg font-bold uppercase font-mono" data-i18n="create_new_attribute">
                    CREATE NEW ATTRIBUTE
                </h2>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block font-mono font-bold uppercase text-sm mb-2" data-i18n="attribute_name">ATTRIBUTE NAME</label>
                        <input type="text" id="new-attr-name" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="Nickname" data-tooltip="tip_attr_name">
                    </div>
                    <div>
                        <label class="block font-mono font-bold uppercase text-sm mb-2" data-i18n="attribute_key">ATTRIBUTE KEY</label>
                        <input type="text" id="new-attr-key" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="nickname" data-tooltip="tip_attr_key">
                    </div>
                    <div>
                        <label class="block font-mono font-bold uppercase text-sm mb-2" data-i18n="data_type">DATA TYPE</label>
                        <select id="new-attr-type" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none rounded-none bg-white appearance-none cursor-pointer" data-tooltip="tip_attr_type">
                            <option value="string">STRING</option>
                            <option value="text">TEXT</option>
                            <option value="email">EMAIL</option>
                            <option value="url">URL</option>
                        </select>
                    </div>
                    <div id="create-attr-error" class="hidden border-[3px] border-black p-3 font-mono text-center bg-white text-sm"></div>
                    <button id="create-attr-btn" class="w-full bg-black text-white p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-white hover:text-black cursor-pointer" data-i18n="create_attribute" data-tooltip="tip_create_attr">
                        CREATE ATTRIBUTE
                    </button>
                </div>
            </div>
        </div>

        <!-- Error Section -->
        <div id="error-section" class="hidden p-4 border-t-4 border-black">
            <div class="border-[3px] border-black p-4 font-mono text-center bg-white">
                <span id="error-message"></span>
            </div>
        </div>
    </div>

    @include('partials.i18n')
    @verbatim
    <script>
        // State
        let authToken = localStorage.getItem('auth_token');
        let currentUser = JSON.parse(localStorage.getItem('current_user') || 'null');
        let contexts = [];
        let currentContextSlug = null;
        let currentLocale = 'en';
        let currentAttributes = {};
        let apiLabels = {};
        let allAttributes = [];

        // DOM Elements
        const notLoggedIn = document.getElementById('not-logged-in');
        const editorContent = document.getElementById('editor-content');
        const loggedInUser = document.getElementById('logged-in-user');
        const logoutBtn = document.getElementById('logout-btn');
        const contextSelect = document.getElementById('context-select');
        const localeSelect = document.getElementById('data-locale-select');
        const loadContextBtn = document.getElementById('load-context-btn');
        const attributesSection = document.getElementById('attributes-section');
        const contextNameDisplay = document.getElementById('context-name-display');
        const attributesContainer = document.getElementById('attributes-container');
        const saveBtn = document.getElementById('save-btn');
        const saveStatus = document.getElementById('save-status');
        const deactivateBtn = document.getElementById('deactivate-btn');
        const newContextName = document.getElementById('new-context-name');
        const newContextSlug = document.getElementById('new-context-slug');
        const createContextBtn = document.getElementById('create-context-btn');
        const addAttributeSelect = document.getElementById('add-attribute-select');
        const addAttributeBtn = document.getElementById('add-attribute-btn');
        const createContextError = document.getElementById('create-context-error');
        const newAttrName = document.getElementById('new-attr-name');
        const newAttrKey = document.getElementById('new-attr-key');
        const newAttrType = document.getElementById('new-attr-type');
        const createAttrBtn = document.getElementById('create-attr-btn');
        const createAttrError = document.getElementById('create-attr-error');
        const errorSection = document.getElementById('error-section');
        const errorMessage = document.getElementById('error-message');

        // API helper
        async function api(url, options = {}) {
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...options.headers,
            };

            if (authToken) {
                headers['Authorization'] = `Bearer ${authToken}`;
            }

            const response = await fetch(url, {
                ...options,
                headers,
            });

            return response;
        }

        // Initialize
        async function init() {
            if (!authToken || !currentUser) {
                notLoggedIn.classList.remove('hidden');
                editorContent.classList.add('hidden');
                return;
            }

            notLoggedIn.classList.add('hidden');
            editorContent.classList.remove('hidden');
            loggedInUser.textContent = currentUser.name || currentUser.email;

            const photoUrl = currentUser.profile_photo || getGravatarUrl(currentUser.username);
            document.getElementById('user-photo').src = photoUrl;
            document.getElementById('photo-preview').src = photoUrl;

            await Promise.all([loadContexts(), loadAvailableAttributes()]);
            translateTooltips();
        }

        // Load all available profile attributes
        async function loadAvailableAttributes() {
            try {
                const response = await api('/api/viewer/attributes');
                if (response.ok) {
                    allAttributes = await response.json();
                }
            } catch (error) {
                console.error('Failed to load attributes:', error);
            }
        }

        // Load contexts for current user
        async function loadContexts() {
            try {
                const response = await api('/api/viewer/contexts');
                const data = await response.json();
                const allContexts = Array.isArray(data) ? data : [];

                // Filter contexts for current user
                contexts = allContexts.filter(ctx => {
                    return String(ctx.user_id) === String(currentUser.id);
                });

                contextSelect.innerHTML = '<option value="">-- Select Context --</option>';
                contexts.forEach(ctx => {
                    const option = document.createElement('option');
                    option.value = ctx.slug;
                    option.textContent = ctx.name || ctx.slug;
                    contextSelect.appendChild(option);
                });
            } catch (error) {
                showError('Failed to load contexts');
                console.error('Failed to load contexts:', error);
            }
        }

        // Load profile attributes for a context
        async function loadProfile(contextSlug) {
            currentLocale = localeSelect.value;
            try {
                const response = await api(`/api/profiles/${currentUser.username}/${contextSlug}?lang=${currentLocale}`);

                if (!response.ok) {
                    const error = await response.json();
                    showError(error.message || 'Failed to load profile');
                    return;
                }

                const data = await response.json();
                currentContextSlug = contextSlug;
                currentAttributes = {};

                // Parse attributes from response
                apiLabels = data._labels || {};
                const skipFields = ['@context', '@type', '@id', 'context', '_labels', 'profile_photo'];
                for (const [key, fieldData] of Object.entries(data)) {
                    if (skipFields.includes(key)) continue;
                    currentAttributes[key] = {
                        value: fieldData?.value ?? fieldData ?? '',
                        visibility: fieldData?.visibility || 'public',
                    };
                }

                displayAttributes();
            } catch (error) {
                showError('Failed to load profile');
                console.error(error);
            }
        }

        // Display editable attributes
        function displayAttributes() {
            attributesContainer.innerHTML = '';

            const context = contexts.find(c => c.slug === currentContextSlug);
            const localeName = localeSelect.selectedOptions[0]?.textContent || currentLocale;
            contextNameDisplay.textContent = (context ? (context.name || context.slug) : currentContextSlug) + ` [${localeName}]`;

            const keys = Object.keys(currentAttributes);

            if (keys.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'p-4 font-mono text-center text-sm';
                empty.textContent = t('no_attributes', currentLocale);
                attributesContainer.appendChild(empty);
            }

            keys.forEach(key => {
                const attr = currentAttributes[key];
                const row = document.createElement('div');
                row.className = 'p-4 space-y-2';

                const translatedKey = apiLabels[key] || tKey(key, currentLocale);

                row.innerHTML = `
                    <div class="flex items-center justify-between">
                        <label class="block font-mono font-bold uppercase text-sm">${translatedKey}</label>
                        <button onclick="deleteAttribute('${key}')" class="bg-white text-black px-2 py-1 font-mono font-bold uppercase border-[2px] border-black hover:bg-black hover:text-white cursor-pointer text-xs" data-tooltip="tip_delete_attr">
                            ✕
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" data-key="${key}" data-field="value"
                            class="flex-1 border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white"
                            value="${escapeHtml(String(attr.value || ''))}">
                        <select data-key="${key}" data-field="visibility"
                            class="border-[3px] border-black p-3 font-mono text-sm focus:outline-none rounded-none bg-white appearance-none cursor-pointer">
                            <option value="public" ${attr.visibility === 'public' ? 'selected' : ''}>PUBLIC</option>
                            <option value="protected" ${attr.visibility === 'protected' ? 'selected' : ''}>PROTECTED</option>
                            <option value="private" ${attr.visibility === 'private' ? 'selected' : ''}>PRIVATE</option>
                        </select>
                    </div>
                `;

                attributesContainer.appendChild(row);
            });

            // Populate add-attribute dropdown with attributes not yet in this context
            populateAddAttributeSelect();

            attributesSection.classList.remove('hidden');
            hideError();
            translateTooltips(currentLocale);
        }

        // Escape HTML for safe insertion
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Simple MD5 hash for Gravatar URLs
        function md5(string) {
            function md5cycle(x, k) {
                var a = x[0], b = x[1], c = x[2], d = x[3];
                a = ff(a, b, c, d, k[0], 7, -680876936); d = ff(d, a, b, c, k[1], 12, -389564586);
                c = ff(c, d, a, b, k[2], 17, 606105819); b = ff(b, c, d, a, k[3], 22, -1044525330);
                a = ff(a, b, c, d, k[4], 7, -176418897); d = ff(d, a, b, c, k[5], 12, 1200080426);
                c = ff(c, d, a, b, k[6], 17, -1473231341); b = ff(b, c, d, a, k[7], 22, -45705983);
                a = ff(a, b, c, d, k[8], 7, 1770035416); d = ff(d, a, b, c, k[9], 12, -1958414417);
                c = ff(c, d, a, b, k[10], 17, -42063); b = ff(b, c, d, a, k[11], 22, -1990404162);
                a = ff(a, b, c, d, k[12], 7, 1804603682); d = ff(d, a, b, c, k[13], 12, -40341101);
                c = ff(c, d, a, b, k[14], 17, -1502002290); b = ff(b, c, d, a, k[15], 22, 1236535329);
                a = gg(a, b, c, d, k[1], 5, -165796510); d = gg(d, a, b, c, k[6], 9, -1069501632);
                c = gg(c, d, a, b, k[11], 14, 643717713); b = gg(b, c, d, a, k[0], 20, -373897302);
                a = gg(a, b, c, d, k[5], 5, -701558691); d = gg(d, a, b, c, k[10], 9, 38016083);
                c = gg(c, d, a, b, k[15], 14, -660478335); b = gg(b, c, d, a, k[4], 20, -405537848);
                a = gg(a, b, c, d, k[9], 5, 568446438); d = gg(d, a, b, c, k[14], 9, -1019803690);
                c = gg(c, d, a, b, k[3], 14, -187363961); b = gg(b, c, d, a, k[8], 20, 1163531501);
                a = gg(a, b, c, d, k[13], 5, -1444681467); d = gg(d, a, b, c, k[2], 9, -51403784);
                c = gg(c, d, a, b, k[7], 14, 1735328473); b = gg(b, c, d, a, k[12], 20, -1926607734);
                a = hh(a, b, c, d, k[5], 4, -378558); d = hh(d, a, b, c, k[8], 11, -2022574463);
                c = hh(c, d, a, b, k[11], 16, 1839030562); b = hh(b, c, d, a, k[14], 23, -35309556);
                a = hh(a, b, c, d, k[1], 4, -1530992060); d = hh(d, a, b, c, k[4], 11, 1272893353);
                c = hh(c, d, a, b, k[7], 16, -155497632); b = hh(b, c, d, a, k[10], 23, -1094730640);
                a = hh(a, b, c, d, k[13], 4, 681279174); d = hh(d, a, b, c, k[0], 11, -358537222);
                c = hh(c, d, a, b, k[3], 16, -722521979); b = hh(b, c, d, a, k[6], 23, 76029189);
                a = hh(a, b, c, d, k[9], 4, -640364487); d = hh(d, a, b, c, k[12], 11, -421815835);
                c = hh(c, d, a, b, k[15], 16, 530742520); b = hh(b, c, d, a, k[2], 23, -995338651);
                a = ii(a, b, c, d, k[0], 6, -198630844); d = ii(d, a, b, c, k[7], 10, 1126891415);
                c = ii(c, d, a, b, k[14], 15, -1416354905); b = ii(b, c, d, a, k[5], 21, -57434055);
                a = ii(a, b, c, d, k[12], 6, 1700485571); d = ii(d, a, b, c, k[3], 10, -1894986606);
                c = ii(c, d, a, b, k[10], 15, -1051523); b = ii(b, c, d, a, k[1], 21, -2054922799);
                a = ii(a, b, c, d, k[8], 6, 1873313359); d = ii(d, a, b, c, k[15], 10, -30611744);
                c = ii(c, d, a, b, k[6], 15, -1560198380); b = ii(b, c, d, a, k[13], 21, 1309151649);
                a = ii(a, b, c, d, k[4], 6, -145523070); d = ii(d, a, b, c, k[11], 10, -1120210379);
                c = ii(c, d, a, b, k[2], 15, 718787259); b = ii(b, c, d, a, k[9], 21, -343485551);
                x[0] = add32(a, x[0]); x[1] = add32(b, x[1]); x[2] = add32(c, x[2]); x[3] = add32(d, x[3]);
            }
            function cmn(q, a, b, x, s, t) { a = add32(add32(a, q), add32(x, t)); return add32((a << s) | (a >>> (32 - s)), b); }
            function ff(a, b, c, d, x, s, t) { return cmn((b & c) | ((~b) & d), a, b, x, s, t); }
            function gg(a, b, c, d, x, s, t) { return cmn((b & d) | (c & (~d)), a, b, x, s, t); }
            function hh(a, b, c, d, x, s, t) { return cmn(b ^ c ^ d, a, b, x, s, t); }
            function ii(a, b, c, d, x, s, t) { return cmn(c ^ (b | (~d)), a, b, x, s, t); }
            function md51(s) {
                var n = s.length, state = [1732584193, -271733879, -1732584194, 271733878], i;
                for (i = 64; i <= n; i += 64) md5cycle(state, md5blk(s.substring(i - 64, i)));
                s = s.substring(i - 64);
                var tail = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
                for (i = 0; i < s.length; i++) tail[i >> 2] |= s.charCodeAt(i) << ((i % 4) << 3);
                tail[i >> 2] |= 0x80 << ((i % 4) << 3);
                if (i > 55) { md5cycle(state, tail); tail = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]; }
                tail[14] = n * 8;
                md5cycle(state, tail);
                return state;
            }
            function md5blk(s) {
                var md5blks = [], i;
                for (i = 0; i < 64; i += 4) md5blks[i >> 2] = s.charCodeAt(i) + (s.charCodeAt(i+1) << 8) + (s.charCodeAt(i+2) << 16) + (s.charCodeAt(i+3) << 24);
                return md5blks;
            }
            var hex_chr = '0123456789abcdef'.split('');
            function rhex(n) { var s = '', j = 0; for (; j < 4; j++) s += hex_chr[(n >> (j * 8 + 4)) & 0x0f] + hex_chr[(n >> (j * 8)) & 0x0f]; return s; }
            function hex(x) { for (var i = 0; i < x.length; i++) x[i] = rhex(x[i]); return x.join(''); }
            function add32(a, b) { return (a + b) & 0xFFFFFFFF; }
            return hex(md51(string));
        }

        // Get Gravatar URL from username
        function getGravatarUrl(username) {
            return 'https://www.gravatar.com/avatar/' + md5(username) + '?d=identicon&s=200';
        }

        // Upload profile photo
        async function uploadPhoto() {
            const fileInput = document.getElementById('photo-upload');
            if (!fileInput.files.length) return;

            const uploadBtn = document.getElementById('photo-upload-btn');
            uploadBtn.textContent = t('saving', currentLocale);
            uploadBtn.disabled = true;

            const formData = new FormData();
            formData.append('photo', fileInput.files[0]);

            try {
                const response = await fetch(`/api/profiles/${currentUser.username}/photo`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (!response.ok) {
                    const error = await response.json();
                    showSaveStatus(error.message || 'Upload failed', false);
                    uploadBtn.textContent = t('upload_photo', currentLocale);
                    uploadBtn.disabled = false;
                    return;
                }

                const data = await response.json();

                // Update previews
                document.getElementById('photo-preview').src = data.url;
                document.getElementById('user-photo').src = data.url;

                // Persist to localStorage
                currentUser.profile_photo = data.url;
                localStorage.setItem('current_user', JSON.stringify(currentUser));

                showSaveStatus(t('photo_uploaded', currentLocale), true);
            } catch (error) {
                showSaveStatus('Upload failed', false);
                console.error(error);
            }

            uploadBtn.textContent = t('upload_photo', currentLocale);
            uploadBtn.disabled = false;
        }

        // Populate the add-attribute dropdown with unused attributes
        function populateAddAttributeSelect() {
            addAttributeSelect.innerHTML = '<option value="">-- Select --</option>';
            const usedKeys = Object.keys(currentAttributes);
            allAttributes
                .filter(attr => !usedKeys.includes(attr.key))
                .forEach(attr => {
                    const label = (attr.translations && attr.translations[currentLocale]) || attr.name;
                    const option = document.createElement('option');
                    option.value = attr.key;
                    option.textContent = `${label} (${attr.key})`;
                    addAttributeSelect.appendChild(option);
                });
        }

        // Add a new attribute to the current context
        function addAttribute() {
            const key = addAttributeSelect.value;
            if (!key) return;

            currentAttributes[key] = { value: '', visibility: 'public' };
            // Add label from allAttributes
            const attrDef = allAttributes.find(a => a.key === key);
            if (attrDef) {
                apiLabels[key] = (attrDef.translations && attrDef.translations[currentLocale]) || attrDef.name;
            }
            displayAttributes();
        }

        // Delete an attribute value from the current context
        async function deleteAttribute(key) {
            if (!currentContextSlug || !currentUser) return;

            const translatedKey = apiLabels[key] || tKey(key, currentLocale);
            if (!confirm(`Delete "${translatedKey}" for this language?`)) return;

            try {
                const response = await api(`/api/profiles/${currentUser.username}/${currentContextSlug}/${key}?lang=${currentLocale}`, {
                    method: 'DELETE',
                });

                if (!response.ok) {
                    const data = await response.json();
                    showSaveStatus(data.message || 'Delete failed', false);
                    return;
                }

                delete currentAttributes[key];
                displayAttributes();
            } catch (error) {
                showSaveStatus('Delete failed', false);
                console.error(error);
            }
        }

        // Save attributes
        async function saveAttributes() {
            if (!currentContextSlug || !currentUser) return;

            const values = {};
            const valueInputs = attributesContainer.querySelectorAll('input[data-field="value"]');
            const visibilitySelects = attributesContainer.querySelectorAll('select[data-field="visibility"]');

            valueInputs.forEach(input => {
                const key = input.dataset.key;
                const visSelect = attributesContainer.querySelector(`select[data-key="${key}"]`);
                values[key] = {
                    value: input.value,
                    visibility: visSelect ? visSelect.value : 'public',
                };
            });

            saveBtn.textContent = t('saving', currentLocale);
            saveBtn.disabled = true;

            try {
                const response = await api(`/api/profiles/${currentUser.username}/${currentContextSlug}?lang=${currentLocale}`, {
                    method: 'PUT',
                    body: JSON.stringify({ values }),
                });

                if (!response.ok) {
                    const error = await response.json();
                    showSaveStatus(error.message || t('save_failed', currentLocale), false);
                    saveBtn.textContent = t('save_changes', currentLocale);
                    saveBtn.disabled = false;
                    return;
                }

                showSaveStatus(t('saved', currentLocale), true);
                saveBtn.textContent = t('save_changes', currentLocale);
                saveBtn.disabled = false;

                // Reload to reflect any server-side changes
                await loadProfile(currentContextSlug);
            } catch (error) {
                showSaveStatus(t('save_failed', currentLocale), false);
                saveBtn.textContent = t('save_changes', currentLocale);
                saveBtn.disabled = false;
                console.error(error);
            }
        }

        // Show save status
        function showSaveStatus(message, success) {
            saveStatus.textContent = message;
            saveStatus.classList.remove('hidden');
            if (success) {
                saveStatus.style.borderColor = 'black';
            } else {
                saveStatus.style.borderColor = 'black';
            }
            setTimeout(() => {
                saveStatus.classList.add('hidden');
            }, 3000);
        }

        // Create context
        async function createContext() {
            const name = newContextName.value.trim();
            const slug = newContextSlug.value.trim();

            if (!name || !slug) {
                showCreateContextError('Name and slug are required');
                return;
            }

            createContextBtn.textContent = 'CREATING...';
            createContextBtn.disabled = true;
            hideCreateContextError();

            try {
                const response = await api(`/api/profiles/${currentUser.username}/contexts`, {
                    method: 'POST',
                    body: JSON.stringify({ name, slug }),
                });

                if (!response.ok) {
                    const data = await response.json();
                    const msg = data.message || data.errors?.slug?.[0] || data.errors?.name?.[0] || 'Failed to create context';
                    showCreateContextError(msg);
                    createContextBtn.textContent = 'CREATE CONTEXT';
                    createContextBtn.disabled = false;
                    return;
                }

                newContextName.value = '';
                newContextSlug.value = '';
                createContextBtn.textContent = 'CREATE CONTEXT';
                createContextBtn.disabled = false;

                // Reload contexts
                await loadContexts();
            } catch (error) {
                showCreateContextError('Failed to create context');
                createContextBtn.textContent = 'CREATE CONTEXT';
                createContextBtn.disabled = false;
                console.error(error);
            }
        }

        // Create custom attribute
        async function createAttribute() {
            const name = newAttrName.value.trim();
            const key = newAttrKey.value.trim();
            const dataType = newAttrType.value;

            if (!name || !key) {
                showCreateAttrError(t('attr_name_key_required', currentLocale));
                return;
            }

            createAttrBtn.textContent = t('creating', currentLocale);
            createAttrBtn.disabled = true;
            hideCreateAttrError();

            try {
                const response = await api('/api/attributes', {
                    method: 'POST',
                    body: JSON.stringify({ key, name, data_type: dataType }),
                });

                if (!response.ok) {
                    const data = await response.json();
                    if (response.status === 401) {
                        showCreateAttrError('Session expired. Please log out and log back in.');
                        createAttrBtn.textContent = t('create_attribute', currentLocale);
                        createAttrBtn.disabled = false;
                        return;
                    }
                    const msg = data.message || data.errors?.key?.[0] || data.errors?.name?.[0] || 'Failed to create attribute';
                    showCreateAttrError(msg);
                    createAttrBtn.textContent = t('create_attribute', currentLocale);
                    createAttrBtn.disabled = false;
                    return;
                }

                newAttrName.value = '';
                newAttrKey.value = '';
                newAttrType.value = 'string';
                createAttrBtn.textContent = t('create_attribute', currentLocale);
                createAttrBtn.disabled = false;

                // Reload available attributes so the new one appears in the dropdown
                await loadAvailableAttributes();
                if (currentContextSlug) {
                    populateAddAttributeSelect();
                }
            } catch (error) {
                showCreateAttrError('Failed to create attribute');
                createAttrBtn.textContent = t('create_attribute', currentLocale);
                createAttrBtn.disabled = false;
                console.error(error);
            }
        }

        function showCreateAttrError(message) {
            createAttrError.textContent = message;
            createAttrError.classList.remove('hidden');
        }

        function hideCreateAttrError() {
            createAttrError.classList.add('hidden');
        }

        // Deactivate context
        async function deactivateContext() {
            if (!currentContextSlug || !currentUser) return;

            if (!confirm(`Are you sure you want to deactivate the context "${currentContextSlug}"?`)) {
                return;
            }

            deactivateBtn.textContent = 'DEACTIVATING...';
            deactivateBtn.disabled = true;

            try {
                const response = await api(`/api/profiles/${currentUser.username}/contexts/${currentContextSlug}`, {
                    method: 'DELETE',
                });

                if (!response.ok) {
                    const data = await response.json();
                    showError(data.message || 'Failed to deactivate context');
                    deactivateBtn.textContent = 'DEACTIVATE THIS CONTEXT';
                    deactivateBtn.disabled = false;
                    return;
                }

                currentContextSlug = null;
                currentAttributes = {};
                attributesSection.classList.add('hidden');
                deactivateBtn.textContent = 'DEACTIVATE THIS CONTEXT';
                deactivateBtn.disabled = false;

                // Reload contexts
                await loadContexts();
            } catch (error) {
                showError('Failed to deactivate context');
                deactivateBtn.textContent = 'DEACTIVATE THIS CONTEXT';
                deactivateBtn.disabled = false;
                console.error(error);
            }
        }

        // Logout
        async function logout() {
            try {
                await api('/api/logout', { method: 'POST' });
            } catch (error) {
                console.error('Logout error:', error);
            }

            authToken = null;
            currentUser = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('current_user');

            window.location.href = '/';
        }

        // Show/hide error helpers
        function showError(message) {
            errorMessage.textContent = message;
            errorSection.classList.remove('hidden');
        }

        function hideError() {
            errorSection.classList.add('hidden');
        }

        function showCreateContextError(message) {
            createContextError.textContent = message;
            createContextError.classList.remove('hidden');
        }

        function hideCreateContextError() {
            createContextError.classList.add('hidden');
        }

        // Event listeners
        contextSelect.addEventListener('change', () => {
            loadContextBtn.disabled = !contextSelect.value;
        });

        localeSelect.addEventListener('change', () => {
            if (contextSelect.value && currentContextSlug) {
                loadProfile(contextSelect.value);
            }
        });

        loadContextBtn.addEventListener('click', () => {
            if (contextSelect.value) {
                loadProfile(contextSelect.value);
            }
        });

        saveBtn.addEventListener('click', saveAttributes);
        addAttributeBtn.addEventListener('click', addAttribute);
        deactivateBtn.addEventListener('click', deactivateContext);
        createContextBtn.addEventListener('click', createContext);
        logoutBtn.addEventListener('click', logout);

        // Auto-generate slug from name
        newContextName.addEventListener('input', () => {
            const name = newContextName.value;
            newContextSlug.value = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        });

        // Create attribute
        createAttrBtn.addEventListener('click', createAttribute);

        // Auto-generate key from attribute name
        newAttrName.addEventListener('input', () => {
            const name = newAttrName.value;
            newAttrKey.value = name.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
        });

        // Start
        initGlobalUiLanguage();
        init();
    </script>
    @endverbatim
</body>
</html>
