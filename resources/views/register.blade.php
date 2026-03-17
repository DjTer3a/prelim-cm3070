<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>decentID - Register</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white min-h-screen py-10 px-4">
    <div class="max-w-2xl mx-auto border-4 border-black">
        <nav class="bg-gray-100 border-b-4 border-black p-3 flex gap-4 font-mono text-sm uppercase">
            <a href="/" class="font-bold hover:underline" data-tooltip="tip_nav_viewer" data-tooltip-pos="bottom">Viewer</a>
            <a href="/editor" class="font-bold hover:underline" data-tooltip="tip_nav_editor" data-tooltip-pos="bottom">Editor</a>
            <a href="/teams" class="font-bold hover:underline" data-tooltip="tip_nav_teams" data-tooltip-pos="bottom">Teams</a>
            <a href="/register" class="font-bold hover:underline" data-tooltip="tip_nav_register" data-tooltip-pos="bottom">Register</a>
        </nav>

        <!-- Header -->
        <h1 class="bg-black text-white p-4 text-xl font-bold uppercase font-mono">
            DECENTID REGISTER
        </h1>

        <!-- Already Logged In -->
        <div id="already-logged-in" class="hidden p-4 space-y-4">
            <div class="border-[3px] border-black p-4 font-mono text-center bg-white">
                <p>You are already logged in as <strong id="logged-in-name"></strong>.</p>
                <a href="/editor" class="inline-block mt-3 bg-black text-white p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-white hover:text-black cursor-pointer">
                    GO TO EDITOR
                </a>
            </div>
        </div>

        <!-- Registration Form -->
        <div id="register-form" class="p-4 space-y-4">
            <div>
                <label class="block font-mono font-bold uppercase text-sm mb-2">NAME</label>
                <input type="text" id="name-input" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="John Doe" data-tooltip="tip_reg_name">
            </div>
            <div>
                <label class="block font-mono font-bold uppercase text-sm mb-2">USERNAME</label>
                <input type="text" id="username-input" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="johndoe" data-tooltip="tip_reg_username">
            </div>
            <div>
                <label class="block font-mono font-bold uppercase text-sm mb-2">EMAIL</label>
                <input type="email" id="email-input" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="user@example.com" data-tooltip="tip_reg_email">
            </div>
            <div>
                <label class="block font-mono font-bold uppercase text-sm mb-2">PASSWORD</label>
                <input type="password" id="password-input" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="********" data-tooltip="tip_reg_password">
            </div>
            <div>
                <label class="block font-mono font-bold uppercase text-sm mb-2">CONFIRM PASSWORD</label>
                <input type="password" id="password-confirm-input" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="********" data-tooltip="tip_reg_password_confirm">
            </div>

            <!-- Validation Errors -->
            <div id="error-section" class="hidden">
                <div class="border-[3px] border-black p-4 font-mono bg-white">
                    <ul id="error-list" class="list-disc list-inside text-sm space-y-1"></ul>
                </div>
            </div>

            <button id="register-btn" class="w-full bg-black text-white p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-white hover:text-black cursor-pointer" data-tooltip="tip_register_btn">
                REGISTER
            </button>

            <div class="font-mono text-sm text-center">
                Already have an account? <a href="/" class="font-bold underline">Login on the Viewer page</a>
            </div>
        </div>
    </div>

    @include('partials.i18n')
    @verbatim
    <script>
        // State
        let authToken = localStorage.getItem('auth_token');
        let currentUser = JSON.parse(localStorage.getItem('current_user') || 'null');

        // DOM Elements
        const alreadyLoggedIn = document.getElementById('already-logged-in');
        const loggedInName = document.getElementById('logged-in-name');
        const registerForm = document.getElementById('register-form');
        const nameInput = document.getElementById('name-input');
        const usernameInput = document.getElementById('username-input');
        const emailInput = document.getElementById('email-input');
        const passwordInput = document.getElementById('password-input');
        const passwordConfirmInput = document.getElementById('password-confirm-input');
        const registerBtn = document.getElementById('register-btn');
        const errorSection = document.getElementById('error-section');
        const errorList = document.getElementById('error-list');

        // Initialize
        function init() {
            if (authToken && currentUser) {
                registerForm.classList.add('hidden');
                alreadyLoggedIn.classList.remove('hidden');
                loggedInName.textContent = currentUser.name || currentUser.email;
            }
            translateTooltips();
        }

        // Show errors
        function showErrors(errors) {
            errorList.innerHTML = '';
            if (typeof errors === 'string') {
                const li = document.createElement('li');
                li.textContent = errors;
                errorList.appendChild(li);
            } else if (typeof errors === 'object') {
                for (const [field, messages] of Object.entries(errors)) {
                    const msgs = Array.isArray(messages) ? messages : [messages];
                    msgs.forEach(msg => {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        errorList.appendChild(li);
                    });
                }
            }
            errorSection.classList.remove('hidden');
        }

        // Hide errors
        function hideErrors() {
            errorSection.classList.add('hidden');
            errorList.innerHTML = '';
        }

        // Register
        async function register() {
            const name = nameInput.value.trim();
            const username = usernameInput.value.trim();
            const email = emailInput.value.trim();
            const password = passwordInput.value;
            const password_confirmation = passwordConfirmInput.value;

            hideErrors();

            // Client-side validation
            const clientErrors = [];
            if (!name) clientErrors.push('Name is required');
            if (!username) clientErrors.push('Username is required');
            if (!email) clientErrors.push('Email is required');
            if (!password) clientErrors.push('Password is required');
            if (password !== password_confirmation) clientErrors.push('Passwords do not match');

            if (clientErrors.length > 0) {
                showErrors({ validation: clientErrors });
                return;
            }

            registerBtn.textContent = 'REGISTERING...';
            registerBtn.disabled = true;

            try {
                const response = await fetch('/api/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        name,
                        username,
                        email,
                        password,
                        password_confirmation,
                    }),
                });

                if (!response.ok) {
                    const data = await response.json();
                    if (data.errors) {
                        showErrors(data.errors);
                    } else {
                        showErrors(data.message || 'Registration failed');
                    }
                    registerBtn.textContent = 'REGISTER';
                    registerBtn.disabled = false;
                    return;
                }

                const data = await response.json();
                authToken = data.token;
                currentUser = data.user;

                localStorage.setItem('auth_token', authToken);
                localStorage.setItem('current_user', JSON.stringify(currentUser));

                window.location.href = '/editor';
            } catch (error) {
                showErrors('Registration failed. Please try again.');
                registerBtn.textContent = 'REGISTER';
                registerBtn.disabled = false;
                console.error(error);
            }
        }

        // Event listeners
        registerBtn.addEventListener('click', register);

        nameInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') usernameInput.focus();
        });

        usernameInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') emailInput.focus();
        });

        emailInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') passwordInput.focus();
        });

        passwordInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') passwordConfirmInput.focus();
        });

        passwordConfirmInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') register();
        });

        // Start
        init();
    </script>
    @endverbatim
</body>
</html>
