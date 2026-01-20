<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Team Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white min-h-screen py-10 px-4">
    <div class="max-w-2xl mx-auto border-4 border-black">
        <nav class="bg-gray-100 border-b-4 border-black p-3 flex gap-4 font-mono text-sm uppercase">
            <a href="/" class="font-bold hover:underline">Viewer</a>
            <a href="/editor" class="font-bold hover:underline">Editor</a>
            <a href="/teams" class="font-bold hover:underline">Teams</a>
            <a href="/register" class="font-bold hover:underline">Register</a>
        </nav>

        <!-- Header -->
        <h1 class="bg-black text-white p-4 text-xl font-bold uppercase font-mono">
            TEAM MANAGEMENT
        </h1>

        <!-- Not Logged In -->
        <div id="not-logged-in" class="hidden p-4 space-y-4">
            <div class="border-[3px] border-black p-4 font-mono text-center bg-white">
                <p>You must be logged in to manage teams.</p>
                <div class="flex gap-3 justify-center mt-3">
                    <a href="/" class="inline-block bg-black text-white p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-white hover:text-black cursor-pointer">
                        LOGIN
                    </a>
                    <a href="/register" class="inline-block bg-white text-black p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-black hover:text-white cursor-pointer">
                        REGISTER
                    </a>
                </div>
            </div>
        </div>

        <!-- Teams Content (shown when logged in) -->
        <div id="teams-content" class="hidden">
            <!-- Auth Status -->
            <div class="p-4 border-b-4 border-black flex items-center justify-between">
                <span class="font-mono">
                    Logged in as: <strong id="logged-in-user"></strong>
                </span>
                <button id="logout-btn" class="bg-white text-black p-2 px-4 font-mono font-bold uppercase border-[3px] border-black hover:bg-black hover:text-white cursor-pointer text-xs">
                    LOGOUT
                </button>
            </div>

            <!-- Teams List -->
            <div id="teams-list-section">
                <div class="bg-black text-white p-3">
                    <h2 class="text-lg font-bold uppercase font-mono">YOUR TEAMS</h2>
                </div>
                <div id="teams-container">
                    <!-- Teams will be inserted here -->
                </div>
                <div id="no-teams" class="hidden p-4 font-mono text-center text-sm">
                    You are not a member of any teams yet.
                </div>
            </div>

            <!-- Create Team Section -->
            <div class="border-t-4 border-black">
                <h2 class="bg-black text-white p-3 text-lg font-bold uppercase font-mono">
                    CREATE NEW TEAM
                </h2>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block font-mono font-bold uppercase text-sm mb-2">TEAM NAME</label>
                        <input type="text" id="new-team-name" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="My Team">
                    </div>
                    <div>
                        <label class="block font-mono font-bold uppercase text-sm mb-2">TEAM SLUG</label>
                        <input type="text" id="new-team-slug" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="my-team">
                    </div>
                    <div>
                        <label class="block font-mono font-bold uppercase text-sm mb-2">DESCRIPTION</label>
                        <input type="text" id="new-team-description" class="w-full border-[3px] border-black p-3 font-mono text-base focus:outline-none focus:ring-0 rounded-none bg-white" placeholder="A brief description of the team">
                    </div>
                    <div id="create-team-error" class="hidden border-[3px] border-black p-3 font-mono text-center bg-white text-sm"></div>
                    <button id="create-team-btn" class="w-full bg-black text-white p-3 font-mono font-bold uppercase border-[3px] border-black hover:bg-white hover:text-black cursor-pointer">
                        CREATE TEAM
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

    @verbatim
    <script>
        // State
        let authToken = localStorage.getItem('auth_token');
        let currentUser = JSON.parse(localStorage.getItem('current_user') || 'null');
        let teams = [];
        let allUsers = [];

        // DOM Elements
        const notLoggedIn = document.getElementById('not-logged-in');
        const teamsContent = document.getElementById('teams-content');
        const loggedInUser = document.getElementById('logged-in-user');
        const logoutBtn = document.getElementById('logout-btn');
        const teamsContainer = document.getElementById('teams-container');
        const noTeams = document.getElementById('no-teams');
        const newTeamName = document.getElementById('new-team-name');
        const newTeamSlug = document.getElementById('new-team-slug');
        const newTeamDescription = document.getElementById('new-team-description');
        const createTeamBtn = document.getElementById('create-team-btn');
        const createTeamError = document.getElementById('create-team-error');
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
                teamsContent.classList.add('hidden');
                return;
            }

            notLoggedIn.classList.add('hidden');
            teamsContent.classList.remove('hidden');
            loggedInUser.textContent = currentUser.name || currentUser.email;

            await loadAllUsers();
            await loadTeams();
        }

        // Load all users for the select box
        async function loadAllUsers() {
            try {
                const response = await api('/api/viewer/users');
                if (response.ok) {
                    allUsers = await response.json();
                }
            } catch (error) {
                console.error('Failed to load users:', error);
            }
        }

        // Load teams
        async function loadTeams() {
            try {
                const response = await api('/api/teams');

                if (!response.ok) {
                    showError('Failed to load teams');
                    return;
                }

                const data = await response.json();
                teams = Array.isArray(data) ? data : (data.data || []);

                displayTeams();
            } catch (error) {
                showError('Failed to load teams');
                console.error('Failed to load teams:', error);
            }
        }

        // Display teams
        function displayTeams() {
            teamsContainer.innerHTML = '';

            if (teams.length === 0) {
                noTeams.classList.remove('hidden');
                return;
            }

            noTeams.classList.add('hidden');

            teams.forEach(team => {
                const isOwner = team.owner_id && String(team.owner_id) === String(currentUser.id);
                const teamEl = document.createElement('div');
                teamEl.className = 'border-b-4 border-black';

                // Team header
                let html = `
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-mono font-bold text-base">${escapeHtml(team.name)}</span>
                                <span class="font-mono text-sm ml-2 opacity-60">(${escapeHtml(team.slug)})</span>
                            </div>
                            ${isOwner ? '<span class="font-mono text-xs bg-black text-white px-2 py-1 uppercase">Owner</span>' : '<span class="font-mono text-xs border-[2px] border-black px-2 py-1 uppercase">Member</span>'}
                        </div>
                `;

                if (team.description) {
                    html += `<p class="font-mono text-sm">${escapeHtml(team.description)}</p>`;
                }

                // Members section
                html += `
                        <div>
                            <label class="block font-mono font-bold uppercase text-xs mb-1">MEMBERS</label>
                            <div id="members-${team.id}" class="space-y-1">
                                <div class="font-mono text-sm">Loading members...</div>
                            </div>
                        </div>
                `;

                // Add member form (owner only)
                if (isOwner) {
                    const userOptions = allUsers
                        .filter(u => String(u.id) !== String(currentUser.id))
                        .map(u => `<option value="${escapeHtml(u.username)}">${escapeHtml(u.name)} (${escapeHtml(u.username)})</option>`)
                        .join('');

                    html += `
                        <div class="flex gap-2">
                            <select id="add-member-${team.id}" class="flex-1 border-[3px] border-black p-2 font-mono text-sm focus:outline-none focus:ring-0 rounded-none bg-white">
                                <option value="">-- Select a user --</option>
                                ${userOptions}
                            </select>
                            <button onclick="addMember(${team.id})" class="bg-black text-white p-2 px-4 font-mono font-bold uppercase border-[3px] border-black hover:bg-white hover:text-black cursor-pointer text-xs">
                                ADD
                            </button>
                        </div>
                        <div id="member-error-${team.id}" class="hidden border-[3px] border-black p-2 font-mono text-center bg-white text-xs"></div>
                    `;
                }

                html += `</div>`;
                teamEl.innerHTML = html;
                teamsContainer.appendChild(teamEl);

                // Load members for this team
                loadMembers(team);
            });
        }

        // Load members for a team
        async function loadMembers(team) {
            const membersEl = document.getElementById(`members-${team.id}`);
            if (!membersEl) return;

            const isOwner = team.owner_id && String(team.owner_id) === String(currentUser.id);

            try {
                const response = await api(`/api/teams/${team.slug}`);

                if (!response.ok) {
                    membersEl.innerHTML = '<div class="font-mono text-sm">Failed to load members</div>';
                    return;
                }

                const data = await response.json();
                const members = data.members || [];

                if (members.length === 0) {
                    membersEl.innerHTML = '<div class="font-mono text-sm opacity-60">No members yet</div>';
                    return;
                }

                membersEl.innerHTML = '';
                members.forEach(member => {
                    const memberUser = member.user || member;
                    const memberEl = document.createElement('div');
                    memberEl.className = 'flex items-center justify-between font-mono text-sm border-[2px] border-black p-2';

                    const username = memberUser.username || memberUser.name || 'Unknown';
                    const role = member.role || 'member';

                    let memberHtml = `
                        <span>
                            <strong>${escapeHtml(username)}</strong>
                            <span class="ml-1 opacity-60 text-xs uppercase">${escapeHtml(role)}</span>
                        </span>
                    `;

                    // Only show remove button for owners and non-owner members
                    if (isOwner && role !== 'owner') {
                        memberHtml += `
                            <button onclick="removeMember(${team.id}, '${escapeHtml(team.slug)}', '${escapeHtml(username)}')" class="bg-white text-black px-2 py-1 font-mono font-bold uppercase border-[2px] border-black hover:bg-black hover:text-white cursor-pointer text-xs">
                                REMOVE
                            </button>
                        `;
                    }

                    memberEl.innerHTML = memberHtml;
                    membersEl.appendChild(memberEl);
                });
            } catch (error) {
                membersEl.innerHTML = '<div class="font-mono text-sm">Failed to load members</div>';
                console.error('Failed to load members:', error);
            }
        }

        // Add member to team
        async function addMember(teamId) {
            const team = teams.find(t => t.id === teamId);
            if (!team) return;

            const input = document.getElementById(`add-member-${teamId}`);
            const errorEl = document.getElementById(`member-error-${teamId}`);
            const username = input.value.trim();

            if (!username) {
                showMemberError(teamId, 'Please select a user');
                return;
            }

            hideMemberError(teamId);

            try {
                const response = await api(`/api/teams/${team.slug}/members`, {
                    method: 'POST',
                    body: JSON.stringify({ username }),
                });

                if (!response.ok) {
                    const data = await response.json();
                    showMemberError(teamId, data.message || 'Failed to add member');
                    return;
                }

                input.value = '';
                await loadMembers(team);
            } catch (error) {
                showMemberError(teamId, 'Failed to add member');
                console.error(error);
            }
        }

        // Remove member from team
        async function removeMember(teamId, teamSlug, username) {
            if (!confirm(`Remove ${username} from the team?`)) return;

            try {
                const response = await api(`/api/teams/${teamSlug}/members/${username}`, {
                    method: 'DELETE',
                });

                if (!response.ok) {
                    const data = await response.json();
                    showMemberError(teamId, data.message || 'Failed to remove member');
                    return;
                }

                const team = teams.find(t => t.id === teamId);
                if (team) await loadMembers(team);
            } catch (error) {
                showMemberError(teamId, 'Failed to remove member');
                console.error(error);
            }
        }

        // Create team
        async function createTeam() {
            const name = newTeamName.value.trim();
            const slug = newTeamSlug.value.trim();
            const description = newTeamDescription.value.trim();

            if (!name || !slug) {
                showCreateTeamError('Name and slug are required');
                return;
            }

            createTeamBtn.textContent = 'CREATING...';
            createTeamBtn.disabled = true;
            hideCreateTeamError();

            try {
                const response = await api('/api/teams', {
                    method: 'POST',
                    body: JSON.stringify({ name, slug, description }),
                });

                if (!response.ok) {
                    const data = await response.json();
                    const msg = data.message || data.errors?.slug?.[0] || data.errors?.name?.[0] || 'Failed to create team';
                    showCreateTeamError(msg);
                    createTeamBtn.textContent = 'CREATE TEAM';
                    createTeamBtn.disabled = false;
                    return;
                }

                newTeamName.value = '';
                newTeamSlug.value = '';
                newTeamDescription.value = '';
                createTeamBtn.textContent = 'CREATE TEAM';
                createTeamBtn.disabled = false;

                // Reload teams
                await loadTeams();
            } catch (error) {
                showCreateTeamError('Failed to create team');
                createTeamBtn.textContent = 'CREATE TEAM';
                createTeamBtn.disabled = false;
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

        // Escape HTML
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Error helpers
        function showError(message) {
            errorMessage.textContent = message;
            errorSection.classList.remove('hidden');
        }

        function hideError() {
            errorSection.classList.add('hidden');
        }

        function showCreateTeamError(message) {
            createTeamError.textContent = message;
            createTeamError.classList.remove('hidden');
        }

        function hideCreateTeamError() {
            createTeamError.classList.add('hidden');
        }

        function showMemberError(teamId, message) {
            const el = document.getElementById(`member-error-${teamId}`);
            if (el) {
                el.textContent = message;
                el.classList.remove('hidden');
            }
        }

        function hideMemberError(teamId) {
            const el = document.getElementById(`member-error-${teamId}`);
            if (el) {
                el.classList.add('hidden');
            }
        }

        // Event listeners
        createTeamBtn.addEventListener('click', createTeam);
        logoutBtn.addEventListener('click', logout);

        // Auto-generate slug from name
        newTeamName.addEventListener('input', () => {
            const name = newTeamName.value;
            newTeamSlug.value = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        });

        // Start
        init();
    </script>
    @endverbatim
</body>
</html>
