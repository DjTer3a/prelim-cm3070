<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\MediaType;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated
    ) {}

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $this->addAuthEndpoints($openApi);
        $this->addProfileEndpoints($openApi);
        $this->addAttributeEndpoints($openApi);
        $this->addTeamEndpoints($openApi);
        $this->addViewerEndpoints($openApi);

        return $openApi;
    }

    private function addAuthEndpoints(OpenApi $openApi): void
    {
        // POST /api/login
        $openApi->getPaths()->addPath('/api/login', new PathItem(
            post: new Operation(
                operationId: 'api_login',
                tags: ['Authentication'],
                summary: 'Login and get a Bearer token',
                description: 'Authenticate with email and password. Returns a Sanctum Bearer token for use in subsequent authenticated requests.',
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['email', 'password'],
                                'properties' => [
                                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'user@example.com'],
                                    'password' => ['type' => 'string', 'example' => 'password'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Response(
                        description: 'Login successful',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => ['type' => 'string', 'description' => 'Bearer token for Authorization header'],
                                        'user' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => ['type' => 'integer'],
                                                'name' => ['type' => 'string'],
                                                'username' => ['type' => 'string'],
                                                'email' => ['type' => 'string'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Response(description: 'Invalid credentials'),
                ],
            ),
        ));

        // POST /api/register
        $openApi->getPaths()->addPath('/api/register', new PathItem(
            post: new Operation(
                operationId: 'api_register',
                tags: ['Authentication'],
                summary: 'Register a new user account',
                description: 'Create a new user with a default "Personal" context. Returns a Sanctum Bearer token.',
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'username', 'email', 'password', 'password_confirmation'],
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => 'John Doe'],
                                    'username' => ['type' => 'string', 'example' => 'johndoe'],
                                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com'],
                                    'password' => ['type' => 'string', 'minLength' => 8, 'example' => 'password'],
                                    'password_confirmation' => ['type' => 'string', 'example' => 'password'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Registration successful',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => ['type' => 'string'],
                                        'user' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => ['type' => 'integer'],
                                                'name' => ['type' => 'string'],
                                                'username' => ['type' => 'string'],
                                                'email' => ['type' => 'string'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Response(description: 'Validation error'),
                ],
            ),
        ));

        // POST /api/logout
        $openApi->getPaths()->addPath('/api/logout', new PathItem(
            post: new Operation(
                operationId: 'api_logout',
                tags: ['Authentication'],
                summary: 'Logout and revoke token',
                description: 'Revokes the current Sanctum access token. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                responses: [
                    '200' => new Response(
                        description: 'Logged out successfully',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string', 'example' => 'Logged out successfully'],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Response(description: 'Unauthenticated'),
                ],
            ),
        ));
    }

    private function addProfileEndpoints(OpenApi $openApi): void
    {
        $formatParam = new Parameter('format', 'query', 'Output format', false, false, null, ['type' => 'string', 'enum' => ['json', 'json-ld', 'rdf', 'vcard', 'csv', 'xml'], 'default' => 'json']);
        $langParam = new Parameter('lang', 'query', 'Locale/language code', false, false, null, ['type' => 'string', 'default' => 'en', 'enum' => ['en', 'ar', 'fr', 'es', 'de', 'zh', 'ja']]);
        $usernameParam = new Parameter('username', 'path', 'The username', true, false, null, ['type' => 'string']);
        $contextParam = new Parameter('context', 'path', 'The context slug (e.g. work, personal, gaming)', true, false, null, ['type' => 'string']);

        $profileValueSchema = [
            'type' => 'object',
            'additionalProperties' => [
                'type' => 'object',
                'properties' => [
                    'value' => ['type' => 'string'],
                    'visibility' => ['type' => 'string', 'enum' => ['public', 'protected', 'private']],
                ],
            ],
        ];

        // GET /api/profiles/{username}/{context?}
        $openApi->getPaths()->addPath('/api/profiles/{username}/{context}', new PathItem(
            get: new Operation(
                operationId: 'api_profiles_show',
                tags: ['Profiles'],
                summary: 'Get profile data for a user context',
                description: 'Returns profile attributes with visibility filtering. Public attributes are always visible. Protected attributes require authentication. Private attributes require being the owner or a team member. Supports multiple output formats.',
                parameters: [$usernameParam, $contextParam, $formatParam, $langParam],
                responses: [
                    '200' => new Response(
                        description: 'Profile data',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'context' => ['type' => 'string'],
                                        '_labels' => ['type' => 'object', 'description' => 'Translated attribute labels'],
                                    ],
                                ],
                            ],
                            'application/ld+json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        '@context' => ['type' => 'object'],
                                        '@type' => ['type' => 'string'],
                                        '@id' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                            'text/turtle' => ['schema' => ['type' => 'string']],
                            'text/vcard' => ['schema' => ['type' => 'string']],
                            'text/csv' => ['schema' => ['type' => 'string']],
                            'application/xml' => ['schema' => ['type' => 'string']],
                        ]),
                    ),
                    '404' => new Response(description: 'User or context not found'),
                ],
            ),
            put: new Operation(
                operationId: 'api_profiles_update',
                tags: ['Profiles'],
                summary: 'Update profile attribute values',
                description: 'Update attribute values and visibility for a specific context. Only the profile owner can update. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                parameters: [$usernameParam, $contextParam, $langParam],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['values'],
                                'properties' => [
                                    'values' => [
                                        'type' => 'object',
                                        'description' => 'Attribute key-value pairs with visibility',
                                        'example' => [
                                            'display_name' => ['value' => 'John Doe', 'visibility' => 'public'],
                                            'email' => ['value' => 'john@example.com', 'visibility' => 'protected'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Response(description: 'Profile updated successfully'),
                    '403' => new Response(description: 'Only the profile owner can edit'),
                    '404' => new Response(description: 'User or context not found'),
                ],
            ),
            delete: new Operation(
                operationId: 'api_profiles_deleteValue',
                tags: ['Profiles'],
                summary: 'Delete a specific attribute value',
                description: 'Delete is handled via /api/profiles/{username}/{context}/{attributeKey}. See that endpoint.',
                deprecated: true,
            ),
        ));

        // DELETE /api/profiles/{username}/{context}/{attributeKey}
        $openApi->getPaths()->addPath('/api/profiles/{username}/{context}/{attributeKey}', new PathItem(
            delete: new Operation(
                operationId: 'api_profiles_delete_value',
                tags: ['Profiles'],
                summary: 'Delete a specific attribute value',
                description: 'Remove a specific attribute value from a context for the given locale. Only the profile owner can delete. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                parameters: [
                    $usernameParam,
                    $contextParam,
                    new Parameter('attributeKey', 'path', 'The attribute key (e.g. display_name, bio)', true, false, null, ['type' => 'string']),
                    $langParam,
                ],
                responses: [
                    '200' => new Response(description: 'Attribute value deleted'),
                    '403' => new Response(description: 'Only the profile owner can delete'),
                    '404' => new Response(description: 'User, context, or attribute not found'),
                ],
            ),
        ));

        // POST /api/profiles/{username}/contexts
        $openApi->getPaths()->addPath('/api/profiles/{username}/contexts', new PathItem(
            post: new Operation(
                operationId: 'api_profiles_createContext',
                tags: ['Profiles'],
                summary: 'Create a new context for a user',
                description: 'Create a new identity context (e.g. "freelance", "volunteer"). Only the profile owner can create contexts. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                parameters: [$usernameParam],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'slug'],
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => 'Freelance'],
                                    'slug' => ['type' => 'string', 'example' => 'freelance'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(description: 'Context created'),
                    '403' => new Response(description: 'Only the profile owner can create contexts'),
                    '422' => new Response(description: 'Context slug already exists'),
                ],
            ),
        ));

        // POST /api/profiles/{username}/photo
        $openApi->getPaths()->addPath('/api/profiles/{username}/photo', new PathItem(
            post: new Operation(
                operationId: 'api_profiles_uploadPhoto',
                tags: ['Profiles'],
                summary: 'Upload a profile photo',
                description: 'Upload a profile photo for the user. Accepts jpg, jpeg, png, gif, webp (max 2MB). Only the profile owner can upload. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                parameters: [$usernameParam],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['photo'],
                                'properties' => [
                                    'photo' => ['type' => 'string', 'format' => 'binary', 'description' => 'Image file (jpg, jpeg, png, gif, webp, max 2MB)'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Response(
                        description: 'Photo uploaded',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'url' => ['type' => 'string', 'format' => 'uri', 'description' => 'Public URL of the uploaded photo'],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '403' => new Response(description: 'Only the profile owner can upload photos'),
                    '404' => new Response(description: 'User not found'),
                    '422' => new Response(description: 'Validation error (invalid image or exceeds 2MB)'),
                ],
            ),
        ));

        // PUT/DELETE /api/profiles/{username}/contexts/{context}
        $openApi->getPaths()->addPath('/api/profiles/{username}/contexts/{context}', new PathItem(
            put: new Operation(
                operationId: 'api_profiles_updateContext',
                tags: ['Profiles'],
                summary: 'Update context metadata',
                description: 'Update context name, slug, or default status. Only the profile owner can update. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                parameters: [$usernameParam, $contextParam],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => ['type' => 'string'],
                                    'slug' => ['type' => 'string'],
                                    'is_default' => ['type' => 'boolean'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Response(description: 'Context updated'),
                    '403' => new Response(description: 'Only the profile owner can update'),
                    '404' => new Response(description: 'Context not found'),
                ],
            ),
            delete: new Operation(
                operationId: 'api_profiles_deleteContext',
                tags: ['Profiles'],
                summary: 'Deactivate a context',
                description: 'Soft-deletes a context by setting is_active to false. Only the profile owner can deactivate. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                parameters: [$usernameParam, $contextParam],
                responses: [
                    '200' => new Response(description: 'Context deactivated'),
                    '403' => new Response(description: 'Only the profile owner can deactivate'),
                    '404' => new Response(description: 'Context not found'),
                ],
            ),
        ));
    }

    private function addAttributeEndpoints(OpenApi $openApi): void
    {
        // POST /api/attributes
        $openApi->getPaths()->addPath('/api/attributes', new PathItem(
            post: new Operation(
                operationId: 'api_attributes_store',
                tags: ['Attributes'],
                summary: 'Create a custom profile attribute',
                description: 'Create a new custom profile attribute definition. The key must be unique and use only alphanumeric characters, dashes, and underscores. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['key', 'name', 'data_type'],
                                'properties' => [
                                    'key' => ['type' => 'string', 'example' => 'github_url', 'description' => 'Unique key (alphanumeric, dashes, underscores)'],
                                    'name' => ['type' => 'string', 'example' => 'GitHub URL'],
                                    'data_type' => ['type' => 'string', 'enum' => ['string', 'email', 'url', 'text'], 'example' => 'url'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Attribute created',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => ['type' => 'integer'],
                                        'key' => ['type' => 'string'],
                                        'name' => ['type' => 'string'],
                                        'data_type' => ['type' => 'string'],
                                        'schema_type' => ['type' => 'string', 'nullable' => true],
                                        'is_system' => ['type' => 'boolean'],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Response(description: 'Unauthenticated'),
                    '422' => new Response(description: 'Validation error (duplicate key or invalid data_type)'),
                ],
            ),
        ));
    }

    private function addTeamEndpoints(OpenApi $openApi): void
    {
        $slugParam = new Parameter('slug', 'path', 'The team slug', true, false, null, ['type' => 'string']);

        // GET/POST /api/teams
        $openApi->getPaths()->addPath('/api/teams', new PathItem(
            get: new Operation(
                operationId: 'api_teams_index',
                tags: ['Teams'],
                summary: 'List your teams',
                description: 'Returns all teams the authenticated user is a member of, with their role. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                responses: [
                    '200' => new Response(
                        description: 'List of teams',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => ['type' => 'integer'],
                                            'name' => ['type' => 'string'],
                                            'slug' => ['type' => 'string'],
                                            'description' => ['type' => 'string', 'nullable' => true],
                                            'owner_id' => ['type' => 'integer'],
                                            'owner' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer'],
                                                    'name' => ['type' => 'string'],
                                                    'username' => ['type' => 'string'],
                                                ],
                                            ],
                                            'role' => ['type' => 'string', 'enum' => ['owner', 'member']],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Response(description: 'Unauthenticated'),
                ],
            ),
            post: new Operation(
                operationId: 'api_teams_store',
                tags: ['Teams'],
                summary: 'Create a new team',
                description: 'Create a team and become its owner. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'slug'],
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => 'My Team'],
                                    'slug' => ['type' => 'string', 'example' => 'my-team'],
                                    'description' => ['type' => 'string', 'nullable' => true, 'example' => 'A team for collaboration'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(description: 'Team created'),
                    '401' => new Response(description: 'Unauthenticated'),
                    '422' => new Response(description: 'Validation error'),
                ],
            ),
        ));

        // GET/PUT/DELETE /api/teams/{slug}
        $openApi->getPaths()->addPath('/api/teams/{slug}', new PathItem(
            get: new Operation(
                operationId: 'api_teams_show',
                tags: ['Teams'],
                summary: 'Get team details with members',
                description: 'Returns team info, owner, and all members with their roles. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                parameters: [$slugParam],
                responses: [
                    '200' => new Response(
                        description: 'Team details',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => ['type' => 'integer'],
                                        'name' => ['type' => 'string'],
                                        'slug' => ['type' => 'string'],
                                        'description' => ['type' => 'string', 'nullable' => true],
                                        'owner' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => ['type' => 'integer'],
                                                'name' => ['type' => 'string'],
                                                'username' => ['type' => 'string'],
                                            ],
                                        ],
                                        'members' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer'],
                                                    'name' => ['type' => 'string'],
                                                    'username' => ['type' => 'string'],
                                                    'role' => ['type' => 'string', 'enum' => ['owner', 'member']],
                                                    'status' => ['type' => 'string', 'enum' => ['pending', 'accepted']],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Team not found'),
                ],
            ),
            put: new Operation(
                operationId: 'api_teams_update',
                tags: ['Teams'],
                summary: 'Update team details',
                description: 'Update team name, slug, or description. Only the team owner can update. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                parameters: [$slugParam],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => ['type' => 'string'],
                                    'slug' => ['type' => 'string'],
                                    'description' => ['type' => 'string', 'nullable' => true],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Response(description: 'Team updated'),
                    '403' => new Response(description: 'Only the team owner can update'),
                    '404' => new Response(description: 'Team not found'),
                ],
            ),
            delete: new Operation(
                operationId: 'api_teams_destroy',
                tags: ['Teams'],
                summary: 'Delete a team',
                description: 'Permanently delete a team. Only the team owner can delete. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                parameters: [$slugParam],
                responses: [
                    '200' => new Response(description: 'Team deleted'),
                    '403' => new Response(description: 'Only the team owner can delete'),
                    '404' => new Response(description: 'Team not found'),
                ],
            ),
        ));

        // POST /api/teams/{slug}/members
        $openApi->getPaths()->addPath('/api/teams/{slug}/members', new PathItem(
            post: new Operation(
                operationId: 'api_teams_addMember',
                tags: ['Teams'],
                summary: 'Invite a member to a team',
                description: 'Invite a user to join the team by username. The invitation will have a "pending" status until the user accepts or declines. Only the team owner can invite members. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                parameters: [$slugParam],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['username'],
                                'properties' => [
                                    'username' => ['type' => 'string', 'example' => 'johndoe'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(description: 'Invitation sent'),
                    '403' => new Response(description: 'Only the team owner can add members'),
                    '422' => new Response(description: 'User is already a team member'),
                ],
            ),
        ));

        // DELETE /api/teams/{slug}/members/{username}
        $openApi->getPaths()->addPath('/api/teams/{slug}/members/{username}', new PathItem(
            delete: new Operation(
                operationId: 'api_teams_removeMember',
                tags: ['Teams'],
                summary: 'Remove a member from a team',
                description: 'Remove a user from the team. Cannot remove the team owner. Only the team owner can remove members. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                parameters: [
                    $slugParam,
                    new Parameter('username', 'path', 'The username of the member to remove', true, false, null, ['type' => 'string']),
                ],
                responses: [
                    '200' => new Response(description: 'Member removed'),
                    '403' => new Response(description: 'Only the team owner can remove members'),
                    '422' => new Response(description: 'Cannot remove the team owner'),
                ],
            ),
        ));

        // GET /api/invitations
        $openApi->getPaths()->addPath('/api/invitations', new PathItem(
            get: new Operation(
                operationId: 'api_invitations_index',
                tags: ['Invitations'],
                summary: 'List pending team invitations',
                description: 'Returns all pending team invitations for the authenticated user. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                responses: [
                    '200' => new Response(
                        description: 'List of pending invitations',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => ['type' => 'integer'],
                                            'name' => ['type' => 'string'],
                                            'slug' => ['type' => 'string'],
                                            'description' => ['type' => 'string', 'nullable' => true],
                                            'owner' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer'],
                                                    'name' => ['type' => 'string'],
                                                    'username' => ['type' => 'string'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Response(description: 'Unauthenticated'),
                ],
            ),
        ));

        // POST /api/invitations/{slug}/accept
        $openApi->getPaths()->addPath('/api/invitations/{slug}/accept', new PathItem(
            post: new Operation(
                operationId: 'api_invitations_accept',
                tags: ['Invitations'],
                summary: 'Accept a team invitation',
                description: 'Accept a pending team invitation. Changes membership status from "pending" to "accepted". Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                parameters: [$slugParam],
                responses: [
                    '200' => new Response(
                        description: 'Invitation accepted',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string', 'example' => 'Invitation accepted.'],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'No pending invitation found'),
                ],
            ),
        ));

        // POST /api/invitations/{slug}/decline
        $openApi->getPaths()->addPath('/api/invitations/{slug}/decline', new PathItem(
            post: new Operation(
                operationId: 'api_invitations_decline',
                tags: ['Invitations'],
                summary: 'Decline a team invitation',
                description: 'Decline a pending team invitation. Removes the membership record. Requires Bearer token authentication.',
                security: [['Bearer Token' => []]],
                parameters: [$slugParam],
                responses: [
                    '200' => new Response(
                        description: 'Invitation declined',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string', 'example' => 'Invitation declined.'],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'No pending invitation found'),
                ],
            ),
        ));
    }

    private function addViewerEndpoints(OpenApi $openApi): void
    {
        // GET /api/viewer/users
        $openApi->getPaths()->addPath('/api/viewer/users', new PathItem(
            get: new Operation(
                operationId: 'api_viewer_users',
                tags: ['Viewer'],
                summary: 'List all users (simple JSON)',
                description: 'Returns a simple JSON array of all users with id, name, username, and email. No authentication required. Used by the frontend viewer.',
                responses: [
                    '200' => new Response(
                        description: 'List of users',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => ['type' => 'integer'],
                                            'name' => ['type' => 'string'],
                                            'username' => ['type' => 'string'],
                                            'email' => ['type' => 'string'],
                                            'profile_photo' => ['type' => 'string', 'format' => 'uri', 'nullable' => true],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ));

        // GET /api/viewer/contexts
        $openApi->getPaths()->addPath('/api/viewer/contexts', new PathItem(
            get: new Operation(
                operationId: 'api_viewer_contexts',
                tags: ['Viewer'],
                summary: 'List all active contexts (simple JSON)',
                description: 'Returns a simple JSON array of all active contexts with id, user_id, name, and slug. No authentication required. Used by the frontend viewer.',
                responses: [
                    '200' => new Response(
                        description: 'List of active contexts',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => ['type' => 'integer'],
                                            'user_id' => ['type' => 'integer'],
                                            'name' => ['type' => 'string'],
                                            'slug' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ));

        // GET /api/viewer/attributes
        $openApi->getPaths()->addPath('/api/viewer/attributes', new PathItem(
            get: new Operation(
                operationId: 'api_viewer_attributes',
                tags: ['Viewer'],
                summary: 'List all profile attribute definitions (simple JSON)',
                description: 'Returns a simple JSON array of all profile attribute definitions with id, key, name, translations, data_type, and schema_type. No authentication required. Used by the frontend editor.',
                responses: [
                    '200' => new Response(
                        description: 'List of profile attributes',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => ['type' => 'integer'],
                                            'key' => ['type' => 'string'],
                                            'name' => ['type' => 'string'],
                                            'translations' => ['type' => 'object', 'nullable' => true],
                                            'data_type' => ['type' => 'string'],
                                            'schema_type' => ['type' => 'string', 'nullable' => true],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ));
    }
}
