<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profile Editor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white min-h-screen py-10 px-4">
    <div class="max-w-2xl mx-auto border-4 border-black">
        <nav class="bg-gray-100 border-b-4 border-black p-3 flex gap-4 font-mono text-sm uppercase">
            <a href="/" class="font-bold hover:underline" data-i18n="viewer" data-tooltip="tip_nav_viewer" data-tooltip-pos="bottom">Viewer</a>
            <a href="/editor" class="font-bold hover:underline" data-i18n="editor" data-tooltip="tip_nav_editor" data-tooltip-pos="bottom">Editor</a>
            <a href="/teams" class="font-bold hover:underline" data-i18n="teams" data-tooltip="tip_nav_teams" data-tooltip-pos="bottom">Teams</a>
            <a href="/register" class="font-bold hover:underline" data-i18n="register" data-tooltip="tip_nav_register" data-tooltip-pos="bottom">Register</a>
        </nav>

        <!-- Header -->
        <h1 class="bg-black text-white p-4 text-xl font-bold uppercase font-mono" data-i18n="profile_editor">
            PROFILE EDITOR
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
                <span class="font-mono">
                    Logged in as: <strong id="logged-in-user"></strong>
                </span>
                <button id="logout-btn" class="bg-white text-black p-2 px-4 font-mono font-bold uppercase border-[3px] border-black hover:bg-black hover:text-white cursor-pointer text-xs" data-i18n="logout" data-tooltip="tip_logout">
                    LOGOUT
                </button>
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
                    <label class="block font-mono font-bold uppercase text-sm mb-2" data-i18n="language">LANGUAGE</label>
                    <select id="locale-select" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none rounded-none bg-white appearance-none cursor-pointer" data-tooltip="tip_locale_select">
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
        const localeSelect = document.getElementById('locale-select');
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
                const skipFields = ['@context', '@type', '@id', 'context', '_labels'];
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
            translatePage(localeSelect.value);
            if (contextSelect.value && currentContextSlug) {
                loadProfile(contextSelect.value);
            }
        });

        // Translate all data-i18n elements
        function translatePage(locale) {
            applyDirection(locale);
            document.querySelectorAll('[data-i18n]').forEach(el => {
                el.textContent = t(el.dataset.i18n, locale);
            });
            translateTooltips(locale);
        }

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

        // Start
        init();
    </script>
    @endverbatim
</body>
</html>
