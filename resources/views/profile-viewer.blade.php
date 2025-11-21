<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Identity Viewer</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white min-h-screen py-10 px-4">
    <div class="max-w-2xl mx-auto border-4 border-black">
        <nav class="bg-gray-100 border-b-4 border-black p-3 flex gap-4 font-mono text-sm uppercase">
            <a href="/" class="font-bold hover:underline" data-i18n="viewer">Viewer</a>
            <a href="/editor" class="font-bold hover:underline" data-i18n="editor">Editor</a>
            <a href="/teams" class="font-bold hover:underline" data-i18n="teams">Teams</a>
            <a href="/register" class="font-bold hover:underline" data-i18n="register">Register</a>
        </nav>
        <!-- Header -->
        <h1 class="bg-black text-white p-4 text-xl font-bold uppercase font-mono" data-i18n="identity_viewer">
            IDENTITY VIEWER
        </h1>

        <!-- Selection Section -->
        <div class="p-4 space-y-4 border-b-4 border-black">
            <div>
                <label class="block font-mono font-bold uppercase text-sm mb-2" data-i18n="username">USERNAME</label>
                <select id="username-select" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none rounded-none bg-white appearance-none cursor-pointer">
                    <option value="">-- Select User --</option>
                </select>
            </div>
            <div>
                <label class="block font-mono font-bold uppercase text-sm mb-2" data-i18n="context">CONTEXT</label>
                <select id="context-select" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none rounded-none bg-white appearance-none cursor-pointer" disabled>
                    <option value="">-- Select Context --</option>
                </select>
            </div>
            <div>
                <label class="block font-mono font-bold uppercase text-sm mb-2" data-i18n="language">LANGUAGE</label>
                <select id="locale-select" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none rounded-none bg-white appearance-none cursor-pointer">
                    <option value="en">English (en)</option>
                    <option value="ar">Arabic (ar)</option>
                    <option value="fr">French (fr)</option>
                    <option value="es">Spanish (es)</option>
                    <option value="de">German (de)</option>
                    <option value="zh">Chinese (zh)</option>
                    <option value="ja">Japanese (ja)</option>
                </select>
            </div>
            <button id="view-btn" class="w-full bg-black text-white p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-white hover:text-black cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" disabled data-i18n="view_profile">
                VIEW PROFILE
            </button>
        </div>

        <!-- Profile Data Section -->
        <div id="profile-section" class="hidden">
            <div class="bg-black text-white p-3 flex items-center justify-between">
                <h2 class="text-lg font-bold uppercase font-mono" data-i18n="profile_data">PROFILE DATA</h2>
                <div class="flex gap-1 flex-wrap">
                    <button data-format="json-ld" class="format-btn px-2 py-1 font-mono text-xs uppercase border-2 border-white bg-white text-black cursor-pointer">
                        JSON-LD
                    </button>
                    <button data-format="json" class="format-btn px-2 py-1 font-mono text-xs uppercase border-2 border-white bg-transparent text-white cursor-pointer">
                        JSON
                    </button>
                    <button data-format="rdf" class="format-btn px-2 py-1 font-mono text-xs uppercase border-2 border-white bg-transparent text-white cursor-pointer">
                        RDF
                    </button>
                    <button data-format="vcard" class="format-btn px-2 py-1 font-mono text-xs uppercase border-2 border-white bg-transparent text-white cursor-pointer">
                        vCard
                    </button>
                    <button data-format="csv" class="format-btn px-2 py-1 font-mono text-xs uppercase border-2 border-white bg-transparent text-white cursor-pointer">
                        CSV
                    </button>
                    <button data-format="xml" class="format-btn px-2 py-1 font-mono text-xs uppercase border-2 border-white bg-transparent text-white cursor-pointer">
                        XML
                    </button>
                </div>
            </div>

            <!-- Semantic View (JSON-LD) -->
            <div id="semantic-view">
                <div id="profile-data" class="divide-y-2 divide-black">
                    <!-- Profile fields will be inserted here -->
                </div>
                <div id="raw-json" class="border-t-2 border-black p-4 bg-gray-100">
                    <pre class="font-mono text-xs overflow-x-auto whitespace-pre-wrap"></pre>
                </div>
            </div>

            <!-- Simple View (Plain JSON) -->
            <div id="simple-view" class="hidden">
                <div id="simple-data" class="divide-y-2 divide-black">
                    <!-- Simple profile fields will be inserted here -->
                </div>
                <div id="simple-json" class="border-t-2 border-black p-4 bg-gray-100">
                    <pre class="font-mono text-xs overflow-x-auto whitespace-pre-wrap"></pre>
                </div>
            </div>

            <!-- RDF View -->
            <div id="rdf-view" class="hidden">
                <div class="border-t-2 border-black p-4 bg-gray-100">
                    <pre id="rdf-output" class="font-mono text-xs overflow-x-auto whitespace-pre-wrap"></pre>
                </div>
            </div>

            <!-- vCard View -->
            <div id="vcard-view" class="hidden">
                <div class="border-t-2 border-black p-4 bg-gray-100">
                    <pre id="vcard-output" class="font-mono text-xs overflow-x-auto whitespace-pre-wrap"></pre>
                </div>
            </div>

            <!-- CSV View -->
            <div id="csv-view" class="hidden">
                <div class="border-t-2 border-black p-4 bg-gray-100">
                    <pre id="csv-output" class="font-mono text-xs overflow-x-auto whitespace-pre-wrap"></pre>
                </div>
            </div>

            <!-- XML View -->
            <div id="xml-view" class="hidden">
                <div class="border-t-2 border-black p-4 bg-gray-100">
                    <pre id="xml-output" class="font-mono text-xs overflow-x-auto whitespace-pre-wrap"></pre>
                </div>
            </div>

            <div id="visibility-notice" class="border-t-4 border-black p-4 font-mono text-center bg-white hidden">
                <!-- Visibility notice will be shown here -->
            </div>
        </div>

        <!-- Error Section -->
        <div id="error-section" class="hidden p-4 border-t-4 border-black">
            <div class="border-[3px] border-black p-4 font-mono text-center bg-white">
                <span id="error-message"></span>
            </div>
        </div>

        <!-- Login Section -->
        <div class="border-t-4 border-black">
            <h2 class="bg-black text-white p-3 text-lg font-bold uppercase font-mono" data-i18n="login_optional">
                LOGIN (OPTIONAL)
            </h2>

            <!-- Login Form (shown when not logged in) -->
            <div id="login-form" class="p-4 space-y-4">
                <div>
                    <label class="block font-mono font-bold uppercase text-sm mb-2" data-i18n="email">EMAIL</label>
                    <input type="email" id="email-input" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="user@example.com">
                </div>
                <div>
                    <label class="block font-mono font-bold uppercase text-sm mb-2" data-i18n="password">PASSWORD</label>
                    <input type="password" id="password-input" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="********">
                </div>
                <div id="login-error" class="hidden border-[3px] border-black p-3 font-mono text-center bg-white">
                    <!-- Login error will be shown here -->
                </div>
                <button id="login-btn" class="w-full bg-black text-white p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-white hover:text-black cursor-pointer" data-i18n="login">
                    LOGIN
                </button>
            </div>

            <!-- Auth Status (shown when logged in) -->
            <div id="auth-status" class="p-4 hidden">
                <div class="flex items-center justify-between gap-4">
                    <span class="font-mono">
                        Logged in as: <strong id="logged-in-user"></strong>
                    </span>
                    <div class="flex gap-2">
                        <button id="copy-token-btn" class="bg-white text-black p-2 px-4 font-mono font-bold uppercase border-[3px] border-black hover:bg-black hover:text-white cursor-pointer text-xs" data-i18n="copy_token">
                            COPY TOKEN
                        </button>
                        <button id="logout-btn" class="bg-white text-black p-2 px-4 font-mono font-bold uppercase border-[3px] border-black hover:bg-black hover:text-white cursor-pointer text-xs" data-i18n="logout">
                            LOGOUT
                        </button>
                    </div>
                </div>
                <div id="token-display" class="hidden mt-3 border-[3px] border-black p-3 bg-gray-100 font-mono text-xs">
                    <div class="block md:hidden space-y-2">
                        <div class="font-bold">Bearer Token:</div>
                        <code id="token-value-mobile" class="block select-all break-all"></code>
                        <div class="text-gray-500">Use: Authorization: Bearer [token]</div>
                    </div>
                    <div class="hidden md:flex md:items-center md:gap-3">
                        <span class="font-bold shrink-0">Bearer Token:</span>
                        <code id="token-value-desktop" class="select-all break-all"></code>
                        <span class="text-gray-500 shrink-0">| Use: Authorization: Bearer [token]</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.i18n')
    @verbatim
    <script>
        // State
        let authToken = localStorage.getItem('auth_token');
        let currentUser = JSON.parse(localStorage.getItem('current_user') || 'null');
        let users = [];
        let contexts = [];
        let currentProfile = null;
        let currentFormat = 'json-ld';

        // DOM Elements
        const usernameSelect = document.getElementById('username-select');
        const contextSelect = document.getElementById('context-select');
        const viewBtn = document.getElementById('view-btn');
        const profileSection = document.getElementById('profile-section');
        const profileData = document.getElementById('profile-data');
        const visibilityNotice = document.getElementById('visibility-notice');
        const errorSection = document.getElementById('error-section');
        const errorMessage = document.getElementById('error-message');
        const loginForm = document.getElementById('login-form');
        const authStatus = document.getElementById('auth-status');
        const emailInput = document.getElementById('email-input');
        const passwordInput = document.getElementById('password-input');
        const loginBtn = document.getElementById('login-btn');
        const loginError = document.getElementById('login-error');
        const logoutBtn = document.getElementById('logout-btn');
        const loggedInUser = document.getElementById('logged-in-user');
        const copyTokenBtn = document.getElementById('copy-token-btn');
        const tokenDisplay = document.getElementById('token-display');
        const tokenValueMobile = document.getElementById('token-value-mobile');
        const tokenValueDesktop = document.getElementById('token-value-desktop');
        const semanticView = document.getElementById('semantic-view');
        const simpleView = document.getElementById('simple-view');
        const rawJson = document.getElementById('raw-json').querySelector('pre');
        const simpleData = document.getElementById('simple-data');
        const simpleJson = document.getElementById('simple-json').querySelector('pre');
        const formatButtons = document.querySelectorAll('.format-btn');
        const allFormatViews = {
            'json-ld': document.getElementById('semantic-view'),
            'json': document.getElementById('simple-view'),
            'rdf': document.getElementById('rdf-view'),
            'vcard': document.getElementById('vcard-view'),
            'csv': document.getElementById('csv-view'),
            'xml': document.getElementById('xml-view'),
        };

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
            updateAuthUI();
            await loadUsers();
        }

        // Update auth UI
        function updateAuthUI() {
            if (authToken && currentUser) {
                loginForm.classList.add('hidden');
                authStatus.classList.remove('hidden');
                loggedInUser.textContent = currentUser.email;
            } else {
                loginForm.classList.remove('hidden');
                authStatus.classList.add('hidden');
            }
        }

        // Load users
        async function loadUsers() {
            try {
                const response = await api('/api/viewer/users');
                const data = await response.json();
                users = Array.isArray(data) ? data : [];

                usernameSelect.innerHTML = '<option value="">-- Select User --</option>';
                users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.username;
                    option.textContent = user.username;
                    option.dataset.userId = user.id;
                    usernameSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Failed to load users:', error);
            }
        }

        // Load contexts for selected user
        async function loadContexts(userId) {
            try {
                const response = await api('/api/viewer/contexts');
                const data = await response.json();
                const allContexts = Array.isArray(data) ? data : [];

                // Filter contexts for selected user
                contexts = allContexts.filter(ctx => {
                    return String(ctx.user_id) === String(userId);
                });

                contextSelect.innerHTML = '<option value="">-- Select Context --</option>';
                contexts.forEach(ctx => {
                    const option = document.createElement('option');
                    option.value = ctx.slug;
                    option.textContent = ctx.name || ctx.slug;
                    contextSelect.appendChild(option);
                });

                contextSelect.disabled = false;
            } catch (error) {
                console.error('Failed to load contexts:', error);
            }
        }

        // View profile
        async function viewProfile() {
            const username = usernameSelect.value;
            const context = contextSelect.value;
            const locale = document.getElementById('locale-select').value;

            if (!username || !context) return;

            hideError();
            profileSection.classList.add('hidden');

            try {
                // Fetch JSON and JSON-LD first (needed for field display)
                const [jsonResponse, jsonLdResponse] = await Promise.all([
                    api(`/api/profiles/${username}/${context}?format=json&lang=${locale}`),
                    api(`/api/profiles/${username}/${context}?format=json-ld&lang=${locale}`)
                ]);

                if (!jsonResponse.ok) {
                    const error = await jsonResponse.json();
                    showError(error.message || 'Profile not found');
                    return;
                }

                const jsonProfile = await jsonResponse.json();
                const jsonLdProfile = await jsonLdResponse.json();

                // Fetch other formats in parallel (non-blocking)
                const [rdfResp, vcardResp, csvResp, xmlResp] = await Promise.all([
                    api(`/api/profiles/${username}/${context}?format=rdf&lang=${locale}`),
                    api(`/api/profiles/${username}/${context}?format=vcard&lang=${locale}`),
                    api(`/api/profiles/${username}/${context}?format=csv&lang=${locale}`),
                    api(`/api/profiles/${username}/${context}?format=xml&lang=${locale}`),
                ]);

                const rawFormats = {
                    rdf: await rdfResp.text(),
                    vcard: await vcardResp.text(),
                    csv: await csvResp.text(),
                    xml: await xmlResp.text(),
                };

                displayProfile(jsonProfile, jsonLdProfile, rawFormats);
            } catch (error) {
                showError('Failed to fetch profile');
                console.error(error);
            }
        }

        // Store profiles for all formats
        let jsonProfile = null;
        let jsonLdProfile = null;
        let apiLabels = {};

        // Display profile data
        function displayProfile(json, jsonLd, rawFormats) {
            jsonProfile = json;
            jsonLdProfile = jsonLd;
            currentProfile = json;
            apiLabels = json._labels || jsonLd._labels || {};

            // Semantic view (JSON-LD)
            displaySemanticView(jsonLd);

            // Simple view (plain JSON)
            displaySimpleView(json);

            // Raw format views
            document.getElementById('rdf-output').textContent = rawFormats.rdf;
            document.getElementById('vcard-output').textContent = rawFormats.vcard;
            document.getElementById('csv-output').textContent = rawFormats.csv;
            document.getElementById('xml-output').textContent = rawFormats.xml;

            // Show visibility notice based on auth state
            const noticeLocale = document.getElementById('locale-select').value;
            if (!authToken) {
                visibilityNotice.textContent = t('visibility_hidden', noticeLocale);
                visibilityNotice.classList.remove('hidden');
            } else if (currentUser && currentUser.username === usernameSelect.value) {
                visibilityNotice.textContent = t('visibility_owner', noticeLocale);
                visibilityNotice.classList.remove('hidden');
            } else {
                visibilityNotice.textContent = t('visibility_auth', noticeLocale);
                visibilityNotice.classList.remove('hidden');
            }

            profileSection.classList.remove('hidden');
        }

        // Get visibility color class
        function getVisibilityClass(visibility) {
            switch (visibility) {
                case 'protected': return 'bg-orange-100 border-l-4 border-orange-400';
                case 'private': return 'bg-red-100 border-l-4 border-red-400';
                default: return '';
            }
        }

        // Display semantic (JSON-LD) view
        function displaySemanticView(profile) {
            profileData.innerHTML = '';

            // Fields to skip (metadata) for display
            const skipFields = ['@context', '@type', '@id', 'context', '_labels'];

            const locale = document.getElementById('locale-select').value;
            for (const [key, fieldData] of Object.entries(profile)) {
                if (skipFields.includes(key)) continue;

                const row = document.createElement('div');
                const visibility = fieldData?.visibility || 'public';
                const value = fieldData?.value ?? fieldData;
                row.className = `p-3 font-mono ${getVisibilityClass(visibility)}`;

                const translatedKey = apiLabels[key] || tKey(key, locale);
                const displayValue = typeof value === 'object' ? JSON.stringify(value) : value;
                row.innerHTML = `<span class="font-bold">${translatedKey}:</span> ${displayValue || '<em class="opacity-50">' + t('not_set', locale) + '</em>'}`;

                profileData.appendChild(row);
            }

            // Show raw JSON-LD
            rawJson.textContent = JSON.stringify(profile, null, 2);
        }

        // Display simple (plain JSON) view
        function displaySimpleView(profile) {
            simpleData.innerHTML = '';

            // Create simple version without JSON-LD metadata
            const simpleProfile = {};
            const visibilityMap = {};
            const skipFields = ['@context', '@type', '@id', 'context', '_labels'];

            for (const [key, fieldData] of Object.entries(profile)) {
                if (skipFields.includes(key)) continue;
                const value = fieldData?.value ?? fieldData;
                const visibility = fieldData?.visibility || 'public';
                simpleProfile[key] = value;
                visibilityMap[key] = visibility;
            }

            const simpleLocale = document.getElementById('locale-select').value;
            for (const [key, value] of Object.entries(simpleProfile)) {
                const row = document.createElement('div');
                const visibility = visibilityMap[key] || 'public';
                row.className = `p-3 font-mono ${getVisibilityClass(visibility)}`;

                const translatedKey = apiLabels[key] || tKey(key, simpleLocale);
                const displayValue = typeof value === 'object' ? JSON.stringify(value) : value;
                row.innerHTML = `<span class="font-bold">${translatedKey}:</span> ${displayValue || '<em class="opacity-50">' + t('not_set', simpleLocale) + '</em>'}`;

                simpleData.appendChild(row);
            }

            // Show simple JSON (values only, no visibility metadata)
            simpleJson.textContent = JSON.stringify(simpleProfile, null, 2);
        }

        // Toggle format view
        function setFormat(format) {
            currentFormat = format;

            // Hide all views
            Object.values(allFormatViews).forEach(v => v.classList.add('hidden'));
            // Show selected view
            if (allFormatViews[format]) {
                allFormatViews[format].classList.remove('hidden');
            }

            // Update button styles
            formatButtons.forEach(btn => {
                if (btn.dataset.format === format) {
                    btn.classList.add('bg-white', 'text-black');
                    btn.classList.remove('bg-transparent', 'text-white');
                } else {
                    btn.classList.remove('bg-white', 'text-black');
                    btn.classList.add('bg-transparent', 'text-white');
                }
            });
        }

        // Show error
        function showError(message) {
            errorMessage.textContent = message;
            errorSection.classList.remove('hidden');
        }

        // Hide error
        function hideError() {
            errorSection.classList.add('hidden');
        }

        // Login
        async function login() {
            const email = emailInput.value.trim();
            const password = passwordInput.value;

            if (!email || !password) {
                showLoginError('Please enter email and password');
                return;
            }

            hideLoginError();

            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email, password }),
                });

                if (!response.ok) {
                    const error = await response.json();
                    showLoginError(error.message || error.errors?.email?.[0] || 'Login failed');
                    return;
                }

                const data = await response.json();
                authToken = data.token;
                currentUser = data.user;

                localStorage.setItem('auth_token', authToken);
                localStorage.setItem('current_user', JSON.stringify(currentUser));

                emailInput.value = '';
                passwordInput.value = '';

                updateAuthUI();

                // Refresh profile if one is displayed
                if (!profileSection.classList.contains('hidden')) {
                    viewProfile();
                }
            } catch (error) {
                showLoginError('Login failed');
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

            // Hide token display
            tokenDisplay.classList.add('hidden');
            copyTokenBtn.textContent = 'COPY TOKEN';

            updateAuthUI();

            // Refresh profile if one is displayed
            if (!profileSection.classList.contains('hidden')) {
                viewProfile();
            }
        }

        // Show login error
        function showLoginError(message) {
            loginError.textContent = message;
            loginError.classList.remove('hidden');
        }

        // Hide login error
        function hideLoginError() {
            loginError.classList.add('hidden');
        }

        // Event listeners
        usernameSelect.addEventListener('change', async (e) => {
            const selectedOption = e.target.selectedOptions[0];
            const userId = selectedOption?.dataset?.userId;

            contextSelect.innerHTML = '<option value="">-- Select Context --</option>';
            contextSelect.disabled = true;
            viewBtn.disabled = true;
            profileSection.classList.add('hidden');
            hideError();

            if (userId) {
                await loadContexts(userId);
            }
        });

        contextSelect.addEventListener('change', () => {
            viewBtn.disabled = !contextSelect.value;
        });

        viewBtn.addEventListener('click', viewProfile);
        loginBtn.addEventListener('click', login);
        logoutBtn.addEventListener('click', logout);

        // Copy token button
        copyTokenBtn.addEventListener('click', () => {
            if (authToken) {
                // Toggle token display
                if (tokenDisplay.classList.contains('hidden')) {
                    tokenValueMobile.textContent = authToken;
                    tokenValueDesktop.textContent = authToken;
                    tokenDisplay.classList.remove('hidden');
                    copyTokenBtn.textContent = 'HIDE TOKEN';

                    // Copy to clipboard
                    navigator.clipboard.writeText(authToken).then(() => {
                        copyTokenBtn.textContent = 'COPIED!';
                        setTimeout(() => {
                            copyTokenBtn.textContent = 'HIDE TOKEN';
                        }, 1500);
                    });
                } else {
                    tokenDisplay.classList.add('hidden');
                    copyTokenBtn.textContent = 'COPY TOKEN';
                }
            }
        });

        // Format toggle buttons
        formatButtons.forEach(btn => {
            btn.addEventListener('click', () => setFormat(btn.dataset.format));
        });

        // Enter key handlers
        emailInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') passwordInput.focus();
        });

        passwordInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') login();
        });

        // Translate all data-i18n elements
        function translatePage(locale) {
            applyDirection(locale);
            document.querySelectorAll('[data-i18n]').forEach(el => {
                el.textContent = t(el.dataset.i18n, locale);
            });
        }

        // Locale change handler
        document.getElementById('locale-select').addEventListener('change', (e) => {
            translatePage(e.target.value);
        });

        // Start
        init();
    </script>
    @endverbatim
</body>
</html>
