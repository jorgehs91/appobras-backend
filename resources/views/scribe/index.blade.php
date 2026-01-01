<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Obras 360 API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://localhost:8000";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.6.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.6.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-endpoints" class="tocify-header">
                <li class="tocify-item level-1" data-unique="endpoints">
                    <a href="#endpoints">Endpoints</a>
                </li>
                                    <ul id="tocify-subheader-endpoints" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="endpoints-GETapi-documentation">
                                <a href="#endpoints-GETapi-documentation">Handles the API request and renders the Swagger documentation view.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-docs">
                                <a href="#endpoints-GETapi-docs">Handles requests for API documentation and returns the corresponding file content.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-docs-asset--asset-">
                                <a href="#endpoints-GETapi-docs-asset--asset-">Serves a specific documentation asset for the Swagger UI interface.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-oauth2-callback">
                                <a href="#endpoints-GETapi-oauth2-callback">Handles the OAuth2 callback and retrieves the required file for the redirect.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-auth-register">
                                <a href="#endpoints-POSTapi-v1-auth-register">POST api/v1/auth/register</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-auth-login">
                                <a href="#endpoints-POSTapi-v1-auth-login">POST api/v1/auth/login</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-auth-forgot">
                                <a href="#endpoints-POSTapi-v1-auth-forgot">POST api/v1/auth/forgot</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-auth-reset">
                                <a href="#endpoints-POSTapi-v1-auth-reset">POST api/v1/auth/reset</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-auth-logout">
                                <a href="#endpoints-POSTapi-v1-auth-logout">POST api/v1/auth/logout</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-auth-refresh">
                                <a href="#endpoints-POSTapi-v1-auth-refresh">POST api/v1/auth/refresh</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-companies">
                                <a href="#endpoints-GETapi-v1-companies">GET api/v1/companies</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-companies">
                                <a href="#endpoints-POSTapi-v1-companies">POST api/v1/companies</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-companies--company_id--invites">
                                <a href="#endpoints-POSTapi-v1-companies--company_id--invites">POST api/v1/companies/{company_id}/invites</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-invites--token--accept">
                                <a href="#endpoints-POSTapi-v1-invites--token--accept">POST api/v1/invites/{token}/accept</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-invites-project--token--accept">
                                <a href="#endpoints-POSTapi-v1-invites-project--token--accept">POST api/v1/invites/project/{token}/accept</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-me-switch-company">
                                <a href="#endpoints-POSTapi-v1-me-switch-company">POST api/v1/me/switch-company</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-me-switch-project">
                                <a href="#endpoints-POSTapi-v1-me-switch-project">POST api/v1/me/switch-project</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PUTapi-v1-user-preferences">
                                <a href="#endpoints-PUTapi-v1-user-preferences">PUT api/v1/user/preferences</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-user-expo-token">
                                <a href="#endpoints-POSTapi-v1-user-expo-token">POST api/v1/user/expo-token</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-notifications">
                                <a href="#endpoints-GETapi-v1-notifications">GET api/v1/notifications</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PATCHapi-v1-notifications--id--read">
                                <a href="#endpoints-PATCHapi-v1-notifications--id--read">PATCH api/v1/notifications/{id}/read</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-admin-roles">
                                <a href="#endpoints-GETapi-v1-admin-roles">GET api/v1/admin/roles</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-admin-permissions">
                                <a href="#endpoints-GETapi-v1-admin-permissions">GET api/v1/admin/permissions</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-admin-roles--role_id--assign">
                                <a href="#endpoints-POSTapi-v1-admin-roles--role_id--assign">POST api/v1/admin/roles/{role_id}/assign</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-admin-roles--role_id--revoke">
                                <a href="#endpoints-POSTapi-v1-admin-roles--role_id--revoke">POST api/v1/admin/roles/{role_id}/revoke</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-admin-audit-logs">
                                <a href="#endpoints-GETapi-v1-admin-audit-logs">GET api/v1/admin/audit-logs</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-projects">
                                <a href="#endpoints-GETapi-v1-projects">GET api/v1/projects</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-projects">
                                <a href="#endpoints-POSTapi-v1-projects">POST api/v1/projects</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-projects--project_id-">
                                <a href="#endpoints-GETapi-v1-projects--project_id-">GET api/v1/projects/{project_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-contractors">
                                <a href="#endpoints-GETapi-v1-contractors">GET api/v1/contractors</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-contractors">
                                <a href="#endpoints-POSTapi-v1-contractors">POST api/v1/contractors</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PUTapi-v1-contractors--contractor_id-">
                                <a href="#endpoints-PUTapi-v1-contractors--contractor_id-">PUT api/v1/contractors/{contractor_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-DELETEapi-v1-contractors--contractor_id-">
                                <a href="#endpoints-DELETEapi-v1-contractors--contractor_id-">DELETE api/v1/contractors/{contractor_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-suppliers">
                                <a href="#endpoints-GETapi-v1-suppliers">GET api/v1/suppliers</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-suppliers--supplier_id-">
                                <a href="#endpoints-GETapi-v1-suppliers--supplier_id-">GET api/v1/suppliers/{supplier_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-suppliers">
                                <a href="#endpoints-POSTapi-v1-suppliers">POST api/v1/suppliers</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PUTapi-v1-suppliers--supplier_id-">
                                <a href="#endpoints-PUTapi-v1-suppliers--supplier_id-">PUT api/v1/suppliers/{supplier_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-DELETEapi-v1-suppliers--supplier_id-">
                                <a href="#endpoints-DELETEapi-v1-suppliers--supplier_id-">DELETE api/v1/suppliers/{supplier_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-projects--project_id--phases">
                                <a href="#endpoints-GETapi-v1-projects--project_id--phases">GET api/v1/projects/{project_id}/phases</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-projects--project_id--phases">
                                <a href="#endpoints-POSTapi-v1-projects--project_id--phases">POST api/v1/projects/{project_id}/phases</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PUTapi-v1-phases--phase_id-">
                                <a href="#endpoints-PUTapi-v1-phases--phase_id-">PUT api/v1/phases/{phase_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-DELETEapi-v1-phases--phase_id-">
                                <a href="#endpoints-DELETEapi-v1-phases--phase_id-">DELETE api/v1/phases/{phase_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-projects--project_id--tasks">
                                <a href="#endpoints-GETapi-v1-projects--project_id--tasks">GET api/v1/projects/{project_id}/tasks</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-projects--project_id--tasks">
                                <a href="#endpoints-POSTapi-v1-projects--project_id--tasks">POST api/v1/projects/{project_id}/tasks</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PATCHapi-v1-projects--project_id--tasks-bulk">
                                <a href="#endpoints-PATCHapi-v1-projects--project_id--tasks-bulk">PATCH api/v1/projects/{project_id}/tasks/bulk</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PUTapi-v1-tasks--task_id-">
                                <a href="#endpoints-PUTapi-v1-tasks--task_id-">PUT api/v1/tasks/{task_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PATCHapi-v1-tasks--task_id--status">
                                <a href="#endpoints-PATCHapi-v1-tasks--task_id--status">PATCH api/v1/tasks/{task_id}/status</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PATCHapi-v1-tasks--task_id--dependencies">
                                <a href="#endpoints-PATCHapi-v1-tasks--task_id--dependencies">PATCH api/v1/tasks/{task_id}/dependencies</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-DELETEapi-v1-tasks--task_id-">
                                <a href="#endpoints-DELETEapi-v1-tasks--task_id-">DELETE api/v1/tasks/{task_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-task-dependencies">
                                <a href="#endpoints-GETapi-v1-task-dependencies">GET api/v1/task-dependencies</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-projects--project_id--task-dependencies">
                                <a href="#endpoints-POSTapi-v1-projects--project_id--task-dependencies">POST api/v1/projects/{project_id}/task-dependencies</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-projects--project_id--task-dependencies-bulk">
                                <a href="#endpoints-POSTapi-v1-projects--project_id--task-dependencies-bulk">POST api/v1/projects/{project_id}/task-dependencies/bulk</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PUTapi-v1-task-dependencies--taskDependency_id-">
                                <a href="#endpoints-PUTapi-v1-task-dependencies--taskDependency_id-">PUT api/v1/task-dependencies/{taskDependency_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-DELETEapi-v1-task-dependencies--taskDependency_id-">
                                <a href="#endpoints-DELETEapi-v1-task-dependencies--taskDependency_id-">DELETE api/v1/task-dependencies/{taskDependency_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-projects--project_id--documents">
                                <a href="#endpoints-GETapi-v1-projects--project_id--documents">GET api/v1/projects/{project_id}/documents</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-projects--project_id--documents">
                                <a href="#endpoints-POSTapi-v1-projects--project_id--documents">POST api/v1/projects/{project_id}/documents</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-documents--document_id-">
                                <a href="#endpoints-GETapi-v1-documents--document_id-">GET api/v1/documents/{document_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-documents--document_id--download">
                                <a href="#endpoints-GETapi-v1-documents--document_id--download">GET api/v1/documents/{document_id}/download</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-DELETEapi-v1-documents--document_id-">
                                <a href="#endpoints-DELETEapi-v1-documents--document_id-">DELETE api/v1/documents/{document_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-projects--project_id--members">
                                <a href="#endpoints-GETapi-v1-projects--project_id--members">GET api/v1/projects/{project_id}/members</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-projects--project_id--members">
                                <a href="#endpoints-POSTapi-v1-projects--project_id--members">POST api/v1/projects/{project_id}/members</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PATCHapi-v1-projects--project_id--members--userId-">
                                <a href="#endpoints-PATCHapi-v1-projects--project_id--members--userId-">PATCH api/v1/projects/{project_id}/members/{userId}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-DELETEapi-v1-projects--project_id--members--userId-">
                                <a href="#endpoints-DELETEapi-v1-projects--project_id--members--userId-">DELETE api/v1/projects/{project_id}/members/{userId}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-projects--project_id--progress">
                                <a href="#endpoints-GETapi-v1-projects--project_id--progress">GET api/v1/projects/{project_id}/progress</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-dashboard-stats">
                                <a href="#endpoints-GETapi-v1-dashboard-stats">GET api/v1/dashboard/stats</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-projects--project_id--budgets">
                                <a href="#endpoints-GETapi-v1-projects--project_id--budgets">GET api/v1/projects/{project_id}/budgets</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-projects--project_id--budgets">
                                <a href="#endpoints-POSTapi-v1-projects--project_id--budgets">POST api/v1/projects/{project_id}/budgets</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-projects--project_id--budget-summary">
                                <a href="#endpoints-GETapi-v1-projects--project_id--budget-summary">GET api/v1/projects/{project_id}/budget/summary</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-budgets--budget_id-">
                                <a href="#endpoints-GETapi-v1-budgets--budget_id-">GET api/v1/budgets/{budget_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PUTapi-v1-budgets--budget_id-">
                                <a href="#endpoints-PUTapi-v1-budgets--budget_id-">PUT api/v1/budgets/{budget_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-DELETEapi-v1-budgets--budget_id-">
                                <a href="#endpoints-DELETEapi-v1-budgets--budget_id-">DELETE api/v1/budgets/{budget_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-projects--project_id--budgets--budget_id--cost-items">
                                <a href="#endpoints-GETapi-v1-projects--project_id--budgets--budget_id--cost-items">GET api/v1/projects/{project_id}/budgets/{budget_id}/cost-items</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items">
                                <a href="#endpoints-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items">POST api/v1/projects/{project_id}/budgets/{budget_id}/cost-items</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-cost-items--costItem_id-">
                                <a href="#endpoints-GETapi-v1-cost-items--costItem_id-">GET api/v1/cost-items/{costItem_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PUTapi-v1-cost-items--costItem_id-">
                                <a href="#endpoints-PUTapi-v1-cost-items--costItem_id-">PUT api/v1/cost-items/{costItem_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-DELETEapi-v1-cost-items--costItem_id-">
                                <a href="#endpoints-DELETEapi-v1-cost-items--costItem_id-">DELETE api/v1/cost-items/{costItem_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-projects--project_id--expenses">
                                <a href="#endpoints-GETapi-v1-projects--project_id--expenses">GET api/v1/projects/{project_id}/expenses</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-projects--project_id--expenses">
                                <a href="#endpoints-POSTapi-v1-projects--project_id--expenses">POST api/v1/projects/{project_id}/expenses</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-projects--project_id--pvxr">
                                <a href="#endpoints-GETapi-v1-projects--project_id--pvxr">GET api/v1/projects/{project_id}/pvxr</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-expenses--expense_id-">
                                <a href="#endpoints-GETapi-v1-expenses--expense_id-">GET api/v1/expenses/{expense_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PUTapi-v1-expenses--expense_id-">
                                <a href="#endpoints-PUTapi-v1-expenses--expense_id-">PUT api/v1/expenses/{expense_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-DELETEapi-v1-expenses--expense_id-">
                                <a href="#endpoints-DELETEapi-v1-expenses--expense_id-">DELETE api/v1/expenses/{expense_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-expenses--expense_id--receipt">
                                <a href="#endpoints-GETapi-v1-expenses--expense_id--receipt">GET api/v1/expenses/{expense_id}/receipt</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-projects--project_id--purchase-requests">
                                <a href="#endpoints-GETapi-v1-projects--project_id--purchase-requests">GET api/v1/projects/{project_id}/purchase-requests</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-projects--project_id--purchase-requests">
                                <a href="#endpoints-POSTapi-v1-projects--project_id--purchase-requests">POST api/v1/projects/{project_id}/purchase-requests</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-purchase-requests--purchaseRequest_id-">
                                <a href="#endpoints-GETapi-v1-purchase-requests--purchaseRequest_id-">GET api/v1/purchase-requests/{purchaseRequest_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PUTapi-v1-purchase-requests--purchaseRequest_id-">
                                <a href="#endpoints-PUTapi-v1-purchase-requests--purchaseRequest_id-">PUT api/v1/purchase-requests/{purchaseRequest_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-DELETEapi-v1-purchase-requests--purchaseRequest_id-">
                                <a href="#endpoints-DELETEapi-v1-purchase-requests--purchaseRequest_id-">DELETE api/v1/purchase-requests/{purchaseRequest_id}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-purchase-requests--purchaseRequest_id--submit">
                                <a href="#endpoints-POSTapi-v1-purchase-requests--purchaseRequest_id--submit">POST api/v1/purchase-requests/{purchaseRequest_id}/submit</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-purchase-requests--purchaseRequest_id--approve">
                                <a href="#endpoints-POSTapi-v1-purchase-requests--purchaseRequest_id--approve">POST api/v1/purchase-requests/{purchaseRequest_id}/approve</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-purchase-requests--purchaseRequest_id--reject">
                                <a href="#endpoints-POSTapi-v1-purchase-requests--purchaseRequest_id--reject">POST api/v1/purchase-requests/{purchaseRequest_id}/reject</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: January 1, 2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>

        <h1 id="endpoints">Endpoints</h1>

    

                                <h2 id="endpoints-GETapi-documentation">Handles the API request and renders the Swagger documentation view.</h2>

<p>
</p>



<span id="example-requests-GETapi-documentation">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/documentation" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/documentation"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-documentation">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">&lt;!DOCTYPE html&gt;
&lt;html lang=&quot;en&quot;&gt;
&lt;head&gt;
    &lt;meta charset=&quot;UTF-8&quot;&gt;
    &lt;title&gt;L5 Swagger UI&lt;/title&gt;
    &lt;link rel=&quot;stylesheet&quot; type=&quot;text/css&quot; href=&quot;http://localhost:8000/api/docs/asset/swagger-ui.css?v=295c0e8b75154a3a99d297333d3e9cb9&quot;&gt;
    &lt;link rel=&quot;icon&quot; type=&quot;image/png&quot; href=&quot;http://localhost:8000/api/docs/asset/favicon-32x32.png?v=40d4f2c38d1cd854ad463f16373cbcb6&quot; sizes=&quot;32x32&quot;/&gt;
    &lt;link rel=&quot;icon&quot; type=&quot;image/png&quot; href=&quot;http://localhost:8000/api/docs/asset/favicon-16x16.png?v=f0ae831196d55d8f4115b6c5e8ec5384&quot; sizes=&quot;16x16&quot;/&gt;
    &lt;style&gt;
    html
    {
        box-sizing: border-box;
        overflow: -moz-scrollbars-vertical;
        overflow-y: scroll;
    }
    *,
    *:before,
    *:after
    {
        box-sizing: inherit;
    }

    body {
      margin:0;
      background: #fafafa;
    }
    &lt;/style&gt;
    &lt;/head&gt;

&lt;body &gt;
&lt;div id=&quot;swagger-ui&quot;&gt;&lt;/div&gt;

&lt;script src=&quot;http://localhost:8000/api/docs/asset/swagger-ui-bundle.js?v=3abe734b0cfda5cb259bd02f796042d1&quot;&gt;&lt;/script&gt;
&lt;script src=&quot;http://localhost:8000/api/docs/asset/swagger-ui-standalone-preset.js?v=e309748b3af627b5137cb7a559853117&quot;&gt;&lt;/script&gt;
&lt;script&gt;
    window.onload = function() {
        const urls = [];

                    urls.push({name: &quot;L5 Swagger UI&quot;, url: &quot;http://localhost:8000/api/docs?api-docs.json&quot;});
        
        // Build a system
        const ui = SwaggerUIBundle({
            dom_id: &#039;#swagger-ui&#039;,
            urls: urls,
            &quot;urls.primaryName&quot;: &quot;L5 Swagger UI&quot;,
            operationsSorter: null,
            configUrl: null,
            validatorUrl: null,
            oauth2RedirectUrl: &quot;http://localhost:8000/api/oauth2-callback&quot;,

            requestInterceptor: function(request) {
                request.headers[&#039;X-CSRF-TOKEN&#039;] = &#039;&#039;;
                return request;
            },

            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],

            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],

            layout: &quot;StandaloneLayout&quot;,
            docExpansion : &quot;none&quot;,
            deepLinking: true,
            filter: true,
            persistAuthorization: &quot;false&quot;,

        })

        window.ui = ui

            }
&lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;
</code>
 </pre>
    </span>
<span id="execution-results-GETapi-documentation" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-documentation"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-documentation"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-documentation" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-documentation">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-documentation" data-method="GET"
      data-path="api/documentation"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-documentation', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-documentation"
                    onclick="tryItOut('GETapi-documentation');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-documentation"
                    onclick="cancelTryOut('GETapi-documentation');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-documentation"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/documentation</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-documentation"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-documentation"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-docs">Handles requests for API documentation and returns the corresponding file content.</h2>

<p>
</p>



<span id="example-requests-GETapi-docs">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/docs" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/docs"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-docs">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">content-type: application/json
cache-control: no-cache, private
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;openapi&quot;: &quot;3.0.0&quot;,
    &quot;info&quot;: {
        &quot;title&quot;: &quot;AppObras API&quot;,
        &quot;description&quot;: &quot;API v1 de autentica&ccedil;&atilde;o e conta&quot;,
        &quot;version&quot;: &quot;1.0.0&quot;
    },
    &quot;servers&quot;: [
        {
            &quot;url&quot;: &quot;/api&quot;,
            &quot;description&quot;: &quot;API base&quot;
        }
    ],
    &quot;paths&quot;: {
        &quot;/api/v1/admin/roles&quot;: {
            &quot;get&quot;: {
                &quot;tags&quot;: [
                    &quot;Admin&quot;
                ],
                &quot;summary&quot;: &quot;Listar roles&quot;,
                &quot;description&quot;: &quot;Retorna todas as roles dispon&iacute;veis no sistema&quot;,
                &quot;operationId&quot;: &quot;90631fac9c850d5d94147a2809e94f31&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Lista de roles&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;array&quot;,
                                            &quot;items&quot;: {
                                                &quot;properties&quot;: {
                                                    &quot;id&quot;: {
                                                        &quot;type&quot;: &quot;integer&quot;,
                                                        &quot;example&quot;: 1
                                                    },
                                                    &quot;name&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;,
                                                        &quot;example&quot;: &quot;Admin Obra&quot;
                                                    },
                                                    &quot;guard_name&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;,
                                                        &quot;example&quot;: &quot;sanctum&quot;
                                                    }
                                                },
                                                &quot;type&quot;: &quot;object&quot;
                                            }
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/admin/permissions&quot;: {
            &quot;get&quot;: {
                &quot;tags&quot;: [
                    &quot;Admin&quot;
                ],
                &quot;summary&quot;: &quot;Listar permiss&otilde;es&quot;,
                &quot;description&quot;: &quot;Retorna todas as permiss&otilde;es dispon&iacute;veis no sistema&quot;,
                &quot;operationId&quot;: &quot;33596e41940f5354d0258fc4534cf10d&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Lista de permiss&otilde;es&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;array&quot;,
                                            &quot;items&quot;: {
                                                &quot;properties&quot;: {
                                                    &quot;id&quot;: {
                                                        &quot;type&quot;: &quot;integer&quot;,
                                                        &quot;example&quot;: 1
                                                    },
                                                    &quot;name&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;,
                                                        &quot;example&quot;: &quot;users.update&quot;
                                                    },
                                                    &quot;guard_name&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;,
                                                        &quot;example&quot;: &quot;sanctum&quot;
                                                    }
                                                },
                                                &quot;type&quot;: &quot;object&quot;
                                            }
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/admin/roles/{role}/assign&quot;: {
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Admin&quot;
                ],
                &quot;summary&quot;: &quot;Atribuir role&quot;,
                &quot;description&quot;: &quot;Atribui uma role a um usu&aacute;rio&quot;,
                &quot;operationId&quot;: &quot;29bc0b6c9fc4d47c6299cc601ec1d115&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;role&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;user_id&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;user_id&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;,
                                        &quot;example&quot;: 1
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Role atribu&iacute;da com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;message&quot;: {
                                            &quot;type&quot;: &quot;string&quot;,
                                            &quot;example&quot;: &quot;Role assigned&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Role ou usu&aacute;rio n&atilde;o encontrado&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/admin/roles/{role}/revoke&quot;: {
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Admin&quot;
                ],
                &quot;summary&quot;: &quot;Revogar role&quot;,
                &quot;description&quot;: &quot;Revoga uma role de um usu&aacute;rio&quot;,
                &quot;operationId&quot;: &quot;e4b647e552f2014281d6a93914d722f5&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;role&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;user_id&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;user_id&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;,
                                        &quot;example&quot;: 1
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Role revogada com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;message&quot;: {
                                            &quot;type&quot;: &quot;string&quot;,
                                            &quot;example&quot;: &quot;Role revoked&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Role ou usu&aacute;rio n&atilde;o encontrado&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/auth/register&quot;: {
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Auth&quot;
                ],
                &quot;summary&quot;: &quot;Register account&quot;,
                &quot;operationId&quot;: &quot;48e49e48019e8b141753cb515a26100b&quot;,
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;name&quot;,
                                    &quot;email&quot;,
                                    &quot;password&quot;,
                                    &quot;password_confirmation&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;name&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;email&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;email&quot;
                                    },
                                    &quot;password&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;password&quot;
                                    },
                                    &quot;password_confirmation&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;password&quot;
                                    },
                                    &quot;device_name&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;201&quot;: {
                        &quot;description&quot;: &quot;Created&quot;
                    }
                }
            }
        },
        &quot;/api/v1/auth/login&quot;: {
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Auth&quot;
                ],
                &quot;summary&quot;: &quot;Login&quot;,
                &quot;operationId&quot;: &quot;5db09a35f965c6ca7529ed3c3cae8e21&quot;,
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;email&quot;,
                                    &quot;password&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;email&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;email&quot;
                                    },
                                    &quot;password&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;password&quot;
                                    },
                                    &quot;device_name&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;OK&quot;
                    }
                }
            }
        },
        &quot;/api/v1/auth/logout&quot;: {
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Auth&quot;
                ],
                &quot;summary&quot;: &quot;Logout&quot;,
                &quot;operationId&quot;: &quot;8f1a443d39c3bf383f851fbb51253c64&quot;,
                &quot;responses&quot;: {
                    &quot;204&quot;: {
                        &quot;description&quot;: &quot;No Content&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/auth/refresh&quot;: {
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Auth&quot;
                ],
                &quot;summary&quot;: &quot;Refresh token&quot;,
                &quot;operationId&quot;: &quot;05bd0482845348cafa4da9e4fb7d0ffb&quot;,
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;OK&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/auth/forgot&quot;: {
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Auth&quot;
                ],
                &quot;summary&quot;: &quot;Request password reset&quot;,
                &quot;operationId&quot;: &quot;4ba1f9eeb48a0ad43e95fde495eba7a3&quot;,
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;email&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;email&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;email&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;OK&quot;
                    }
                }
            }
        },
        &quot;/api/v1/auth/reset&quot;: {
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Auth&quot;
                ],
                &quot;summary&quot;: &quot;Reset password&quot;,
                &quot;operationId&quot;: &quot;59223f02bd5118bf2cfec01dd55506d6&quot;,
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;email&quot;,
                                    &quot;token&quot;,
                                    &quot;password&quot;,
                                    &quot;password_confirmation&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;email&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;email&quot;
                                    },
                                    &quot;token&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;password&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;password&quot;
                                    },
                                    &quot;password_confirmation&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;password&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;OK&quot;
                    }
                }
            }
        },
        &quot;/api/v1/companies&quot;: {
            &quot;get&quot;: {
                &quot;tags&quot;: [
                    &quot;Companies&quot;
                ],
                &quot;summary&quot;: &quot;Listar empresas do usu&aacute;rio&quot;,
                &quot;description&quot;: &quot;Retorna todas as empresas que o usu&aacute;rio autenticado pertence&quot;,
                &quot;operationId&quot;: &quot;44926c230232e0ab0f8674f0e7d1ef0c&quot;,
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Lista de empresas&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;array&quot;,
                                            &quot;items&quot;: {
                                                &quot;properties&quot;: {
                                                    &quot;id&quot;: {
                                                        &quot;type&quot;: &quot;integer&quot;,
                                                        &quot;example&quot;: 1
                                                    },
                                                    &quot;name&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;,
                                                        &quot;example&quot;: &quot;Construtora ABC&quot;
                                                    },
                                                    &quot;created_at&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;,
                                                        &quot;format&quot;: &quot;date-time&quot;
                                                    }
                                                },
                                                &quot;type&quot;: &quot;object&quot;
                                            }
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            },
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Companies&quot;
                ],
                &quot;summary&quot;: &quot;Criar empresa&quot;,
                &quot;description&quot;: &quot;Cria uma nova empresa e atribui o usu&aacute;rio como Admin&quot;,
                &quot;operationId&quot;: &quot;452e31b1f5c474b63614ab2a7138e467&quot;,
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;name&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;name&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;maxLength&quot;: 255,
                                        &quot;minLength&quot;: 2,
                                        &quot;example&quot;: &quot;Minha Construtora&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;201&quot;: {
                        &quot;description&quot;: &quot;Empresa criada com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;properties&quot;: {
                                                &quot;id&quot;: {
                                                    &quot;type&quot;: &quot;integer&quot;,
                                                    &quot;example&quot;: 1
                                                },
                                                &quot;name&quot;: {
                                                    &quot;type&quot;: &quot;string&quot;,
                                                    &quot;example&quot;: &quot;Minha Construtora&quot;
                                                },
                                                &quot;created_at&quot;: {
                                                    &quot;type&quot;: &quot;string&quot;,
                                                    &quot;format&quot;: &quot;date-time&quot;
                                                }
                                            },
                                            &quot;type&quot;: &quot;object&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/contractors&quot;: {
            &quot;get&quot;: {
                &quot;tags&quot;: [
                    &quot;Contractors&quot;
                ],
                &quot;summary&quot;: &quot;Listar empreiteiros&quot;,
                &quot;description&quot;: &quot;Retorna lista de empreiteiros da empresa&quot;,
                &quot;operationId&quot;: &quot;fc05a178905474e3e652ea3dd5114c46&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Lista de empreiteiros&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;array&quot;,
                                            &quot;items&quot;: {
                                                &quot;properties&quot;: {
                                                    &quot;id&quot;: {
                                                        &quot;type&quot;: &quot;integer&quot;,
                                                        &quot;example&quot;: 1
                                                    },
                                                    &quot;name&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;,
                                                        &quot;example&quot;: &quot;Construtora ABC&quot;
                                                    },
                                                    &quot;contact&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;,
                                                        &quot;example&quot;: &quot;(11) 98765-4321&quot;
                                                    },
                                                    &quot;specialties&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;,
                                                        &quot;example&quot;: &quot;Funda&ccedil;&atilde;o, Estrutura&quot;
                                                    }
                                                },
                                                &quot;type&quot;: &quot;object&quot;
                                            }
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            },
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Contractors&quot;
                ],
                &quot;summary&quot;: &quot;Criar empreiteiro&quot;,
                &quot;description&quot;: &quot;Cria um novo empreiteiro na empresa&quot;,
                &quot;operationId&quot;: &quot;5723ef720f05f0744659b666f05b91cc&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;name&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;name&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;Construtora ABC&quot;
                                    },
                                    &quot;contact&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;(11) 98765-4321&quot;
                                    },
                                    &quot;specialties&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;Funda&ccedil;&atilde;o, Estrutura&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;201&quot;: {
                        &quot;description&quot;: &quot;Empreiteiro criado com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;properties&quot;: {
                                                &quot;id&quot;: {
                                                    &quot;type&quot;: &quot;integer&quot;,
                                                    &quot;example&quot;: 1
                                                },
                                                &quot;name&quot;: {
                                                    &quot;type&quot;: &quot;string&quot;,
                                                    &quot;example&quot;: &quot;Construtora ABC&quot;
                                                },
                                                &quot;contact&quot;: {
                                                    &quot;type&quot;: &quot;string&quot;,
                                                    &quot;example&quot;: &quot;(11) 98765-4321&quot;
                                                },
                                                &quot;specialties&quot;: {
                                                    &quot;type&quot;: &quot;string&quot;,
                                                    &quot;example&quot;: &quot;Funda&ccedil;&atilde;o, Estrutura&quot;
                                                }
                                            },
                                            &quot;type&quot;: &quot;object&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/contractors/{contractor}&quot;: {
            &quot;put&quot;: {
                &quot;tags&quot;: [
                    &quot;Contractors&quot;
                ],
                &quot;summary&quot;: &quot;Atualizar empreiteiro&quot;,
                &quot;description&quot;: &quot;Atualiza informa&ccedil;&otilde;es de um empreiteiro existente&quot;,
                &quot;operationId&quot;: &quot;bb46251f14d7c2162f2c3c12a04c2c27&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;contractor&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;properties&quot;: {
                                    &quot;name&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;Construtora ABC&quot;
                                    },
                                    &quot;contact&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;(11) 98765-4321&quot;
                                    },
                                    &quot;specialties&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;Funda&ccedil;&atilde;o, Estrutura&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Empreiteiro atualizado com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;object&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Empreiteiro n&atilde;o encontrado&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            },
            &quot;delete&quot;: {
                &quot;tags&quot;: [
                    &quot;Contractors&quot;
                ],
                &quot;summary&quot;: &quot;Remover empreiteiro&quot;,
                &quot;description&quot;: &quot;Remove um empreiteiro (soft delete)&quot;,
                &quot;operationId&quot;: &quot;f09b98a2286ca36c4748421fb3c2d19f&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;contractor&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;204&quot;: {
                        &quot;description&quot;: &quot;Empreiteiro removido com sucesso&quot;
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Empreiteiro n&atilde;o encontrado&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/dashboard/stats&quot;: {
            &quot;get&quot;: {
                &quot;tags&quot;: [
                    &quot;Progress&quot;
                ],
                &quot;summary&quot;: &quot;Estat&iacute;sticas do dashboard&quot;,
                &quot;description&quot;: &quot;Retorna estat&iacute;sticas agregadas dos projetos&quot;,
                &quot;operationId&quot;: &quot;cafa93e57a14923cee17d59cd18bca1d&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;project_id&quot;,
                        &quot;in&quot;: &quot;query&quot;,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Estat&iacute;sticas&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;avg_progress&quot;: {
                                            &quot;type&quot;: &quot;integer&quot;
                                        },
                                        &quot;overdue_tasks_count&quot;: {
                                            &quot;type&quot;: &quot;integer&quot;
                                        },
                                        &quot;upcoming_deliveries_count&quot;: {
                                            &quot;type&quot;: &quot;integer&quot;
                                        },
                                        &quot;total_budget&quot;: {
                                            &quot;type&quot;: &quot;number&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/projects/{project}/documents&quot;: {
            &quot;get&quot;: {
                &quot;tags&quot;: [
                    &quot;Documents&quot;
                ],
                &quot;summary&quot;: &quot;Listar documentos do projeto&quot;,
                &quot;operationId&quot;: &quot;00487875969f23166bb69a221191437b&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;project&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Lista de documentos&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            },
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Documents&quot;
                ],
                &quot;summary&quot;: &quot;Upload de documento&quot;,
                &quot;description&quot;: &quot;Faz upload de um documento para o projeto&quot;,
                &quot;operationId&quot;: &quot;6218bfea75ccc43e87ca1fe6405fff41&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;project&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;multipart/form-data&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;file&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;file&quot;: {
                                        &quot;description&quot;: &quot;Arquivo a ser enviado (PDF, JPG, PNG, DOC, DOCX, XLS, XLSX - m&aacute;x. 10MB)&quot;,
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;binary&quot;
                                    },
                                    &quot;name&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;Planta Baixa&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;201&quot;: {
                        &quot;description&quot;: &quot;Documento enviado com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;object&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/documents/{document}&quot;: {
            &quot;get&quot;: {
                &quot;tags&quot;: [
                    &quot;Documents&quot;
                ],
                &quot;summary&quot;: &quot;Obter documento espec&iacute;fico&quot;,
                &quot;description&quot;: &quot;Retorna um documento espec&iacute;fico por ID. O usu&aacute;rio deve ter permiss&atilde;o para visualizar documentos do projeto.&quot;,
                &quot;operationId&quot;: &quot;0a86888d32d9a9165059255e7a8b70d3&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;document&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Documento encontrado&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;object&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Documento n&atilde;o encontrado&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            },
            &quot;delete&quot;: {
                &quot;tags&quot;: [
                    &quot;Documents&quot;
                ],
                &quot;summary&quot;: &quot;Remover documento&quot;,
                &quot;description&quot;: &quot;Remove um documento (soft delete) e exclui o arquivo do storage&quot;,
                &quot;operationId&quot;: &quot;8215d99153c61fc0a495a9a9e437be25&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;document&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;204&quot;: {
                        &quot;description&quot;: &quot;Documento removido com sucesso&quot;
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Documento n&atilde;o encontrado&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/documents/{document}/download&quot;: {
            &quot;get&quot;: {
                &quot;tags&quot;: [
                    &quot;Documents&quot;
                ],
                &quot;summary&quot;: &quot;Baixar arquivo do documento&quot;,
                &quot;description&quot;: &quot;Retorna o arquivo f&iacute;sico do documento para download. O usu&aacute;rio deve ter permiss&atilde;o para visualizar documentos do projeto.&quot;,
                &quot;operationId&quot;: &quot;a43a75f4c96bcb4a60a1189527190391&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;document&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Arquivo do documento&quot;,
                        &quot;content&quot;: {
                            &quot;application/octet-stream&quot;: {
                                &quot;schema&quot;: {
                                    &quot;type&quot;: &quot;string&quot;,
                                    &quot;format&quot;: &quot;binary&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Documento n&atilde;o encontrado&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/companies/{company}/invites&quot;: {
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Invites&quot;
                ],
                &quot;summary&quot;: &quot;Criar convite&quot;,
                &quot;description&quot;: &quot;Cria um convite para adicionar um usu&aacute;rio &agrave; empresa&quot;,
                &quot;operationId&quot;: &quot;e6a71cd1ba9abafa42652b490e7915ec&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;company&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;email&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;email&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;email&quot;,
                                        &quot;example&quot;: &quot;usuario@example.com&quot;
                                    },
                                    &quot;role_name&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;Admin Obra&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;201&quot;: {
                        &quot;description&quot;: &quot;Convite criado com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;token&quot;: {
                                            &quot;type&quot;: &quot;string&quot;,
                                            &quot;example&quot;: &quot;abc123def456...&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Empresa n&atilde;o encontrada&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/invites/{token}/accept&quot;: {
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Invites&quot;
                ],
                &quot;summary&quot;: &quot;Aceitar convite&quot;,
                &quot;description&quot;: &quot;Aceita um convite para ingressar em uma empresa&quot;,
                &quot;operationId&quot;: &quot;2c944cf259e4527a5b6e685d6068d8f9&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;token&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;description&quot;: &quot;Token do convite&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;string&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Convite aceito com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;message&quot;: {
                                            &quot;type&quot;: &quot;string&quot;,
                                            &quot;example&quot;: &quot;Invite accepted&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Convite n&atilde;o encontrado ou expirado&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/me/switch-company&quot;: {
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Me&quot;
                ],
                &quot;summary&quot;: &quot;Trocar empresa ativa&quot;,
                &quot;description&quot;: &quot;Altera a empresa ativa do usu&aacute;rio autenticado&quot;,
                &quot;operationId&quot;: &quot;258cb181da859637cadf5eaec5a2be3f&quot;,
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;company_id&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;company_id&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;,
                                        &quot;example&quot;: 1
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Empresa trocada com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;message&quot;: {
                                            &quot;type&quot;: &quot;string&quot;,
                                            &quot;example&quot;: &quot;Company switched&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Usu&aacute;rio n&atilde;o pertence &agrave; empresa&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/me/switch-project&quot;: {
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Me&quot;
                ],
                &quot;summary&quot;: &quot;Trocar projeto ativo&quot;,
                &quot;description&quot;: &quot;Altera o projeto ativo do usu&aacute;rio autenticado dentro da empresa atual&quot;,
                &quot;operationId&quot;: &quot;bc2be8c31b63851fabf4d4cbb9a67d31&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;project_id&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;project_id&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;,
                                        &quot;example&quot;: 1
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Projeto trocado com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;message&quot;: {
                                            &quot;type&quot;: &quot;string&quot;,
                                            &quot;example&quot;: &quot;Project switched&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Usu&aacute;rio n&atilde;o &eacute; membro do projeto&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Projeto n&atilde;o encontrado&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/projects/{project}/phases&quot;: {
            &quot;get&quot;: {
                &quot;tags&quot;: [
                    &quot;Phases&quot;
                ],
                &quot;summary&quot;: &quot;Listar fases do projeto&quot;,
                &quot;description&quot;: &quot;Retorna todas as fases de um projeto com progresso calculado&quot;,
                &quot;operationId&quot;: &quot;d7a760c08e07f540d5304de0d9fb0c8d&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;project&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;status&quot;,
                        &quot;in&quot;: &quot;query&quot;,
                        &quot;required&quot;: false,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;string&quot;,
                            &quot;enum&quot;: [
                                &quot;draft&quot;,
                                &quot;active&quot;,
                                &quot;archived&quot;
                            ]
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Lista de fases com progresso&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            },
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Phases&quot;
                ],
                &quot;summary&quot;: &quot;Criar fase&quot;,
                &quot;description&quot;: &quot;Cria uma nova fase no projeto&quot;,
                &quot;operationId&quot;: &quot;7401f41d9e1d8f64b65fa4e9f31b3bea&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;project&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;name&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;name&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;Funda&ccedil;&atilde;o&quot;
                                    },
                                    &quot;description&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;status&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;enum&quot;: [
                                            &quot;draft&quot;,
                                            &quot;active&quot;,
                                            &quot;archived&quot;
                                        ]
                                    },
                                    &quot;sequence&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;,
                                        &quot;example&quot;: 1
                                    },
                                    &quot;color&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;#FF5733&quot;
                                    },
                                    &quot;planned_start_at&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    },
                                    &quot;planned_end_at&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;201&quot;: {
                        &quot;description&quot;: &quot;Fase criada com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;object&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/phases/{phase}&quot;: {
            &quot;put&quot;: {
                &quot;tags&quot;: [
                    &quot;Phases&quot;
                ],
                &quot;summary&quot;: &quot;Atualizar fase&quot;,
                &quot;description&quot;: &quot;Atualiza informa&ccedil;&otilde;es de uma fase existente&quot;,
                &quot;operationId&quot;: &quot;531ca5d6cd5774be86bf805fe63450ab&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;phase&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;properties&quot;: {
                                    &quot;name&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;description&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;status&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;enum&quot;: [
                                            &quot;draft&quot;,
                                            &quot;active&quot;,
                                            &quot;archived&quot;
                                        ]
                                    },
                                    &quot;sequence&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;
                                    },
                                    &quot;color&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;#FF5733&quot;
                                    },
                                    &quot;planned_start_at&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    },
                                    &quot;planned_end_at&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    },
                                    &quot;actual_start_at&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    },
                                    &quot;actual_end_at&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Fase atualizada com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;object&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Fase n&atilde;o encontrada&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            },
            &quot;delete&quot;: {
                &quot;tags&quot;: [
                    &quot;Phases&quot;
                ],
                &quot;summary&quot;: &quot;Remover fase&quot;,
                &quot;description&quot;: &quot;Remove uma fase (soft delete). N&atilde;o &eacute; poss&iacute;vel remover fases que cont&ecirc;m tarefas&quot;,
                &quot;operationId&quot;: &quot;c02a809cf6ed0799f7e6b66862edd72e&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;phase&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;204&quot;: {
                        &quot;description&quot;: &quot;Fase removida com sucesso&quot;
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Fase n&atilde;o encontrada&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;N&atilde;o &eacute; poss&iacute;vel deletar uma fase que cont&eacute;m tarefas&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;message&quot;: {
                                            &quot;type&quot;: &quot;string&quot;,
                                            &quot;example&quot;: &quot;N&atilde;o &eacute; poss&iacute;vel deletar uma fase que cont&eacute;m tarefas.&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/projects&quot;: {
            &quot;get&quot;: {
                &quot;tags&quot;: [
                    &quot;Projects&quot;
                ],
                &quot;summary&quot;: &quot;Listar projetos&quot;,
                &quot;description&quot;: &quot;Retorna os projetos da empresa que o usu&aacute;rio tem acesso&quot;,
                &quot;operationId&quot;: &quot;4581dcc89729ced0a5c29d8132aaaee0&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;X-Project-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;description&quot;: &quot;Filtrar por projeto espec&iacute;fico&quot;,
                        &quot;required&quot;: false,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Lista de projetos&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;array&quot;,
                                            &quot;items&quot;: {
                                                &quot;properties&quot;: {
                                                    &quot;id&quot;: {
                                                        &quot;type&quot;: &quot;integer&quot;
                                                    },
                                                    &quot;name&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;
                                                    },
                                                    &quot;description&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;
                                                    },
                                                    &quot;status&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;
                                                    },
                                                    &quot;address&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;
                                                    },
                                                    &quot;start_date&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;,
                                                        &quot;format&quot;: &quot;date&quot;
                                                    },
                                                    &quot;end_date&quot;: {
                                                        &quot;type&quot;: &quot;string&quot;,
                                                        &quot;format&quot;: &quot;date&quot;
                                                    },
                                                    &quot;planned_budget_amount&quot;: {
                                                        &quot;type&quot;: &quot;number&quot;
                                                    }
                                                },
                                                &quot;type&quot;: &quot;object&quot;
                                            }
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Usu&aacute;rio n&atilde;o pertence &agrave; empresa&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            },
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Projects&quot;
                ],
                &quot;summary&quot;: &quot;Criar projeto&quot;,
                &quot;description&quot;: &quot;Cria um novo projeto/obra na empresa e atribui o criador como Manager&quot;,
                &quot;operationId&quot;: &quot;7afee26aa9047c66217855bd05d3db0b&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;name&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;name&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;Edif&iacute;cio Residencial ABC&quot;
                                    },
                                    &quot;description&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;address&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;Rua das Flores, 123&quot;
                                    },
                                    &quot;start_date&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    },
                                    &quot;end_date&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    },
                                    &quot;planned_budget_amount&quot;: {
                                        &quot;type&quot;: &quot;number&quot;,
                                        &quot;example&quot;: 500000
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;201&quot;: {
                        &quot;description&quot;: &quot;Projeto criado com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;object&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Usu&aacute;rio n&atilde;o pertence &agrave; empresa&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/projects/{project}&quot;: {
            &quot;get&quot;: {
                &quot;tags&quot;: [
                    &quot;Projects&quot;
                ],
                &quot;summary&quot;: &quot;Exibir projeto&quot;,
                &quot;description&quot;: &quot;Retorna detalhes de um projeto espec&iacute;fico&quot;,
                &quot;operationId&quot;: &quot;e6c47e85b5ba86fd4315df00515c000b&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;project&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Detalhes do projeto&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;object&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Projeto n&atilde;o encontrado&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            },
            &quot;put&quot;: {
                &quot;tags&quot;: [
                    &quot;Projects&quot;
                ],
                &quot;summary&quot;: &quot;Atualizar projeto&quot;,
                &quot;description&quot;: &quot;Atualiza informa&ccedil;&otilde;es de um projeto existente&quot;,
                &quot;operationId&quot;: &quot;ff7410e1a9bd3620dfa89eff0d02c9c4&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;project&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;properties&quot;: {
                                    &quot;name&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;description&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;status&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;enum&quot;: [
                                            &quot;planning&quot;,
                                            &quot;in_progress&quot;,
                                            &quot;on_hold&quot;,
                                            &quot;completed&quot;,
                                            &quot;canceled&quot;
                                        ]
                                    },
                                    &quot;address&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;start_date&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    },
                                    &quot;end_date&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Projeto atualizado com sucesso&quot;
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Projeto n&atilde;o encontrado&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/projects/{project}/progress&quot;: {
            &quot;get&quot;: {
                &quot;tags&quot;: [
                    &quot;Progress&quot;
                ],
                &quot;summary&quot;: &quot;Progresso detalhado do projeto&quot;,
                &quot;description&quot;: &quot;Retorna o progresso do projeto calculado com base nas fases e tarefas&quot;,
                &quot;operationId&quot;: &quot;c36f21523d6a79974ea4d35c1ae2893a&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;project&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Progresso do projeto&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;project_id&quot;: {
                                            &quot;type&quot;: &quot;integer&quot;
                                        },
                                        &quot;project_progress_percent&quot;: {
                                            &quot;type&quot;: &quot;integer&quot;
                                        },
                                        &quot;phases&quot;: {
                                            &quot;type&quot;: &quot;array&quot;,
                                            &quot;items&quot;: {}
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/projects/{project}/tasks&quot;: {
            &quot;get&quot;: {
                &quot;tags&quot;: [
                    &quot;Tasks&quot;
                ],
                &quot;summary&quot;: &quot;Listar tarefas do projeto&quot;,
                &quot;description&quot;: &quot;Retorna todas as tarefas de um projeto com filtros opcionais&quot;,
                &quot;operationId&quot;: &quot;fdd782c4a5fa76d48f0775aff6683721&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;project&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;phase_id&quot;,
                        &quot;in&quot;: &quot;query&quot;,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;status&quot;,
                        &quot;in&quot;: &quot;query&quot;,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;string&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;assignee_id&quot;,
                        &quot;in&quot;: &quot;query&quot;,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Lista de tarefas&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            },
            &quot;post&quot;: {
                &quot;tags&quot;: [
                    &quot;Tasks&quot;
                ],
                &quot;summary&quot;: &quot;Criar tarefa&quot;,
                &quot;description&quot;: &quot;Cria uma nova tarefa no projeto&quot;,
                &quot;operationId&quot;: &quot;79be8596790aea0d4a76e69da9474240&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;project&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;phase_id&quot;,
                                    &quot;title&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;phase_id&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;,
                                        &quot;example&quot;: 1
                                    },
                                    &quot;title&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;example&quot;: &quot;Instalar estrutura met&aacute;lica&quot;
                                    },
                                    &quot;description&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;status&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;enum&quot;: [
                                            &quot;backlog&quot;,
                                            &quot;in_progress&quot;,
                                            &quot;in_review&quot;,
                                            &quot;done&quot;,
                                            &quot;canceled&quot;
                                        ]
                                    },
                                    &quot;priority&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;enum&quot;: [
                                            &quot;low&quot;,
                                            &quot;medium&quot;,
                                            &quot;high&quot;,
                                            &quot;urgent&quot;
                                        ]
                                    },
                                    &quot;order_in_phase&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;,
                                        &quot;example&quot;: 1
                                    },
                                    &quot;assignee_id&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;
                                    },
                                    &quot;contractor_id&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;
                                    },
                                    &quot;is_blocked&quot;: {
                                        &quot;type&quot;: &quot;boolean&quot;
                                    },
                                    &quot;blocked_reason&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;planned_start_at&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    },
                                    &quot;planned_end_at&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    },
                                    &quot;due_at&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;201&quot;: {
                        &quot;description&quot;: &quot;Tarefa criada com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;object&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/tasks/{task}&quot;: {
            &quot;put&quot;: {
                &quot;tags&quot;: [
                    &quot;Tasks&quot;
                ],
                &quot;summary&quot;: &quot;Atualizar tarefa&quot;,
                &quot;description&quot;: &quot;Atualiza informa&ccedil;&otilde;es de uma tarefa existente&quot;,
                &quot;operationId&quot;: &quot;7ca08fc4397f1b44d058d9a8571df4e3&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;task&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;properties&quot;: {
                                    &quot;phase_id&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;
                                    },
                                    &quot;title&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;description&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;status&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;enum&quot;: [
                                            &quot;backlog&quot;,
                                            &quot;in_progress&quot;,
                                            &quot;in_review&quot;,
                                            &quot;done&quot;,
                                            &quot;canceled&quot;
                                        ]
                                    },
                                    &quot;priority&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;enum&quot;: [
                                            &quot;low&quot;,
                                            &quot;medium&quot;,
                                            &quot;high&quot;,
                                            &quot;urgent&quot;
                                        ]
                                    },
                                    &quot;order_in_phase&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;
                                    },
                                    &quot;assignee_id&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;
                                    },
                                    &quot;contractor_id&quot;: {
                                        &quot;type&quot;: &quot;integer&quot;
                                    },
                                    &quot;is_blocked&quot;: {
                                        &quot;type&quot;: &quot;boolean&quot;
                                    },
                                    &quot;blocked_reason&quot;: {
                                        &quot;type&quot;: &quot;string&quot;
                                    },
                                    &quot;planned_start_at&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    },
                                    &quot;planned_end_at&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    },
                                    &quot;due_at&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;format&quot;: &quot;date&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Tarefa atualizada com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;object&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Tarefa n&atilde;o encontrada&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            },
            &quot;delete&quot;: {
                &quot;tags&quot;: [
                    &quot;Tasks&quot;
                ],
                &quot;summary&quot;: &quot;Remover tarefa&quot;,
                &quot;description&quot;: &quot;Remove uma tarefa (soft delete)&quot;,
                &quot;operationId&quot;: &quot;c02800103c68f71d9c6e1646ac17ee8d&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;task&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;responses&quot;: {
                    &quot;204&quot;: {
                        &quot;description&quot;: &quot;Tarefa removida com sucesso&quot;
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Tarefa n&atilde;o encontrada&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        },
        &quot;/api/v1/tasks/{task}/status&quot;: {
            &quot;patch&quot;: {
                &quot;tags&quot;: [
                    &quot;Tasks&quot;
                ],
                &quot;summary&quot;: &quot;Atualizar status da tarefa&quot;,
                &quot;description&quot;: &quot;Atualiza apenas o status da tarefa (atalho para drag-and-drop no Kanban)&quot;,
                &quot;operationId&quot;: &quot;fa7333c0544a0bb2a20cd36700109d68&quot;,
                &quot;parameters&quot;: [
                    {
                        &quot;name&quot;: &quot;X-Company-Id&quot;,
                        &quot;in&quot;: &quot;header&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    },
                    {
                        &quot;name&quot;: &quot;task&quot;,
                        &quot;in&quot;: &quot;path&quot;,
                        &quot;required&quot;: true,
                        &quot;schema&quot;: {
                            &quot;type&quot;: &quot;integer&quot;
                        }
                    }
                ],
                &quot;requestBody&quot;: {
                    &quot;required&quot;: true,
                    &quot;content&quot;: {
                        &quot;application/json&quot;: {
                            &quot;schema&quot;: {
                                &quot;required&quot;: [
                                    &quot;status&quot;
                                ],
                                &quot;properties&quot;: {
                                    &quot;status&quot;: {
                                        &quot;type&quot;: &quot;string&quot;,
                                        &quot;enum&quot;: [
                                            &quot;backlog&quot;,
                                            &quot;in_progress&quot;,
                                            &quot;in_review&quot;,
                                            &quot;done&quot;,
                                            &quot;canceled&quot;
                                        ],
                                        &quot;example&quot;: &quot;in_progress&quot;
                                    }
                                },
                                &quot;type&quot;: &quot;object&quot;
                            }
                        }
                    }
                },
                &quot;responses&quot;: {
                    &quot;200&quot;: {
                        &quot;description&quot;: &quot;Status atualizado com sucesso&quot;,
                        &quot;content&quot;: {
                            &quot;application/json&quot;: {
                                &quot;schema&quot;: {
                                    &quot;properties&quot;: {
                                        &quot;data&quot;: {
                                            &quot;type&quot;: &quot;object&quot;
                                        }
                                    },
                                    &quot;type&quot;: &quot;object&quot;
                                }
                            }
                        }
                    },
                    &quot;403&quot;: {
                        &quot;description&quot;: &quot;Sem permiss&atilde;o&quot;
                    },
                    &quot;404&quot;: {
                        &quot;description&quot;: &quot;Tarefa n&atilde;o encontrada&quot;
                    },
                    &quot;422&quot;: {
                        &quot;description&quot;: &quot;Erro de valida&ccedil;&atilde;o&quot;
                    }
                },
                &quot;security&quot;: [
                    {
                        &quot;sanctum&quot;: []
                    }
                ]
            }
        }
    },
    &quot;components&quot;: {
        &quot;securitySchemes&quot;: {
            &quot;sanctum&quot;: {
                &quot;type&quot;: &quot;http&quot;,
                &quot;bearerFormat&quot;: &quot;JWT&quot;,
                &quot;scheme&quot;: &quot;bearer&quot;
            }
        }
    },
    &quot;tags&quot;: [
        {
            &quot;name&quot;: &quot;Admin&quot;,
            &quot;description&quot;: &quot;Gerenciamento administrativo de roles e permiss&otilde;es&quot;
        },
        {
            &quot;name&quot;: &quot;Companies&quot;,
            &quot;description&quot;: &quot;Gerenciamento de empresas&quot;
        },
        {
            &quot;name&quot;: &quot;Contractors&quot;,
            &quot;description&quot;: &quot;Gerenciamento de empreiteiros&quot;
        },
        {
            &quot;name&quot;: &quot;Documents&quot;,
            &quot;description&quot;: &quot;Gerenciamento de documentos do projeto&quot;
        },
        {
            &quot;name&quot;: &quot;Invites&quot;,
            &quot;description&quot;: &quot;Gerenciamento de convites para empresas&quot;
        },
        {
            &quot;name&quot;: &quot;Me&quot;,
            &quot;description&quot;: &quot;Opera&ccedil;&otilde;es do usu&aacute;rio autenticado&quot;
        },
        {
            &quot;name&quot;: &quot;Phases&quot;,
            &quot;description&quot;: &quot;Gerenciamento de fases do projeto&quot;
        },
        {
            &quot;name&quot;: &quot;Projects&quot;,
            &quot;description&quot;: &quot;Gerenciamento de projetos/obras&quot;
        },
        {
            &quot;name&quot;: &quot;Progress&quot;,
            &quot;description&quot;: &quot;Progresso e estat&iacute;sticas&quot;
        },
        {
            &quot;name&quot;: &quot;Tasks&quot;,
            &quot;description&quot;: &quot;Gerenciamento de tarefas do projeto&quot;
        },
        {
            &quot;name&quot;: &quot;Auth&quot;,
            &quot;description&quot;: &quot;Auth&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-docs" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-docs"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-docs"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-docs" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-docs">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-docs" data-method="GET"
      data-path="api/docs"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-docs', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-docs"
                    onclick="tryItOut('GETapi-docs');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-docs"
                    onclick="cancelTryOut('GETapi-docs');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-docs"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/docs</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-docs"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-docs"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-docs-asset--asset-">Serves a specific documentation asset for the Swagger UI interface.</h2>

<p>
</p>



<span id="example-requests-GETapi-docs-asset--asset-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/docs/asset/consequatur" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/docs/asset/consequatur"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-docs-asset--asset-">
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;(consequatur) - this L5 Swagger asset is not allowed&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-docs-asset--asset-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-docs-asset--asset-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-docs-asset--asset-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-docs-asset--asset-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-docs-asset--asset-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-docs-asset--asset-" data-method="GET"
      data-path="api/docs/asset/{asset}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-docs-asset--asset-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-docs-asset--asset-"
                    onclick="tryItOut('GETapi-docs-asset--asset-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-docs-asset--asset-"
                    onclick="cancelTryOut('GETapi-docs-asset--asset-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-docs-asset--asset-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/docs/asset/{asset}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-docs-asset--asset-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-docs-asset--asset-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>asset</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="asset"                data-endpoint="GETapi-docs-asset--asset-"
               value="consequatur"
               data-component="url">
    <br>
<p>The asset. Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-oauth2-callback">Handles the OAuth2 callback and retrieves the required file for the redirect.</h2>

<p>
</p>



<span id="example-requests-GETapi-oauth2-callback">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/oauth2-callback" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/oauth2-callback"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-oauth2-callback">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">content-type: text/html; charset=utf-8
cache-control: no-cache, private
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">&lt;!doctype html&gt;
&lt;html lang=&quot;en-US&quot;&gt;
&lt;body&gt;
&lt;script src=&quot;oauth2-redirect.js&quot;&gt;&lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;
</code>
 </pre>
    </span>
<span id="execution-results-GETapi-oauth2-callback" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-oauth2-callback"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-oauth2-callback"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-oauth2-callback" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-oauth2-callback">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-oauth2-callback" data-method="GET"
      data-path="api/oauth2-callback"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-oauth2-callback', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-oauth2-callback"
                    onclick="tryItOut('GETapi-oauth2-callback');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-oauth2-callback"
                    onclick="cancelTryOut('GETapi-oauth2-callback');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-oauth2-callback"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/oauth2-callback</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-oauth2-callback"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-oauth2-callback"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-POSTapi-v1-auth-register">POST api/v1/auth/register</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-auth-register">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/register" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"vmqeopfuudtdsufvyvddq\",
    \"email\": \"kunde.eloisa@example.com\",
    \"password\": \"4[*UyPJ\\\"}6\",
    \"device_name\": \"hdtqtqxbajwbpilpmufin\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/register"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "vmqeopfuudtdsufvyvddq",
    "email": "kunde.eloisa@example.com",
    "password": "4[*UyPJ\"}6",
    "device_name": "hdtqtqxbajwbpilpmufin"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-register">
</span>
<span id="execution-results-POSTapi-v1-auth-register" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-register"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-register"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-register" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-register">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-register" data-method="POST"
      data-path="api/v1/auth/register"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-register', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-register"
                    onclick="tryItOut('POSTapi-v1-auth-register');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-register"
                    onclick="cancelTryOut('POSTapi-v1-auth-register');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-register"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/register</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-auth-register"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-v1-auth-register"
               value="kunde.eloisa@example.com"
               data-component="body">
    <br>
<p>Must be a valid email address. Must not be greater than 255 characters. Example: <code>kunde.eloisa@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-v1-auth-register"
               value="4[*UyPJ"}6"
               data-component="body">
    <br>
<p>Must be at least 8 characters. Example: <code>4[*UyPJ"}6</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_name"                data-endpoint="POSTapi-v1-auth-register"
               value="hdtqtqxbajwbpilpmufin"
               data-component="body">
    <br>
<p>Must not be greater than 100 characters. Example: <code>hdtqtqxbajwbpilpmufin</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-auth-login">POST api/v1/auth/login</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-auth-login">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/login" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"qkunze@example.com\",
    \"password\": \"Z5ij-e\\/dl4m{o,\",
    \"device_name\": \"dqamniihfqcoynlazghdt\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/login"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "qkunze@example.com",
    "password": "Z5ij-e\/dl4m{o,",
    "device_name": "dqamniihfqcoynlazghdt"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-login">
</span>
<span id="execution-results-POSTapi-v1-auth-login" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-login"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-login"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-login" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-login">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-login" data-method="POST"
      data-path="api/v1/auth/login"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-login', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-login"
                    onclick="tryItOut('POSTapi-v1-auth-login');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-login"
                    onclick="cancelTryOut('POSTapi-v1-auth-login');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-login"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/login</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-v1-auth-login"
               value="qkunze@example.com"
               data-component="body">
    <br>
<p>Must be a valid email address. Example: <code>qkunze@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-v1-auth-login"
               value="Z5ij-e/dl4m{o,"
               data-component="body">
    <br>
<p>Must be at least 6 characters. Example: <code>Z5ij-e/dl4m{o,</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_name"                data-endpoint="POSTapi-v1-auth-login"
               value="dqamniihfqcoynlazghdt"
               data-component="body">
    <br>
<p>Must not be greater than 100 characters. Example: <code>dqamniihfqcoynlazghdt</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-auth-forgot">POST api/v1/auth/forgot</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-auth-forgot">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/forgot" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"qkunze@example.com\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/forgot"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "qkunze@example.com"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-forgot">
</span>
<span id="execution-results-POSTapi-v1-auth-forgot" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-forgot"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-forgot"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-forgot" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-forgot">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-forgot" data-method="POST"
      data-path="api/v1/auth/forgot"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-forgot', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-forgot"
                    onclick="tryItOut('POSTapi-v1-auth-forgot');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-forgot"
                    onclick="cancelTryOut('POSTapi-v1-auth-forgot');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-forgot"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/forgot</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-forgot"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-forgot"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-v1-auth-forgot"
               value="qkunze@example.com"
               data-component="body">
    <br>
<p>Must be a valid email address. Example: <code>qkunze@example.com</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-auth-reset">POST api/v1/auth/reset</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-auth-reset">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/reset" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"qkunze@example.com\",
    \"token\": \"consequatur\",
    \"password\": \"[2UZ5ij-e\\/dl4\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/reset"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "qkunze@example.com",
    "token": "consequatur",
    "password": "[2UZ5ij-e\/dl4"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-reset">
</span>
<span id="execution-results-POSTapi-v1-auth-reset" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-reset"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-reset"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-reset" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-reset">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-reset" data-method="POST"
      data-path="api/v1/auth/reset"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-reset', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-reset"
                    onclick="tryItOut('POSTapi-v1-auth-reset');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-reset"
                    onclick="cancelTryOut('POSTapi-v1-auth-reset');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-reset"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/reset</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-reset"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-reset"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-v1-auth-reset"
               value="qkunze@example.com"
               data-component="body">
    <br>
<p>Must be a valid email address. Example: <code>qkunze@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="token"                data-endpoint="POSTapi-v1-auth-reset"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-v1-auth-reset"
               value="[2UZ5ij-e/dl4"
               data-component="body">
    <br>
<p>Must be at least 8 characters. Example: <code>[2UZ5ij-e/dl4</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-auth-logout">POST api/v1/auth/logout</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-auth-logout">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/logout" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/logout"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-logout">
</span>
<span id="execution-results-POSTapi-v1-auth-logout" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-logout"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-logout"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-logout" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-logout">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-logout" data-method="POST"
      data-path="api/v1/auth/logout"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-logout', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-logout"
                    onclick="tryItOut('POSTapi-v1-auth-logout');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-logout"
                    onclick="cancelTryOut('POSTapi-v1-auth-logout');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-logout"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/logout</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-POSTapi-v1-auth-refresh">POST api/v1/auth/refresh</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-auth-refresh">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/refresh" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/refresh"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-refresh">
</span>
<span id="execution-results-POSTapi-v1-auth-refresh" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-refresh"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-refresh"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-refresh" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-refresh">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-refresh" data-method="POST"
      data-path="api/v1/auth/refresh"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-refresh', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-refresh"
                    onclick="tryItOut('POSTapi-v1-auth-refresh');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-refresh"
                    onclick="cancelTryOut('POSTapi-v1-auth-refresh');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-refresh"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/refresh</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-refresh"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-refresh"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-companies">GET api/v1/companies</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-companies">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/companies" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/companies"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-companies">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-companies" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-companies"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-companies"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-companies" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-companies">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-companies" data-method="GET"
      data-path="api/v1/companies"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-companies', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-companies"
                    onclick="tryItOut('GETapi-v1-companies');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-companies"
                    onclick="cancelTryOut('GETapi-v1-companies');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-companies"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/companies</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-companies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-companies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-POSTapi-v1-companies">POST api/v1/companies</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-companies">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/companies" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"vmqeopfuudtdsufvyvddq\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/companies"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "vmqeopfuudtdsufvyvddq"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-companies">
</span>
<span id="execution-results-POSTapi-v1-companies" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-companies"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-companies"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-companies" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-companies">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-companies" data-method="POST"
      data-path="api/v1/companies"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-companies', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-companies"
                    onclick="tryItOut('POSTapi-v1-companies');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-companies"
                    onclick="cancelTryOut('POSTapi-v1-companies');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-companies"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/companies</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-companies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-companies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-companies"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must be at least 2 characters. Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-companies--company_id--invites">POST api/v1/companies/{company_id}/invites</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-companies--company_id--invites">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/companies/13/invites" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"qkunze@example.com\",
    \"role_name\": \"consequatur\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/companies/13/invites"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "qkunze@example.com",
    "role_name": "consequatur"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-companies--company_id--invites">
</span>
<span id="execution-results-POSTapi-v1-companies--company_id--invites" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-companies--company_id--invites"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-companies--company_id--invites"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-companies--company_id--invites" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-companies--company_id--invites">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-companies--company_id--invites" data-method="POST"
      data-path="api/v1/companies/{company_id}/invites"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-companies--company_id--invites', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-companies--company_id--invites"
                    onclick="tryItOut('POSTapi-v1-companies--company_id--invites');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-companies--company_id--invites"
                    onclick="cancelTryOut('POSTapi-v1-companies--company_id--invites');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-companies--company_id--invites"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/companies/{company_id}/invites</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-companies--company_id--invites"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-companies--company_id--invites"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>company_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="company_id"                data-endpoint="POSTapi-v1-companies--company_id--invites"
               value="13"
               data-component="url">
    <br>
<p>The ID of the company. Example: <code>13</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-v1-companies--company_id--invites"
               value="qkunze@example.com"
               data-component="body">
    <br>
<p>Must be a valid email address. Example: <code>qkunze@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>role_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="role_name"                data-endpoint="POSTapi-v1-companies--company_id--invites"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-invites--token--accept">POST api/v1/invites/{token}/accept</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-invites--token--accept">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/invites/consequatur/accept" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invites/consequatur/accept"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-invites--token--accept">
</span>
<span id="execution-results-POSTapi-v1-invites--token--accept" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-invites--token--accept"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-invites--token--accept"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-invites--token--accept" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-invites--token--accept">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-invites--token--accept" data-method="POST"
      data-path="api/v1/invites/{token}/accept"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-invites--token--accept', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-invites--token--accept"
                    onclick="tryItOut('POSTapi-v1-invites--token--accept');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-invites--token--accept"
                    onclick="cancelTryOut('POSTapi-v1-invites--token--accept');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-invites--token--accept"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/invites/{token}/accept</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-invites--token--accept"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-invites--token--accept"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="token"                data-endpoint="POSTapi-v1-invites--token--accept"
               value="consequatur"
               data-component="url">
    <br>
<p>Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-invites-project--token--accept">POST api/v1/invites/project/{token}/accept</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-invites-project--token--accept">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/invites/project/8/accept" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invites/project/8/accept"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-invites-project--token--accept">
</span>
<span id="execution-results-POSTapi-v1-invites-project--token--accept" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-invites-project--token--accept"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-invites-project--token--accept"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-invites-project--token--accept" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-invites-project--token--accept">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-invites-project--token--accept" data-method="POST"
      data-path="api/v1/invites/project/{token}/accept"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-invites-project--token--accept', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-invites-project--token--accept"
                    onclick="tryItOut('POSTapi-v1-invites-project--token--accept');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-invites-project--token--accept"
                    onclick="cancelTryOut('POSTapi-v1-invites-project--token--accept');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-invites-project--token--accept"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/invites/project/{token}/accept</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-invites-project--token--accept"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-invites-project--token--accept"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>token</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="token"                data-endpoint="POSTapi-v1-invites-project--token--accept"
               value="8"
               data-component="url">
    <br>
<p>Example: <code>8</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-me-switch-company">POST api/v1/me/switch-company</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-me-switch-company">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/me/switch-company" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"company_id\": 17
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/me/switch-company"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "company_id": 17
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-me-switch-company">
</span>
<span id="execution-results-POSTapi-v1-me-switch-company" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-me-switch-company"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-me-switch-company"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-me-switch-company" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-me-switch-company">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-me-switch-company" data-method="POST"
      data-path="api/v1/me/switch-company"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-me-switch-company', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-me-switch-company"
                    onclick="tryItOut('POSTapi-v1-me-switch-company');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-me-switch-company"
                    onclick="cancelTryOut('POSTapi-v1-me-switch-company');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-me-switch-company"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/me/switch-company</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-me-switch-company"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-me-switch-company"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>company_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="company_id"                data-endpoint="POSTapi-v1-me-switch-company"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the companies table. Example: <code>17</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-me-switch-project">POST api/v1/me/switch-project</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-me-switch-project">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/me/switch-project" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"project_id\": 17
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/me/switch-project"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "project_id": 17
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-me-switch-project">
</span>
<span id="execution-results-POSTapi-v1-me-switch-project" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-me-switch-project"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-me-switch-project"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-me-switch-project" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-me-switch-project">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-me-switch-project" data-method="POST"
      data-path="api/v1/me/switch-project"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-me-switch-project', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-me-switch-project"
                    onclick="tryItOut('POSTapi-v1-me-switch-project');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-me-switch-project"
                    onclick="cancelTryOut('POSTapi-v1-me-switch-project');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-me-switch-project"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/me/switch-project</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-me-switch-project"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-me-switch-project"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="POSTapi-v1-me-switch-project"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the projects table. Example: <code>17</code></p>
        </div>
        </form>

                    <h2 id="endpoints-PUTapi-v1-user-preferences">PUT api/v1/user/preferences</h2>

<p>
</p>



<span id="example-requests-PUTapi-v1-user-preferences">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/user/preferences" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email_notifications_enabled\": true
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/user/preferences"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email_notifications_enabled": true
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-user-preferences">
</span>
<span id="execution-results-PUTapi-v1-user-preferences" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-user-preferences"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-user-preferences"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-user-preferences" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-user-preferences">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-user-preferences" data-method="PUT"
      data-path="api/v1/user/preferences"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-user-preferences', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-user-preferences"
                    onclick="tryItOut('PUTapi-v1-user-preferences');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-user-preferences"
                    onclick="cancelTryOut('PUTapi-v1-user-preferences');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-user-preferences"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/user/preferences</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-user-preferences"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-user-preferences"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email_notifications_enabled</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="PUTapi-v1-user-preferences" style="display: none">
            <input type="radio" name="email_notifications_enabled"
                   value="true"
                   data-endpoint="PUTapi-v1-user-preferences"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PUTapi-v1-user-preferences" style="display: none">
            <input type="radio" name="email_notifications_enabled"
                   value="false"
                   data-endpoint="PUTapi-v1-user-preferences"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>true</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-user-expo-token">POST api/v1/user/expo-token</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-user-expo-token">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/user/expo-token" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"expo_push_token\": \"vmqeopfuudtdsufvyvddq\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/user/expo-token"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "expo_push_token": "vmqeopfuudtdsufvyvddq"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-user-expo-token">
</span>
<span id="execution-results-POSTapi-v1-user-expo-token" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-user-expo-token"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-user-expo-token"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-user-expo-token" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-user-expo-token">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-user-expo-token" data-method="POST"
      data-path="api/v1/user/expo-token"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-user-expo-token', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-user-expo-token"
                    onclick="tryItOut('POSTapi-v1-user-expo-token');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-user-expo-token"
                    onclick="cancelTryOut('POSTapi-v1-user-expo-token');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-user-expo-token"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/user/expo-token</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-user-expo-token"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-user-expo-token"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>expo_push_token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="expo_push_token"                data-endpoint="POSTapi-v1-user-expo-token"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-v1-notifications">GET api/v1/notifications</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-notifications">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/notifications" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/notifications"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-notifications">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-notifications" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-notifications"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-notifications"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-notifications" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-notifications">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-notifications" data-method="GET"
      data-path="api/v1/notifications"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-notifications', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-notifications"
                    onclick="tryItOut('GETapi-v1-notifications');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-notifications"
                    onclick="cancelTryOut('GETapi-v1-notifications');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-notifications"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/notifications</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-notifications"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-notifications"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-PATCHapi-v1-notifications--id--read">PATCH api/v1/notifications/{id}/read</h2>

<p>
</p>



<span id="example-requests-PATCHapi-v1-notifications--id--read">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PATCH \
    "http://localhost:8000/api/v1/notifications/consequatur/read" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/notifications/consequatur/read"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "PATCH",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-v1-notifications--id--read">
</span>
<span id="execution-results-PATCHapi-v1-notifications--id--read" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-v1-notifications--id--read"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-v1-notifications--id--read"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-v1-notifications--id--read" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-v1-notifications--id--read">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-v1-notifications--id--read" data-method="PATCH"
      data-path="api/v1/notifications/{id}/read"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-v1-notifications--id--read', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-v1-notifications--id--read"
                    onclick="tryItOut('PATCHapi-v1-notifications--id--read');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-v1-notifications--id--read"
                    onclick="cancelTryOut('PATCHapi-v1-notifications--id--read');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-v1-notifications--id--read"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/notifications/{id}/read</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-v1-notifications--id--read"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-v1-notifications--id--read"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="PATCHapi-v1-notifications--id--read"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the notification. Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-admin-roles">GET api/v1/admin/roles</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-admin-roles">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/admin/roles" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/admin/roles"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-admin-roles">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-admin-roles" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-admin-roles"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-admin-roles"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-admin-roles" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-admin-roles">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-admin-roles" data-method="GET"
      data-path="api/v1/admin/roles"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-admin-roles', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-admin-roles"
                    onclick="tryItOut('GETapi-v1-admin-roles');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-admin-roles"
                    onclick="cancelTryOut('GETapi-v1-admin-roles');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-admin-roles"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/admin/roles</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-admin-roles"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-admin-roles"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-admin-permissions">GET api/v1/admin/permissions</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-admin-permissions">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/admin/permissions" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/admin/permissions"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-admin-permissions">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-admin-permissions" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-admin-permissions"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-admin-permissions"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-admin-permissions" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-admin-permissions">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-admin-permissions" data-method="GET"
      data-path="api/v1/admin/permissions"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-admin-permissions', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-admin-permissions"
                    onclick="tryItOut('GETapi-v1-admin-permissions');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-admin-permissions"
                    onclick="cancelTryOut('GETapi-v1-admin-permissions');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-admin-permissions"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/admin/permissions</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-admin-permissions"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-admin-permissions"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-POSTapi-v1-admin-roles--role_id--assign">POST api/v1/admin/roles/{role_id}/assign</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-admin-roles--role_id--assign">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/admin/roles/1/assign" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"user_id\": 17
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/admin/roles/1/assign"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "user_id": 17
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-admin-roles--role_id--assign">
</span>
<span id="execution-results-POSTapi-v1-admin-roles--role_id--assign" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-admin-roles--role_id--assign"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-admin-roles--role_id--assign"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-admin-roles--role_id--assign" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-admin-roles--role_id--assign">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-admin-roles--role_id--assign" data-method="POST"
      data-path="api/v1/admin/roles/{role_id}/assign"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-admin-roles--role_id--assign', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-admin-roles--role_id--assign"
                    onclick="tryItOut('POSTapi-v1-admin-roles--role_id--assign');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-admin-roles--role_id--assign"
                    onclick="cancelTryOut('POSTapi-v1-admin-roles--role_id--assign');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-admin-roles--role_id--assign"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/admin/roles/{role_id}/assign</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-admin-roles--role_id--assign"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-admin-roles--role_id--assign"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>role_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="role_id"                data-endpoint="POSTapi-v1-admin-roles--role_id--assign"
               value="1"
               data-component="url">
    <br>
<p>The ID of the role. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="POSTapi-v1-admin-roles--role_id--assign"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the users table. Example: <code>17</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-admin-roles--role_id--revoke">POST api/v1/admin/roles/{role_id}/revoke</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-admin-roles--role_id--revoke">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/admin/roles/1/revoke" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"user_id\": 17
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/admin/roles/1/revoke"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "user_id": 17
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-admin-roles--role_id--revoke">
</span>
<span id="execution-results-POSTapi-v1-admin-roles--role_id--revoke" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-admin-roles--role_id--revoke"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-admin-roles--role_id--revoke"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-admin-roles--role_id--revoke" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-admin-roles--role_id--revoke">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-admin-roles--role_id--revoke" data-method="POST"
      data-path="api/v1/admin/roles/{role_id}/revoke"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-admin-roles--role_id--revoke', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-admin-roles--role_id--revoke"
                    onclick="tryItOut('POSTapi-v1-admin-roles--role_id--revoke');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-admin-roles--role_id--revoke"
                    onclick="cancelTryOut('POSTapi-v1-admin-roles--role_id--revoke');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-admin-roles--role_id--revoke"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/admin/roles/{role_id}/revoke</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-admin-roles--role_id--revoke"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-admin-roles--role_id--revoke"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>role_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="role_id"                data-endpoint="POSTapi-v1-admin-roles--role_id--revoke"
               value="1"
               data-component="url">
    <br>
<p>The ID of the role. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="POSTapi-v1-admin-roles--role_id--revoke"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the users table. Example: <code>17</code></p>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-v1-admin-audit-logs">GET api/v1/admin/audit-logs</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-admin-audit-logs">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/admin/audit-logs" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/admin/audit-logs"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-admin-audit-logs">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-admin-audit-logs" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-admin-audit-logs"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-admin-audit-logs"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-admin-audit-logs" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-admin-audit-logs">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-admin-audit-logs" data-method="GET"
      data-path="api/v1/admin/audit-logs"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-admin-audit-logs', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-admin-audit-logs"
                    onclick="tryItOut('GETapi-v1-admin-audit-logs');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-admin-audit-logs"
                    onclick="cancelTryOut('GETapi-v1-admin-audit-logs');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-admin-audit-logs"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/admin/audit-logs</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-admin-audit-logs"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-admin-audit-logs"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-projects">GET api/v1/projects</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-projects">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/projects" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-projects">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-projects" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-projects"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-projects"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-projects" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-projects">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-projects" data-method="GET"
      data-path="api/v1/projects"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-projects', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-projects"
                    onclick="tryItOut('GETapi-v1-projects');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-projects"
                    onclick="cancelTryOut('GETapi-v1-projects');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-projects"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/projects</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-projects"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-projects"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-POSTapi-v1-projects">POST api/v1/projects</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-projects">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/projects" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"vmqeopfuudtdsufvyvddq\",
    \"description\": \"Dolores molestias ipsam sit.\",
    \"status\": \"planning\",
    \"archived_at\": \"2026-01-01T11:12:26\",
    \"start_date\": \"2026-01-01T11:12:26\",
    \"end_date\": \"2107-01-30\",
    \"actual_start_date\": \"2026-01-01T11:12:26\",
    \"actual_end_date\": \"2107-01-30\",
    \"planned_budget_amount\": 45,
    \"address\": \"qeopfuudtdsufvyvddqam\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "vmqeopfuudtdsufvyvddq",
    "description": "Dolores molestias ipsam sit.",
    "status": "planning",
    "archived_at": "2026-01-01T11:12:26",
    "start_date": "2026-01-01T11:12:26",
    "end_date": "2107-01-30",
    "actual_start_date": "2026-01-01T11:12:26",
    "actual_end_date": "2107-01-30",
    "planned_budget_amount": 45,
    "address": "qeopfuudtdsufvyvddqam"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-projects">
</span>
<span id="execution-results-POSTapi-v1-projects" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-projects"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-projects"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-projects" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-projects">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-projects" data-method="POST"
      data-path="api/v1/projects"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-projects', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-projects"
                    onclick="tryItOut('POSTapi-v1-projects');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-projects"
                    onclick="cancelTryOut('POSTapi-v1-projects');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-projects"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/projects</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-projects"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-projects"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-projects"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must be at least 2 characters. Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="POSTapi-v1-projects"
               value="Dolores molestias ipsam sit."
               data-component="body">
    <br>
<p>Must not be greater than 2000 characters. Example: <code>Dolores molestias ipsam sit.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="POSTapi-v1-projects"
               value="planning"
               data-component="body">
    <br>
<p>Example: <code>planning</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>planning</code></li> <li><code>in_progress</code></li> <li><code>on_hold</code></li> <li><code>completed</code></li> <li><code>canceled</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>archived_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="archived_at"                data-endpoint="POSTapi-v1-projects"
               value="2026-01-01T11:12:26"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-01-01T11:12:26</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>start_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="start_date"                data-endpoint="POSTapi-v1-projects"
               value="2026-01-01T11:12:26"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-01-01T11:12:26</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>end_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="end_date"                data-endpoint="POSTapi-v1-projects"
               value="2107-01-30"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date after or equal to <code>start_date</code>. Example: <code>2107-01-30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>actual_start_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="actual_start_date"                data-endpoint="POSTapi-v1-projects"
               value="2026-01-01T11:12:26"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-01-01T11:12:26</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>actual_end_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="actual_end_date"                data-endpoint="POSTapi-v1-projects"
               value="2107-01-30"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date after or equal to <code>actual_start_date</code>. Example: <code>2107-01-30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>planned_budget_amount</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="planned_budget_amount"                data-endpoint="POSTapi-v1-projects"
               value="45"
               data-component="body">
    <br>
<p>Must be at least 0. Example: <code>45</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>address</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="address"                data-endpoint="POSTapi-v1-projects"
               value="qeopfuudtdsufvyvddqam"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>qeopfuudtdsufvyvddqam</code></p>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-v1-projects--project_id-">GET api/v1/projects/{project_id}</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-projects--project_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/projects/8" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-projects--project_id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-projects--project_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-projects--project_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-projects--project_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-projects--project_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-projects--project_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-projects--project_id-" data-method="GET"
      data-path="api/v1/projects/{project_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-projects--project_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-projects--project_id-"
                    onclick="tryItOut('GETapi-v1-projects--project_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-projects--project_id-"
                    onclick="cancelTryOut('GETapi-v1-projects--project_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-projects--project_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/projects/{project_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-projects--project_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-projects--project_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="GETapi-v1-projects--project_id-"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-contractors">GET api/v1/contractors</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-contractors">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/contractors" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/contractors"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-contractors">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-contractors" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-contractors"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-contractors"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-contractors" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-contractors">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-contractors" data-method="GET"
      data-path="api/v1/contractors"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-contractors', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-contractors"
                    onclick="tryItOut('GETapi-v1-contractors');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-contractors"
                    onclick="cancelTryOut('GETapi-v1-contractors');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-contractors"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/contractors</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-contractors"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-contractors"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-POSTapi-v1-contractors">POST api/v1/contractors</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-contractors">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/contractors" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"vmqeopfuudtdsufvyvddq\",
    \"contact\": \"amniihfqcoynlazghdtqt\",
    \"specialties\": \"consequatur\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/contractors"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "vmqeopfuudtdsufvyvddq",
    "contact": "amniihfqcoynlazghdtqt",
    "specialties": "consequatur"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-contractors">
</span>
<span id="execution-results-POSTapi-v1-contractors" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-contractors"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-contractors"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-contractors" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-contractors">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-contractors" data-method="POST"
      data-path="api/v1/contractors"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-contractors', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-contractors"
                    onclick="tryItOut('POSTapi-v1-contractors');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-contractors"
                    onclick="cancelTryOut('POSTapi-v1-contractors');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-contractors"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/contractors</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-contractors"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-contractors"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-contractors"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>contact</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="contact"                data-endpoint="POSTapi-v1-contractors"
               value="amniihfqcoynlazghdtqt"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>amniihfqcoynlazghdtqt</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>specialties</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="specialties"                data-endpoint="POSTapi-v1-contractors"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
        </form>

                    <h2 id="endpoints-PUTapi-v1-contractors--contractor_id-">PUT api/v1/contractors/{contractor_id}</h2>

<p>
</p>



<span id="example-requests-PUTapi-v1-contractors--contractor_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/contractors/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"vmqeopfuudtdsufvyvddq\",
    \"contact\": \"amniihfqcoynlazghdtqt\",
    \"specialties\": \"consequatur\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/contractors/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "vmqeopfuudtdsufvyvddq",
    "contact": "amniihfqcoynlazghdtqt",
    "specialties": "consequatur"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-contractors--contractor_id-">
</span>
<span id="execution-results-PUTapi-v1-contractors--contractor_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-contractors--contractor_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-contractors--contractor_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-contractors--contractor_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-contractors--contractor_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-contractors--contractor_id-" data-method="PUT"
      data-path="api/v1/contractors/{contractor_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-contractors--contractor_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-contractors--contractor_id-"
                    onclick="tryItOut('PUTapi-v1-contractors--contractor_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-contractors--contractor_id-"
                    onclick="cancelTryOut('PUTapi-v1-contractors--contractor_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-contractors--contractor_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/contractors/{contractor_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-contractors--contractor_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-contractors--contractor_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>contractor_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="contractor_id"                data-endpoint="PUTapi-v1-contractors--contractor_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the contractor. Example: <code>17</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-v1-contractors--contractor_id-"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>contact</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="contact"                data-endpoint="PUTapi-v1-contractors--contractor_id-"
               value="amniihfqcoynlazghdtqt"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>amniihfqcoynlazghdtqt</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>specialties</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="specialties"                data-endpoint="PUTapi-v1-contractors--contractor_id-"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
        </form>

                    <h2 id="endpoints-DELETEapi-v1-contractors--contractor_id-">DELETE api/v1/contractors/{contractor_id}</h2>

<p>
</p>



<span id="example-requests-DELETEapi-v1-contractors--contractor_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/contractors/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/contractors/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-contractors--contractor_id-">
</span>
<span id="execution-results-DELETEapi-v1-contractors--contractor_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-contractors--contractor_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-contractors--contractor_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-contractors--contractor_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-contractors--contractor_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-contractors--contractor_id-" data-method="DELETE"
      data-path="api/v1/contractors/{contractor_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-contractors--contractor_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-contractors--contractor_id-"
                    onclick="tryItOut('DELETEapi-v1-contractors--contractor_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-contractors--contractor_id-"
                    onclick="cancelTryOut('DELETEapi-v1-contractors--contractor_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-contractors--contractor_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/contractors/{contractor_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-contractors--contractor_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-contractors--contractor_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>contractor_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="contractor_id"                data-endpoint="DELETEapi-v1-contractors--contractor_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the contractor. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-suppliers">GET api/v1/suppliers</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-suppliers">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/suppliers" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/suppliers"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-suppliers">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-suppliers" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-suppliers"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-suppliers"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-suppliers" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-suppliers">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-suppliers" data-method="GET"
      data-path="api/v1/suppliers"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-suppliers', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-suppliers"
                    onclick="tryItOut('GETapi-v1-suppliers');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-suppliers"
                    onclick="cancelTryOut('GETapi-v1-suppliers');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-suppliers"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/suppliers</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-suppliers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-suppliers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-suppliers--supplier_id-">GET api/v1/suppliers/{supplier_id}</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-suppliers--supplier_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/suppliers/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/suppliers/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-suppliers--supplier_id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-suppliers--supplier_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-suppliers--supplier_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-suppliers--supplier_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-suppliers--supplier_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-suppliers--supplier_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-suppliers--supplier_id-" data-method="GET"
      data-path="api/v1/suppliers/{supplier_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-suppliers--supplier_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-suppliers--supplier_id-"
                    onclick="tryItOut('GETapi-v1-suppliers--supplier_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-suppliers--supplier_id-"
                    onclick="cancelTryOut('GETapi-v1-suppliers--supplier_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-suppliers--supplier_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/suppliers/{supplier_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-suppliers--supplier_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-suppliers--supplier_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>supplier_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="supplier_id"                data-endpoint="GETapi-v1-suppliers--supplier_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the supplier. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-suppliers">POST api/v1/suppliers</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-suppliers">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/suppliers" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"vmqeopfuudtdsufvyvddq\",
    \"cnpj\": \"71.717.728\\/9811-60$|^45332260595409\",
    \"contact\": \"ghdtqtqxbajwbpilpmufi\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/suppliers"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "vmqeopfuudtdsufvyvddq",
    "cnpj": "71.717.728\/9811-60$|^45332260595409",
    "contact": "ghdtqtqxbajwbpilpmufi"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-suppliers">
</span>
<span id="execution-results-POSTapi-v1-suppliers" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-suppliers"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-suppliers"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-suppliers" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-suppliers">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-suppliers" data-method="POST"
      data-path="api/v1/suppliers"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-suppliers', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-suppliers"
                    onclick="tryItOut('POSTapi-v1-suppliers');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-suppliers"
                    onclick="cancelTryOut('POSTapi-v1-suppliers');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-suppliers"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/suppliers</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-suppliers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-suppliers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-suppliers"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cnpj</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cnpj"                data-endpoint="POSTapi-v1-suppliers"
               value="71.717.728/9811-60$|^45332260595409"
               data-component="body">
    <br>
<p>Must match the regex /^\d{2}.\d{3}.\d{3}\/\d{4}-\d{2}$|^\d{14}$/. Example: <code>71.717.728/9811-60$|^45332260595409</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>contact</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="contact"                data-endpoint="POSTapi-v1-suppliers"
               value="ghdtqtqxbajwbpilpmufi"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>ghdtqtqxbajwbpilpmufi</code></p>
        </div>
        </form>

                    <h2 id="endpoints-PUTapi-v1-suppliers--supplier_id-">PUT api/v1/suppliers/{supplier_id}</h2>

<p>
</p>



<span id="example-requests-PUTapi-v1-suppliers--supplier_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/suppliers/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"vmqeopfuudtdsufvyvddq\",
    \"cnpj\": \"71.717.728\\/9811-60$|^45332260595409\",
    \"contact\": \"ghdtqtqxbajwbpilpmufi\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/suppliers/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "vmqeopfuudtdsufvyvddq",
    "cnpj": "71.717.728\/9811-60$|^45332260595409",
    "contact": "ghdtqtqxbajwbpilpmufi"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-suppliers--supplier_id-">
</span>
<span id="execution-results-PUTapi-v1-suppliers--supplier_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-suppliers--supplier_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-suppliers--supplier_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-suppliers--supplier_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-suppliers--supplier_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-suppliers--supplier_id-" data-method="PUT"
      data-path="api/v1/suppliers/{supplier_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-suppliers--supplier_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-suppliers--supplier_id-"
                    onclick="tryItOut('PUTapi-v1-suppliers--supplier_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-suppliers--supplier_id-"
                    onclick="cancelTryOut('PUTapi-v1-suppliers--supplier_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-suppliers--supplier_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/suppliers/{supplier_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-suppliers--supplier_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-suppliers--supplier_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>supplier_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="supplier_id"                data-endpoint="PUTapi-v1-suppliers--supplier_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the supplier. Example: <code>17</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-v1-suppliers--supplier_id-"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cnpj</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cnpj"                data-endpoint="PUTapi-v1-suppliers--supplier_id-"
               value="71.717.728/9811-60$|^45332260595409"
               data-component="body">
    <br>
<p>Must match the regex /^\d{2}.\d{3}.\d{3}\/\d{4}-\d{2}$|^\d{14}$/. Example: <code>71.717.728/9811-60$|^45332260595409</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>contact</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="contact"                data-endpoint="PUTapi-v1-suppliers--supplier_id-"
               value="ghdtqtqxbajwbpilpmufi"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>ghdtqtqxbajwbpilpmufi</code></p>
        </div>
        </form>

                    <h2 id="endpoints-DELETEapi-v1-suppliers--supplier_id-">DELETE api/v1/suppliers/{supplier_id}</h2>

<p>
</p>



<span id="example-requests-DELETEapi-v1-suppliers--supplier_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/suppliers/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/suppliers/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-suppliers--supplier_id-">
</span>
<span id="execution-results-DELETEapi-v1-suppliers--supplier_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-suppliers--supplier_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-suppliers--supplier_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-suppliers--supplier_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-suppliers--supplier_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-suppliers--supplier_id-" data-method="DELETE"
      data-path="api/v1/suppliers/{supplier_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-suppliers--supplier_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-suppliers--supplier_id-"
                    onclick="tryItOut('DELETEapi-v1-suppliers--supplier_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-suppliers--supplier_id-"
                    onclick="cancelTryOut('DELETEapi-v1-suppliers--supplier_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-suppliers--supplier_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/suppliers/{supplier_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-suppliers--supplier_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-suppliers--supplier_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>supplier_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="supplier_id"                data-endpoint="DELETEapi-v1-suppliers--supplier_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the supplier. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-projects--project_id--phases">GET api/v1/projects/{project_id}/phases</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-projects--project_id--phases">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/projects/8/phases" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/phases"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-projects--project_id--phases">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-projects--project_id--phases" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-projects--project_id--phases"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-projects--project_id--phases"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-projects--project_id--phases" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-projects--project_id--phases">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-projects--project_id--phases" data-method="GET"
      data-path="api/v1/projects/{project_id}/phases"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-projects--project_id--phases', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-projects--project_id--phases"
                    onclick="tryItOut('GETapi-v1-projects--project_id--phases');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-projects--project_id--phases"
                    onclick="cancelTryOut('GETapi-v1-projects--project_id--phases');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-projects--project_id--phases"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/projects/{project_id}/phases</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-projects--project_id--phases"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-projects--project_id--phases"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="GETapi-v1-projects--project_id--phases"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-projects--project_id--phases">POST api/v1/projects/{project_id}/phases</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-projects--project_id--phases">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/projects/8/phases" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"vmqeopfuudtdsufvyvddq\",
    \"description\": \"Dolores dolorum amet iste laborum eius est dolor.\",
    \"status\": \"active\",
    \"sequence\": 12,
    \"color\": \"#4CD4ab\",
    \"planned_start_at\": \"2026-01-01T11:12:26\",
    \"planned_end_at\": \"2107-01-30\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/phases"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "vmqeopfuudtdsufvyvddq",
    "description": "Dolores dolorum amet iste laborum eius est dolor.",
    "status": "active",
    "sequence": 12,
    "color": "#4CD4ab",
    "planned_start_at": "2026-01-01T11:12:26",
    "planned_end_at": "2107-01-30"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-projects--project_id--phases">
</span>
<span id="execution-results-POSTapi-v1-projects--project_id--phases" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-projects--project_id--phases"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-projects--project_id--phases"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-projects--project_id--phases" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-projects--project_id--phases">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-projects--project_id--phases" data-method="POST"
      data-path="api/v1/projects/{project_id}/phases"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-projects--project_id--phases', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-projects--project_id--phases"
                    onclick="tryItOut('POSTapi-v1-projects--project_id--phases');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-projects--project_id--phases"
                    onclick="cancelTryOut('POSTapi-v1-projects--project_id--phases');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-projects--project_id--phases"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/projects/{project_id}/phases</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-projects--project_id--phases"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-projects--project_id--phases"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="POSTapi-v1-projects--project_id--phases"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-projects--project_id--phases"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="POSTapi-v1-projects--project_id--phases"
               value="Dolores dolorum amet iste laborum eius est dolor."
               data-component="body">
    <br>
<p>Example: <code>Dolores dolorum amet iste laborum eius est dolor.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="POSTapi-v1-projects--project_id--phases"
               value="active"
               data-component="body">
    <br>
<p>Example: <code>active</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>draft</code></li> <li><code>active</code></li> <li><code>archived</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sequence</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="sequence"                data-endpoint="POSTapi-v1-projects--project_id--phases"
               value="12"
               data-component="body">
    <br>
<p>Must be at least 0. Example: <code>12</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>color</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="color"                data-endpoint="POSTapi-v1-projects--project_id--phases"
               value="#4CD4ab"
               data-component="body">
    <br>
<p>Must match the regex /^#[0-9A-Fa-f]{6}$/. Example: <code>#4CD4ab</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>planned_start_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="planned_start_at"                data-endpoint="POSTapi-v1-projects--project_id--phases"
               value="2026-01-01T11:12:26"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-01-01T11:12:26</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>planned_end_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="planned_end_at"                data-endpoint="POSTapi-v1-projects--project_id--phases"
               value="2107-01-30"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date after or equal to <code>planned_start_at</code>. Example: <code>2107-01-30</code></p>
        </div>
        </form>

                    <h2 id="endpoints-PUTapi-v1-phases--phase_id-">PUT api/v1/phases/{phase_id}</h2>

<p>
</p>



<span id="example-requests-PUTapi-v1-phases--phase_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/phases/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"vmqeopfuudtdsufvyvddq\",
    \"description\": \"Dolores dolorum amet iste laborum eius est dolor.\",
    \"status\": \"archived\",
    \"sequence\": 12,
    \"color\": \"#4CD4ab\",
    \"planned_start_at\": \"2026-01-01T11:12:26\",
    \"planned_end_at\": \"2107-01-30\",
    \"actual_start_at\": \"2026-01-01T11:12:26\",
    \"actual_end_at\": \"2026-01-01T11:12:26\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/phases/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "vmqeopfuudtdsufvyvddq",
    "description": "Dolores dolorum amet iste laborum eius est dolor.",
    "status": "archived",
    "sequence": 12,
    "color": "#4CD4ab",
    "planned_start_at": "2026-01-01T11:12:26",
    "planned_end_at": "2107-01-30",
    "actual_start_at": "2026-01-01T11:12:26",
    "actual_end_at": "2026-01-01T11:12:26"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-phases--phase_id-">
</span>
<span id="execution-results-PUTapi-v1-phases--phase_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-phases--phase_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-phases--phase_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-phases--phase_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-phases--phase_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-phases--phase_id-" data-method="PUT"
      data-path="api/v1/phases/{phase_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-phases--phase_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-phases--phase_id-"
                    onclick="tryItOut('PUTapi-v1-phases--phase_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-phases--phase_id-"
                    onclick="cancelTryOut('PUTapi-v1-phases--phase_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-phases--phase_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/phases/{phase_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-phases--phase_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-phases--phase_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>phase_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="phase_id"                data-endpoint="PUTapi-v1-phases--phase_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the phase. Example: <code>17</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-v1-phases--phase_id-"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="PUTapi-v1-phases--phase_id-"
               value="Dolores dolorum amet iste laborum eius est dolor."
               data-component="body">
    <br>
<p>Example: <code>Dolores dolorum amet iste laborum eius est dolor.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PUTapi-v1-phases--phase_id-"
               value="archived"
               data-component="body">
    <br>
<p>Example: <code>archived</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>draft</code></li> <li><code>active</code></li> <li><code>archived</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sequence</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="sequence"                data-endpoint="PUTapi-v1-phases--phase_id-"
               value="12"
               data-component="body">
    <br>
<p>Must be at least 0. Example: <code>12</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>color</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="color"                data-endpoint="PUTapi-v1-phases--phase_id-"
               value="#4CD4ab"
               data-component="body">
    <br>
<p>Must match the regex /^#[0-9A-Fa-f]{6}$/. Example: <code>#4CD4ab</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>planned_start_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="planned_start_at"                data-endpoint="PUTapi-v1-phases--phase_id-"
               value="2026-01-01T11:12:26"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-01-01T11:12:26</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>planned_end_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="planned_end_at"                data-endpoint="PUTapi-v1-phases--phase_id-"
               value="2107-01-30"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date after or equal to <code>planned_start_at</code>. Example: <code>2107-01-30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>actual_start_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="actual_start_at"                data-endpoint="PUTapi-v1-phases--phase_id-"
               value="2026-01-01T11:12:26"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-01-01T11:12:26</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>actual_end_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="actual_end_at"                data-endpoint="PUTapi-v1-phases--phase_id-"
               value="2026-01-01T11:12:26"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-01-01T11:12:26</code></p>
        </div>
        </form>

                    <h2 id="endpoints-DELETEapi-v1-phases--phase_id-">DELETE api/v1/phases/{phase_id}</h2>

<p>
</p>



<span id="example-requests-DELETEapi-v1-phases--phase_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/phases/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/phases/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-phases--phase_id-">
</span>
<span id="execution-results-DELETEapi-v1-phases--phase_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-phases--phase_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-phases--phase_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-phases--phase_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-phases--phase_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-phases--phase_id-" data-method="DELETE"
      data-path="api/v1/phases/{phase_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-phases--phase_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-phases--phase_id-"
                    onclick="tryItOut('DELETEapi-v1-phases--phase_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-phases--phase_id-"
                    onclick="cancelTryOut('DELETEapi-v1-phases--phase_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-phases--phase_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/phases/{phase_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-phases--phase_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-phases--phase_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>phase_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="phase_id"                data-endpoint="DELETEapi-v1-phases--phase_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the phase. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-projects--project_id--tasks">GET api/v1/projects/{project_id}/tasks</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-projects--project_id--tasks">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/projects/8/tasks" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/tasks"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-projects--project_id--tasks">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-projects--project_id--tasks" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-projects--project_id--tasks"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-projects--project_id--tasks"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-projects--project_id--tasks" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-projects--project_id--tasks">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-projects--project_id--tasks" data-method="GET"
      data-path="api/v1/projects/{project_id}/tasks"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-projects--project_id--tasks', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-projects--project_id--tasks"
                    onclick="tryItOut('GETapi-v1-projects--project_id--tasks');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-projects--project_id--tasks"
                    onclick="cancelTryOut('GETapi-v1-projects--project_id--tasks');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-projects--project_id--tasks"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/projects/{project_id}/tasks</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-projects--project_id--tasks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-projects--project_id--tasks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="GETapi-v1-projects--project_id--tasks"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-projects--project_id--tasks">POST api/v1/projects/{project_id}/tasks</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-projects--project_id--tasks">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/projects/8/tasks" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"phase_id\": \"consequatur\",
    \"title\": \"mqeopfuudtdsufvyvddqa\",
    \"description\": \"Dolores dolorum amet iste laborum eius est dolor.\",
    \"status\": \"in_progress\",
    \"priority\": \"urgent\",
    \"order_in_phase\": 12,
    \"is_blocked\": true,
    \"blocked_reason\": \"tdsufvyvddqamniihfqco\",
    \"planned_start_at\": \"2026-01-01T11:12:26\",
    \"planned_end_at\": \"2107-01-30\",
    \"due_at\": \"2026-01-01T11:12:26\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/tasks"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phase_id": "consequatur",
    "title": "mqeopfuudtdsufvyvddqa",
    "description": "Dolores dolorum amet iste laborum eius est dolor.",
    "status": "in_progress",
    "priority": "urgent",
    "order_in_phase": 12,
    "is_blocked": true,
    "blocked_reason": "tdsufvyvddqamniihfqco",
    "planned_start_at": "2026-01-01T11:12:26",
    "planned_end_at": "2107-01-30",
    "due_at": "2026-01-01T11:12:26"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-projects--project_id--tasks">
</span>
<span id="execution-results-POSTapi-v1-projects--project_id--tasks" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-projects--project_id--tasks"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-projects--project_id--tasks"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-projects--project_id--tasks" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-projects--project_id--tasks">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-projects--project_id--tasks" data-method="POST"
      data-path="api/v1/projects/{project_id}/tasks"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-projects--project_id--tasks', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-projects--project_id--tasks"
                    onclick="tryItOut('POSTapi-v1-projects--project_id--tasks');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-projects--project_id--tasks"
                    onclick="cancelTryOut('POSTapi-v1-projects--project_id--tasks');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-projects--project_id--tasks"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/projects/{project_id}/tasks</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phase_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phase_id"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value="consequatur"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the phases table. Example: <code>consequatur</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>title</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="title"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value="mqeopfuudtdsufvyvddqa"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>mqeopfuudtdsufvyvddqa</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value="Dolores dolorum amet iste laborum eius est dolor."
               data-component="body">
    <br>
<p>Example: <code>Dolores dolorum amet iste laborum eius est dolor.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value="in_progress"
               data-component="body">
    <br>
<p>Example: <code>in_progress</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>backlog</code></li> <li><code>in_progress</code></li> <li><code>in_review</code></li> <li><code>done</code></li> <li><code>canceled</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>priority</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="priority"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value="urgent"
               data-component="body">
    <br>
<p>Example: <code>urgent</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>low</code></li> <li><code>medium</code></li> <li><code>high</code></li> <li><code>urgent</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>order_in_phase</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="order_in_phase"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value="12"
               data-component="body">
    <br>
<p>Must be at least 0. Example: <code>12</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>assignee_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="assignee_id"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value=""
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the users table.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>contractor_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="contractor_id"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value=""
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the contractors table.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_blocked</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-v1-projects--project_id--tasks" style="display: none">
            <input type="radio" name="is_blocked"
                   value="true"
                   data-endpoint="POSTapi-v1-projects--project_id--tasks"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-v1-projects--project_id--tasks" style="display: none">
            <input type="radio" name="is_blocked"
                   value="false"
                   data-endpoint="POSTapi-v1-projects--project_id--tasks"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>true</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>blocked_reason</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="blocked_reason"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value="tdsufvyvddqamniihfqco"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>tdsufvyvddqamniihfqco</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>planned_start_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="planned_start_at"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value="2026-01-01T11:12:26"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-01-01T11:12:26</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>planned_end_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="planned_end_at"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value="2107-01-30"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date after or equal to <code>planned_start_at</code>. Example: <code>2107-01-30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>due_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="due_at"                data-endpoint="POSTapi-v1-projects--project_id--tasks"
               value="2026-01-01T11:12:26"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-01-01T11:12:26</code></p>
        </div>
        </form>

                    <h2 id="endpoints-PATCHapi-v1-projects--project_id--tasks-bulk">PATCH api/v1/projects/{project_id}/tasks/bulk</h2>

<p>
</p>



<span id="example-requests-PATCHapi-v1-projects--project_id--tasks-bulk">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PATCH \
    "http://localhost:8000/api/v1/projects/8/tasks/bulk" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"tasks\": [
        {
            \"id\": 17,
            \"position\": 45,
            \"status\": \"done\",
            \"phase_id\": 17,
            \"priority\": \"medium\",
            \"assignee_id\": 17,
            \"contractor_id\": 17,
            \"is_blocked\": true,
            \"blocked_reason\": \"mqeopfuudtdsufvyvddqa\"
        }
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/tasks/bulk"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "tasks": [
        {
            "id": 17,
            "position": 45,
            "status": "done",
            "phase_id": 17,
            "priority": "medium",
            "assignee_id": 17,
            "contractor_id": 17,
            "is_blocked": true,
            "blocked_reason": "mqeopfuudtdsufvyvddqa"
        }
    ]
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-v1-projects--project_id--tasks-bulk">
</span>
<span id="execution-results-PATCHapi-v1-projects--project_id--tasks-bulk" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-v1-projects--project_id--tasks-bulk"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-v1-projects--project_id--tasks-bulk"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-v1-projects--project_id--tasks-bulk" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-v1-projects--project_id--tasks-bulk">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-v1-projects--project_id--tasks-bulk" data-method="PATCH"
      data-path="api/v1/projects/{project_id}/tasks/bulk"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-v1-projects--project_id--tasks-bulk', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-v1-projects--project_id--tasks-bulk"
                    onclick="tryItOut('PATCHapi-v1-projects--project_id--tasks-bulk');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-v1-projects--project_id--tasks-bulk"
                    onclick="cancelTryOut('PATCHapi-v1-projects--project_id--tasks-bulk');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-v1-projects--project_id--tasks-bulk"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/projects/{project_id}/tasks/bulk</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>tasks</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Must have at least 1 items.</p>
            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="tasks.0.id"                data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the tasks table. Example: <code>17</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>position</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="tasks.0.position"                data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk"
               value="45"
               data-component="body">
    <br>
<p>Must be at least 0. Example: <code>45</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="tasks.0.status"                data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk"
               value="done"
               data-component="body">
    <br>
<p>Example: <code>done</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>backlog</code></li> <li><code>in_progress</code></li> <li><code>in_review</code></li> <li><code>done</code></li> <li><code>canceled</code></li></ul>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>phase_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="tasks.0.phase_id"                data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the phases table. Example: <code>17</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>priority</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="tasks.0.priority"                data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk"
               value="medium"
               data-component="body">
    <br>
<p>Example: <code>medium</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>low</code></li> <li><code>medium</code></li> <li><code>high</code></li> <li><code>urgent</code></li></ul>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>assignee_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="tasks.0.assignee_id"                data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the users table. Example: <code>17</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>contractor_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="tasks.0.contractor_id"                data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the contractors table. Example: <code>17</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>is_blocked</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk" style="display: none">
            <input type="radio" name="tasks.0.is_blocked"
                   value="true"
                   data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk" style="display: none">
            <input type="radio" name="tasks.0.is_blocked"
                   value="false"
                   data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>true</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>blocked_reason</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="tasks.0.blocked_reason"                data-endpoint="PATCHapi-v1-projects--project_id--tasks-bulk"
               value="mqeopfuudtdsufvyvddqa"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>mqeopfuudtdsufvyvddqa</code></p>
                    </div>
                                    </details>
        </div>
        </form>

                    <h2 id="endpoints-PUTapi-v1-tasks--task_id-">PUT api/v1/tasks/{task_id}</h2>

<p>
</p>



<span id="example-requests-PUTapi-v1-tasks--task_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/tasks/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"title\": \"vmqeopfuudtdsufvyvddq\",
    \"description\": \"Dolores dolorum amet iste laborum eius est dolor.\",
    \"status\": \"in_progress\",
    \"priority\": \"low\",
    \"order_in_phase\": 12,
    \"is_blocked\": true,
    \"blocked_reason\": \"tdsufvyvddqamniihfqco\",
    \"planned_start_at\": \"2026-01-01T11:12:26\",
    \"planned_end_at\": \"2107-01-30\",
    \"due_at\": \"2026-01-01T11:12:26\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/tasks/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "vmqeopfuudtdsufvyvddq",
    "description": "Dolores dolorum amet iste laborum eius est dolor.",
    "status": "in_progress",
    "priority": "low",
    "order_in_phase": 12,
    "is_blocked": true,
    "blocked_reason": "tdsufvyvddqamniihfqco",
    "planned_start_at": "2026-01-01T11:12:26",
    "planned_end_at": "2107-01-30",
    "due_at": "2026-01-01T11:12:26"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-tasks--task_id-">
</span>
<span id="execution-results-PUTapi-v1-tasks--task_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-tasks--task_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-tasks--task_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-tasks--task_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-tasks--task_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-tasks--task_id-" data-method="PUT"
      data-path="api/v1/tasks/{task_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-tasks--task_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-tasks--task_id-"
                    onclick="tryItOut('PUTapi-v1-tasks--task_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-tasks--task_id-"
                    onclick="cancelTryOut('PUTapi-v1-tasks--task_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-tasks--task_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/tasks/{task_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>task_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="task_id"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the task. Example: <code>17</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phase_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phase_id"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value=""
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the phases table.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>title</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="title"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value="Dolores dolorum amet iste laborum eius est dolor."
               data-component="body">
    <br>
<p>Example: <code>Dolores dolorum amet iste laborum eius est dolor.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value="in_progress"
               data-component="body">
    <br>
<p>Example: <code>in_progress</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>backlog</code></li> <li><code>in_progress</code></li> <li><code>in_review</code></li> <li><code>done</code></li> <li><code>canceled</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>priority</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="priority"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value="low"
               data-component="body">
    <br>
<p>Example: <code>low</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>low</code></li> <li><code>medium</code></li> <li><code>high</code></li> <li><code>urgent</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>order_in_phase</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="order_in_phase"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value="12"
               data-component="body">
    <br>
<p>Must be at least 0. Example: <code>12</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>assignee_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="assignee_id"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value=""
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the users table.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>contractor_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="contractor_id"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value=""
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the contractors table.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_blocked</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="PUTapi-v1-tasks--task_id-" style="display: none">
            <input type="radio" name="is_blocked"
                   value="true"
                   data-endpoint="PUTapi-v1-tasks--task_id-"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PUTapi-v1-tasks--task_id-" style="display: none">
            <input type="radio" name="is_blocked"
                   value="false"
                   data-endpoint="PUTapi-v1-tasks--task_id-"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>true</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>blocked_reason</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="blocked_reason"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value="tdsufvyvddqamniihfqco"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>tdsufvyvddqamniihfqco</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>planned_start_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="planned_start_at"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value="2026-01-01T11:12:26"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-01-01T11:12:26</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>planned_end_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="planned_end_at"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value="2107-01-30"
               data-component="body">
    <br>
<p>Must be a valid date. Must be a date after or equal to <code>planned_start_at</code>. Example: <code>2107-01-30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>due_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="due_at"                data-endpoint="PUTapi-v1-tasks--task_id-"
               value="2026-01-01T11:12:26"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-01-01T11:12:26</code></p>
        </div>
        </form>

                    <h2 id="endpoints-PATCHapi-v1-tasks--task_id--status">PATCH api/v1/tasks/{task_id}/status</h2>

<p>
</p>



<span id="example-requests-PATCHapi-v1-tasks--task_id--status">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PATCH \
    "http://localhost:8000/api/v1/tasks/17/status" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"status\": \"done\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/tasks/17/status"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": "done"
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-v1-tasks--task_id--status">
</span>
<span id="execution-results-PATCHapi-v1-tasks--task_id--status" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-v1-tasks--task_id--status"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-v1-tasks--task_id--status"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-v1-tasks--task_id--status" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-v1-tasks--task_id--status">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-v1-tasks--task_id--status" data-method="PATCH"
      data-path="api/v1/tasks/{task_id}/status"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-v1-tasks--task_id--status', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-v1-tasks--task_id--status"
                    onclick="tryItOut('PATCHapi-v1-tasks--task_id--status');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-v1-tasks--task_id--status"
                    onclick="cancelTryOut('PATCHapi-v1-tasks--task_id--status');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-v1-tasks--task_id--status"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/tasks/{task_id}/status</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-v1-tasks--task_id--status"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-v1-tasks--task_id--status"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>task_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="task_id"                data-endpoint="PATCHapi-v1-tasks--task_id--status"
               value="17"
               data-component="url">
    <br>
<p>The ID of the task. Example: <code>17</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PATCHapi-v1-tasks--task_id--status"
               value="done"
               data-component="body">
    <br>
<p>Example: <code>done</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>backlog</code></li> <li><code>in_progress</code></li> <li><code>in_review</code></li> <li><code>done</code></li> <li><code>canceled</code></li></ul>
        </div>
        </form>

                    <h2 id="endpoints-PATCHapi-v1-tasks--task_id--dependencies">PATCH api/v1/tasks/{task_id}/dependencies</h2>

<p>
</p>



<span id="example-requests-PATCHapi-v1-tasks--task_id--dependencies">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PATCH \
    "http://localhost:8000/api/v1/tasks/17/dependencies" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"add\": [
        17
    ],
    \"remove\": [
        17
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/tasks/17/dependencies"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "add": [
        17
    ],
    "remove": [
        17
    ]
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-v1-tasks--task_id--dependencies">
</span>
<span id="execution-results-PATCHapi-v1-tasks--task_id--dependencies" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-v1-tasks--task_id--dependencies"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-v1-tasks--task_id--dependencies"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-v1-tasks--task_id--dependencies" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-v1-tasks--task_id--dependencies">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-v1-tasks--task_id--dependencies" data-method="PATCH"
      data-path="api/v1/tasks/{task_id}/dependencies"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-v1-tasks--task_id--dependencies', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-v1-tasks--task_id--dependencies"
                    onclick="tryItOut('PATCHapi-v1-tasks--task_id--dependencies');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-v1-tasks--task_id--dependencies"
                    onclick="cancelTryOut('PATCHapi-v1-tasks--task_id--dependencies');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-v1-tasks--task_id--dependencies"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/tasks/{task_id}/dependencies</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-v1-tasks--task_id--dependencies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-v1-tasks--task_id--dependencies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>task_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="task_id"                data-endpoint="PATCHapi-v1-tasks--task_id--dependencies"
               value="17"
               data-component="url">
    <br>
<p>The ID of the task. Example: <code>17</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>add</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="add[0]"                data-endpoint="PATCHapi-v1-tasks--task_id--dependencies"
               data-component="body">
        <input type="number" style="display: none"
               name="add[1]"                data-endpoint="PATCHapi-v1-tasks--task_id--dependencies"
               data-component="body">
    <br>
<p>The value and <code>task</code> must be different. The <code>id</code> of an existing record in the tasks table.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>remove</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="remove[0]"                data-endpoint="PATCHapi-v1-tasks--task_id--dependencies"
               data-component="body">
        <input type="number" style="display: none"
               name="remove[1]"                data-endpoint="PATCHapi-v1-tasks--task_id--dependencies"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the task_dependencies table.</p>
        </div>
        </form>

                    <h2 id="endpoints-DELETEapi-v1-tasks--task_id-">DELETE api/v1/tasks/{task_id}</h2>

<p>
</p>



<span id="example-requests-DELETEapi-v1-tasks--task_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/tasks/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/tasks/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-tasks--task_id-">
</span>
<span id="execution-results-DELETEapi-v1-tasks--task_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-tasks--task_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-tasks--task_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-tasks--task_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-tasks--task_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-tasks--task_id-" data-method="DELETE"
      data-path="api/v1/tasks/{task_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-tasks--task_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-tasks--task_id-"
                    onclick="tryItOut('DELETEapi-v1-tasks--task_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-tasks--task_id-"
                    onclick="cancelTryOut('DELETEapi-v1-tasks--task_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-tasks--task_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/tasks/{task_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-tasks--task_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-tasks--task_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>task_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="task_id"                data-endpoint="DELETEapi-v1-tasks--task_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the task. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-task-dependencies">GET api/v1/task-dependencies</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-task-dependencies">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/task-dependencies" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"task_id\": 17
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/task-dependencies"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "task_id": 17
};

fetch(url, {
    method: "GET",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-task-dependencies">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-task-dependencies" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-task-dependencies"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-task-dependencies"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-task-dependencies" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-task-dependencies">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-task-dependencies" data-method="GET"
      data-path="api/v1/task-dependencies"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-task-dependencies', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-task-dependencies"
                    onclick="tryItOut('GETapi-v1-task-dependencies');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-task-dependencies"
                    onclick="cancelTryOut('GETapi-v1-task-dependencies');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-task-dependencies"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/task-dependencies</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-task-dependencies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-task-dependencies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>task_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="task_id"                data-endpoint="GETapi-v1-task-dependencies"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the tasks table. Example: <code>17</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-projects--project_id--task-dependencies">POST api/v1/projects/{project_id}/task-dependencies</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-projects--project_id--task-dependencies">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/projects/8/task-dependencies" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"task_id\": 17,
    \"depends_on_task_id\": 17
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/task-dependencies"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "task_id": 17,
    "depends_on_task_id": 17
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-projects--project_id--task-dependencies">
</span>
<span id="execution-results-POSTapi-v1-projects--project_id--task-dependencies" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-projects--project_id--task-dependencies"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-projects--project_id--task-dependencies"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-projects--project_id--task-dependencies" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-projects--project_id--task-dependencies">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-projects--project_id--task-dependencies" data-method="POST"
      data-path="api/v1/projects/{project_id}/task-dependencies"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-projects--project_id--task-dependencies', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-projects--project_id--task-dependencies"
                    onclick="tryItOut('POSTapi-v1-projects--project_id--task-dependencies');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-projects--project_id--task-dependencies"
                    onclick="cancelTryOut('POSTapi-v1-projects--project_id--task-dependencies');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-projects--project_id--task-dependencies"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/projects/{project_id}/task-dependencies</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-projects--project_id--task-dependencies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-projects--project_id--task-dependencies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="POSTapi-v1-projects--project_id--task-dependencies"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>task_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="task_id"                data-endpoint="POSTapi-v1-projects--project_id--task-dependencies"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the tasks table. Example: <code>17</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>depends_on_task_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="depends_on_task_id"                data-endpoint="POSTapi-v1-projects--project_id--task-dependencies"
               value="17"
               data-component="body">
    <br>
<p>The value and <code>task_id</code> must be different. The <code>id</code> of an existing record in the tasks table. Example: <code>17</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-projects--project_id--task-dependencies-bulk">POST api/v1/projects/{project_id}/task-dependencies/bulk</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-projects--project_id--task-dependencies-bulk">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/projects/8/task-dependencies/bulk" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"dependencies\": [
        {
            \"task_id\": 17,
            \"depends_on_task_id\": 17
        }
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/task-dependencies/bulk"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "dependencies": [
        {
            "task_id": 17,
            "depends_on_task_id": 17
        }
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-projects--project_id--task-dependencies-bulk">
</span>
<span id="execution-results-POSTapi-v1-projects--project_id--task-dependencies-bulk" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-projects--project_id--task-dependencies-bulk"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-projects--project_id--task-dependencies-bulk"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-projects--project_id--task-dependencies-bulk" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-projects--project_id--task-dependencies-bulk">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-projects--project_id--task-dependencies-bulk" data-method="POST"
      data-path="api/v1/projects/{project_id}/task-dependencies/bulk"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-projects--project_id--task-dependencies-bulk', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-projects--project_id--task-dependencies-bulk"
                    onclick="tryItOut('POSTapi-v1-projects--project_id--task-dependencies-bulk');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-projects--project_id--task-dependencies-bulk"
                    onclick="cancelTryOut('POSTapi-v1-projects--project_id--task-dependencies-bulk');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-projects--project_id--task-dependencies-bulk"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/projects/{project_id}/task-dependencies/bulk</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-projects--project_id--task-dependencies-bulk"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-projects--project_id--task-dependencies-bulk"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="POSTapi-v1-projects--project_id--task-dependencies-bulk"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>dependencies</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Must have at least 1 items.</p>
            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>task_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="dependencies.0.task_id"                data-endpoint="POSTapi-v1-projects--project_id--task-dependencies-bulk"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the tasks table. Example: <code>17</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>depends_on_task_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="dependencies.0.depends_on_task_id"                data-endpoint="POSTapi-v1-projects--project_id--task-dependencies-bulk"
               value="17"
               data-component="body">
    <br>
<p>The value and <code>dependencies.*.task_id</code> must be different. The <code>id</code> of an existing record in the tasks table. Example: <code>17</code></p>
                    </div>
                                    </details>
        </div>
        </form>

                    <h2 id="endpoints-PUTapi-v1-task-dependencies--taskDependency_id-">PUT api/v1/task-dependencies/{taskDependency_id}</h2>

<p>
</p>



<span id="example-requests-PUTapi-v1-task-dependencies--taskDependency_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/task-dependencies/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"task_id\": 17,
    \"depends_on_task_id\": 17
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/task-dependencies/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "task_id": 17,
    "depends_on_task_id": 17
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-task-dependencies--taskDependency_id-">
</span>
<span id="execution-results-PUTapi-v1-task-dependencies--taskDependency_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-task-dependencies--taskDependency_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-task-dependencies--taskDependency_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-task-dependencies--taskDependency_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-task-dependencies--taskDependency_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-task-dependencies--taskDependency_id-" data-method="PUT"
      data-path="api/v1/task-dependencies/{taskDependency_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-task-dependencies--taskDependency_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-task-dependencies--taskDependency_id-"
                    onclick="tryItOut('PUTapi-v1-task-dependencies--taskDependency_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-task-dependencies--taskDependency_id-"
                    onclick="cancelTryOut('PUTapi-v1-task-dependencies--taskDependency_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-task-dependencies--taskDependency_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/task-dependencies/{taskDependency_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-task-dependencies--taskDependency_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-task-dependencies--taskDependency_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>taskDependency_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="taskDependency_id"                data-endpoint="PUTapi-v1-task-dependencies--taskDependency_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the taskDependency. Example: <code>17</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>task_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="task_id"                data-endpoint="PUTapi-v1-task-dependencies--taskDependency_id-"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the tasks table. Example: <code>17</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>depends_on_task_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="depends_on_task_id"                data-endpoint="PUTapi-v1-task-dependencies--taskDependency_id-"
               value="17"
               data-component="body">
    <br>
<p>The value and <code>task_id</code> must be different. The <code>id</code> of an existing record in the tasks table. Example: <code>17</code></p>
        </div>
        </form>

                    <h2 id="endpoints-DELETEapi-v1-task-dependencies--taskDependency_id-">DELETE api/v1/task-dependencies/{taskDependency_id}</h2>

<p>
</p>



<span id="example-requests-DELETEapi-v1-task-dependencies--taskDependency_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/task-dependencies/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/task-dependencies/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-task-dependencies--taskDependency_id-">
</span>
<span id="execution-results-DELETEapi-v1-task-dependencies--taskDependency_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-task-dependencies--taskDependency_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-task-dependencies--taskDependency_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-task-dependencies--taskDependency_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-task-dependencies--taskDependency_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-task-dependencies--taskDependency_id-" data-method="DELETE"
      data-path="api/v1/task-dependencies/{taskDependency_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-task-dependencies--taskDependency_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-task-dependencies--taskDependency_id-"
                    onclick="tryItOut('DELETEapi-v1-task-dependencies--taskDependency_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-task-dependencies--taskDependency_id-"
                    onclick="cancelTryOut('DELETEapi-v1-task-dependencies--taskDependency_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-task-dependencies--taskDependency_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/task-dependencies/{taskDependency_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-task-dependencies--taskDependency_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-task-dependencies--taskDependency_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>taskDependency_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="taskDependency_id"                data-endpoint="DELETEapi-v1-task-dependencies--taskDependency_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the taskDependency. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-projects--project_id--documents">GET api/v1/projects/{project_id}/documents</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-projects--project_id--documents">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/projects/8/documents" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/documents"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-projects--project_id--documents">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-projects--project_id--documents" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-projects--project_id--documents"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-projects--project_id--documents"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-projects--project_id--documents" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-projects--project_id--documents">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-projects--project_id--documents" data-method="GET"
      data-path="api/v1/projects/{project_id}/documents"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-projects--project_id--documents', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-projects--project_id--documents"
                    onclick="tryItOut('GETapi-v1-projects--project_id--documents');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-projects--project_id--documents"
                    onclick="cancelTryOut('GETapi-v1-projects--project_id--documents');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-projects--project_id--documents"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/projects/{project_id}/documents</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-projects--project_id--documents"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-projects--project_id--documents"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="GETapi-v1-projects--project_id--documents"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-projects--project_id--documents">POST api/v1/projects/{project_id}/documents</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-projects--project_id--documents">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/projects/8/documents" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "name=vmqeopfuudtdsufvyvddq"\
    --form "file=@/private/var/folders/1p/t__mjx8s4bl01p68ls9f79300000gn/T/phpdAqNu7" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/documents"
);

const headers = {
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('name', 'vmqeopfuudtdsufvyvddq');
body.append('file', document.querySelector('input[name="file"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-projects--project_id--documents">
</span>
<span id="execution-results-POSTapi-v1-projects--project_id--documents" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-projects--project_id--documents"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-projects--project_id--documents"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-projects--project_id--documents" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-projects--project_id--documents">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-projects--project_id--documents" data-method="POST"
      data-path="api/v1/projects/{project_id}/documents"
      data-authed="0"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-projects--project_id--documents', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-projects--project_id--documents"
                    onclick="tryItOut('POSTapi-v1-projects--project_id--documents');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-projects--project_id--documents"
                    onclick="cancelTryOut('POSTapi-v1-projects--project_id--documents');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-projects--project_id--documents"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/projects/{project_id}/documents</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-projects--project_id--documents"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-projects--project_id--documents"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="POSTapi-v1-projects--project_id--documents"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>file</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="file"                data-endpoint="POSTapi-v1-projects--project_id--documents"
               value=""
               data-component="body">
    <br>
<p>Must be a file. Must not be greater than 10240 kilobytes. Example: <code>/private/var/folders/1p/t__mjx8s4bl01p68ls9f79300000gn/T/phpdAqNu7</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-projects--project_id--documents"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-v1-documents--document_id-">GET api/v1/documents/{document_id}</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-documents--document_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/documents/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/documents/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-documents--document_id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-documents--document_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-documents--document_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-documents--document_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-documents--document_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-documents--document_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-documents--document_id-" data-method="GET"
      data-path="api/v1/documents/{document_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-documents--document_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-documents--document_id-"
                    onclick="tryItOut('GETapi-v1-documents--document_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-documents--document_id-"
                    onclick="cancelTryOut('GETapi-v1-documents--document_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-documents--document_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/documents/{document_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-documents--document_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-documents--document_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>document_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="document_id"                data-endpoint="GETapi-v1-documents--document_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the document. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-documents--document_id--download">GET api/v1/documents/{document_id}/download</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-documents--document_id--download">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/documents/17/download" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/documents/17/download"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-documents--document_id--download">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-documents--document_id--download" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-documents--document_id--download"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-documents--document_id--download"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-documents--document_id--download" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-documents--document_id--download">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-documents--document_id--download" data-method="GET"
      data-path="api/v1/documents/{document_id}/download"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-documents--document_id--download', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-documents--document_id--download"
                    onclick="tryItOut('GETapi-v1-documents--document_id--download');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-documents--document_id--download"
                    onclick="cancelTryOut('GETapi-v1-documents--document_id--download');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-documents--document_id--download"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/documents/{document_id}/download</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-documents--document_id--download"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-documents--document_id--download"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>document_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="document_id"                data-endpoint="GETapi-v1-documents--document_id--download"
               value="17"
               data-component="url">
    <br>
<p>The ID of the document. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-DELETEapi-v1-documents--document_id-">DELETE api/v1/documents/{document_id}</h2>

<p>
</p>



<span id="example-requests-DELETEapi-v1-documents--document_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/documents/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/documents/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-documents--document_id-">
</span>
<span id="execution-results-DELETEapi-v1-documents--document_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-documents--document_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-documents--document_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-documents--document_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-documents--document_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-documents--document_id-" data-method="DELETE"
      data-path="api/v1/documents/{document_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-documents--document_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-documents--document_id-"
                    onclick="tryItOut('DELETEapi-v1-documents--document_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-documents--document_id-"
                    onclick="cancelTryOut('DELETEapi-v1-documents--document_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-documents--document_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/documents/{document_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-documents--document_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-documents--document_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>document_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="document_id"                data-endpoint="DELETEapi-v1-documents--document_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the document. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-projects--project_id--members">GET api/v1/projects/{project_id}/members</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-projects--project_id--members">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/projects/8/members" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/members"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-projects--project_id--members">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-projects--project_id--members" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-projects--project_id--members"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-projects--project_id--members"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-projects--project_id--members" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-projects--project_id--members">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-projects--project_id--members" data-method="GET"
      data-path="api/v1/projects/{project_id}/members"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-projects--project_id--members', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-projects--project_id--members"
                    onclick="tryItOut('GETapi-v1-projects--project_id--members');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-projects--project_id--members"
                    onclick="cancelTryOut('GETapi-v1-projects--project_id--members');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-projects--project_id--members"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/projects/{project_id}/members</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-projects--project_id--members"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-projects--project_id--members"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="GETapi-v1-projects--project_id--members"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-projects--project_id--members">POST api/v1/projects/{project_id}/members</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-projects--project_id--members">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/projects/8/members" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"user_id\": 17,
    \"role\": \"Manager\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/members"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "user_id": 17,
    "role": "Manager"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-projects--project_id--members">
</span>
<span id="execution-results-POSTapi-v1-projects--project_id--members" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-projects--project_id--members"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-projects--project_id--members"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-projects--project_id--members" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-projects--project_id--members">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-projects--project_id--members" data-method="POST"
      data-path="api/v1/projects/{project_id}/members"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-projects--project_id--members', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-projects--project_id--members"
                    onclick="tryItOut('POSTapi-v1-projects--project_id--members');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-projects--project_id--members"
                    onclick="cancelTryOut('POSTapi-v1-projects--project_id--members');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-projects--project_id--members"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/projects/{project_id}/members</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-projects--project_id--members"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-projects--project_id--members"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="POSTapi-v1-projects--project_id--members"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="POSTapi-v1-projects--project_id--members"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the users table. Example: <code>17</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>role</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="role"                data-endpoint="POSTapi-v1-projects--project_id--members"
               value="Manager"
               data-component="body">
    <br>
<p>Example: <code>Manager</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>Manager</code></li> <li><code>Engenheiro</code></li> <li><code>Fiscal</code></li> <li><code>Coordenador</code></li> <li><code>Viewer</code></li></ul>
        </div>
        </form>

                    <h2 id="endpoints-PATCHapi-v1-projects--project_id--members--userId-">PATCH api/v1/projects/{project_id}/members/{userId}</h2>

<p>
</p>



<span id="example-requests-PATCHapi-v1-projects--project_id--members--userId-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PATCH \
    "http://localhost:8000/api/v1/projects/8/members/consequatur" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"role\": \"Manager\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/members/consequatur"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "role": "Manager"
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-v1-projects--project_id--members--userId-">
</span>
<span id="execution-results-PATCHapi-v1-projects--project_id--members--userId-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-v1-projects--project_id--members--userId-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-v1-projects--project_id--members--userId-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-v1-projects--project_id--members--userId-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-v1-projects--project_id--members--userId-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-v1-projects--project_id--members--userId-" data-method="PATCH"
      data-path="api/v1/projects/{project_id}/members/{userId}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-v1-projects--project_id--members--userId-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-v1-projects--project_id--members--userId-"
                    onclick="tryItOut('PATCHapi-v1-projects--project_id--members--userId-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-v1-projects--project_id--members--userId-"
                    onclick="cancelTryOut('PATCHapi-v1-projects--project_id--members--userId-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-v1-projects--project_id--members--userId-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/projects/{project_id}/members/{userId}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-v1-projects--project_id--members--userId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-v1-projects--project_id--members--userId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="PATCHapi-v1-projects--project_id--members--userId-"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>userId</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="userId"                data-endpoint="PATCHapi-v1-projects--project_id--members--userId-"
               value="consequatur"
               data-component="url">
    <br>
<p>Example: <code>consequatur</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>role</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="role"                data-endpoint="PATCHapi-v1-projects--project_id--members--userId-"
               value="Manager"
               data-component="body">
    <br>
<p>Example: <code>Manager</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>Manager</code></li> <li><code>Engenheiro</code></li> <li><code>Fiscal</code></li> <li><code>Coordenador</code></li> <li><code>Viewer</code></li></ul>
        </div>
        </form>

                    <h2 id="endpoints-DELETEapi-v1-projects--project_id--members--userId-">DELETE api/v1/projects/{project_id}/members/{userId}</h2>

<p>
</p>



<span id="example-requests-DELETEapi-v1-projects--project_id--members--userId-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/projects/8/members/consequatur" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/members/consequatur"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-projects--project_id--members--userId-">
</span>
<span id="execution-results-DELETEapi-v1-projects--project_id--members--userId-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-projects--project_id--members--userId-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-projects--project_id--members--userId-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-projects--project_id--members--userId-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-projects--project_id--members--userId-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-projects--project_id--members--userId-" data-method="DELETE"
      data-path="api/v1/projects/{project_id}/members/{userId}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-projects--project_id--members--userId-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-projects--project_id--members--userId-"
                    onclick="tryItOut('DELETEapi-v1-projects--project_id--members--userId-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-projects--project_id--members--userId-"
                    onclick="cancelTryOut('DELETEapi-v1-projects--project_id--members--userId-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-projects--project_id--members--userId-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/projects/{project_id}/members/{userId}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-projects--project_id--members--userId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-projects--project_id--members--userId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="DELETEapi-v1-projects--project_id--members--userId-"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>userId</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="userId"                data-endpoint="DELETEapi-v1-projects--project_id--members--userId-"
               value="consequatur"
               data-component="url">
    <br>
<p>Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-projects--project_id--progress">GET api/v1/projects/{project_id}/progress</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-projects--project_id--progress">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/projects/8/progress" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/progress"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-projects--project_id--progress">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-projects--project_id--progress" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-projects--project_id--progress"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-projects--project_id--progress"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-projects--project_id--progress" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-projects--project_id--progress">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-projects--project_id--progress" data-method="GET"
      data-path="api/v1/projects/{project_id}/progress"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-projects--project_id--progress', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-projects--project_id--progress"
                    onclick="tryItOut('GETapi-v1-projects--project_id--progress');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-projects--project_id--progress"
                    onclick="cancelTryOut('GETapi-v1-projects--project_id--progress');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-projects--project_id--progress"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/projects/{project_id}/progress</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-projects--project_id--progress"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-projects--project_id--progress"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="GETapi-v1-projects--project_id--progress"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-dashboard-stats">GET api/v1/dashboard/stats</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-dashboard-stats">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboard/stats" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboard/stats"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-dashboard-stats">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-dashboard-stats" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-dashboard-stats"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-dashboard-stats"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-dashboard-stats" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-dashboard-stats">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-dashboard-stats" data-method="GET"
      data-path="api/v1/dashboard/stats"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboard-stats', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-dashboard-stats"
                    onclick="tryItOut('GETapi-v1-dashboard-stats');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-dashboard-stats"
                    onclick="cancelTryOut('GETapi-v1-dashboard-stats');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-dashboard-stats"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/dashboard/stats</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-dashboard-stats"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-dashboard-stats"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-projects--project_id--budgets">GET api/v1/projects/{project_id}/budgets</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-projects--project_id--budgets">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/projects/8/budgets" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/budgets"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-projects--project_id--budgets">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-projects--project_id--budgets" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-projects--project_id--budgets"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-projects--project_id--budgets"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-projects--project_id--budgets" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-projects--project_id--budgets">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-projects--project_id--budgets" data-method="GET"
      data-path="api/v1/projects/{project_id}/budgets"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-projects--project_id--budgets', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-projects--project_id--budgets"
                    onclick="tryItOut('GETapi-v1-projects--project_id--budgets');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-projects--project_id--budgets"
                    onclick="cancelTryOut('GETapi-v1-projects--project_id--budgets');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-projects--project_id--budgets"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/projects/{project_id}/budgets</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-projects--project_id--budgets"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-projects--project_id--budgets"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="GETapi-v1-projects--project_id--budgets"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-projects--project_id--budgets">POST api/v1/projects/{project_id}/budgets</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-projects--project_id--budgets">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/projects/8/budgets" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"total_planned\": 21
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/budgets"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "total_planned": 21
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-projects--project_id--budgets">
</span>
<span id="execution-results-POSTapi-v1-projects--project_id--budgets" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-projects--project_id--budgets"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-projects--project_id--budgets"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-projects--project_id--budgets" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-projects--project_id--budgets">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-projects--project_id--budgets" data-method="POST"
      data-path="api/v1/projects/{project_id}/budgets"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-projects--project_id--budgets', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-projects--project_id--budgets"
                    onclick="tryItOut('POSTapi-v1-projects--project_id--budgets');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-projects--project_id--budgets"
                    onclick="cancelTryOut('POSTapi-v1-projects--project_id--budgets');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-projects--project_id--budgets"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/projects/{project_id}/budgets</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-projects--project_id--budgets"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-projects--project_id--budgets"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="POSTapi-v1-projects--project_id--budgets"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>total_planned</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="total_planned"                data-endpoint="POSTapi-v1-projects--project_id--budgets"
               value="21"
               data-component="body">
    <br>
<p>Must be at least 0. Must not be greater than 9999999999999.99. Example: <code>21</code></p>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-v1-projects--project_id--budget-summary">GET api/v1/projects/{project_id}/budget/summary</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-projects--project_id--budget-summary">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/projects/8/budget/summary" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/budget/summary"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-projects--project_id--budget-summary">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-projects--project_id--budget-summary" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-projects--project_id--budget-summary"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-projects--project_id--budget-summary"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-projects--project_id--budget-summary" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-projects--project_id--budget-summary">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-projects--project_id--budget-summary" data-method="GET"
      data-path="api/v1/projects/{project_id}/budget/summary"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-projects--project_id--budget-summary', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-projects--project_id--budget-summary"
                    onclick="tryItOut('GETapi-v1-projects--project_id--budget-summary');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-projects--project_id--budget-summary"
                    onclick="cancelTryOut('GETapi-v1-projects--project_id--budget-summary');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-projects--project_id--budget-summary"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/projects/{project_id}/budget/summary</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-projects--project_id--budget-summary"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-projects--project_id--budget-summary"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="GETapi-v1-projects--project_id--budget-summary"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-budgets--budget_id-">GET api/v1/budgets/{budget_id}</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-budgets--budget_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/budgets/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/budgets/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-budgets--budget_id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-budgets--budget_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-budgets--budget_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-budgets--budget_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-budgets--budget_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-budgets--budget_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-budgets--budget_id-" data-method="GET"
      data-path="api/v1/budgets/{budget_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-budgets--budget_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-budgets--budget_id-"
                    onclick="tryItOut('GETapi-v1-budgets--budget_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-budgets--budget_id-"
                    onclick="cancelTryOut('GETapi-v1-budgets--budget_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-budgets--budget_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/budgets/{budget_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-budgets--budget_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-budgets--budget_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>budget_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="budget_id"                data-endpoint="GETapi-v1-budgets--budget_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the budget. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-PUTapi-v1-budgets--budget_id-">PUT api/v1/budgets/{budget_id}</h2>

<p>
</p>



<span id="example-requests-PUTapi-v1-budgets--budget_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/budgets/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"total_planned\": 21
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/budgets/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "total_planned": 21
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-budgets--budget_id-">
</span>
<span id="execution-results-PUTapi-v1-budgets--budget_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-budgets--budget_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-budgets--budget_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-budgets--budget_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-budgets--budget_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-budgets--budget_id-" data-method="PUT"
      data-path="api/v1/budgets/{budget_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-budgets--budget_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-budgets--budget_id-"
                    onclick="tryItOut('PUTapi-v1-budgets--budget_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-budgets--budget_id-"
                    onclick="cancelTryOut('PUTapi-v1-budgets--budget_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-budgets--budget_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/budgets/{budget_id}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/budgets/{budget_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-budgets--budget_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-budgets--budget_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>budget_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="budget_id"                data-endpoint="PUTapi-v1-budgets--budget_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the budget. Example: <code>17</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>total_planned</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="total_planned"                data-endpoint="PUTapi-v1-budgets--budget_id-"
               value="21"
               data-component="body">
    <br>
<p>Must be at least 0. Must not be greater than 9999999999999.99. Example: <code>21</code></p>
        </div>
        </form>

                    <h2 id="endpoints-DELETEapi-v1-budgets--budget_id-">DELETE api/v1/budgets/{budget_id}</h2>

<p>
</p>



<span id="example-requests-DELETEapi-v1-budgets--budget_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/budgets/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/budgets/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-budgets--budget_id-">
</span>
<span id="execution-results-DELETEapi-v1-budgets--budget_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-budgets--budget_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-budgets--budget_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-budgets--budget_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-budgets--budget_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-budgets--budget_id-" data-method="DELETE"
      data-path="api/v1/budgets/{budget_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-budgets--budget_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-budgets--budget_id-"
                    onclick="tryItOut('DELETEapi-v1-budgets--budget_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-budgets--budget_id-"
                    onclick="cancelTryOut('DELETEapi-v1-budgets--budget_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-budgets--budget_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/budgets/{budget_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-budgets--budget_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-budgets--budget_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>budget_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="budget_id"                data-endpoint="DELETEapi-v1-budgets--budget_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the budget. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-projects--project_id--budgets--budget_id--cost-items">GET api/v1/projects/{project_id}/budgets/{budget_id}/cost-items</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-projects--project_id--budgets--budget_id--cost-items">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/projects/8/budgets/17/cost-items" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/budgets/17/cost-items"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-projects--project_id--budgets--budget_id--cost-items">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-projects--project_id--budgets--budget_id--cost-items" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-projects--project_id--budgets--budget_id--cost-items"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-projects--project_id--budgets--budget_id--cost-items"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-projects--project_id--budgets--budget_id--cost-items" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-projects--project_id--budgets--budget_id--cost-items">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-projects--project_id--budgets--budget_id--cost-items" data-method="GET"
      data-path="api/v1/projects/{project_id}/budgets/{budget_id}/cost-items"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-projects--project_id--budgets--budget_id--cost-items', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-projects--project_id--budgets--budget_id--cost-items"
                    onclick="tryItOut('GETapi-v1-projects--project_id--budgets--budget_id--cost-items');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-projects--project_id--budgets--budget_id--cost-items"
                    onclick="cancelTryOut('GETapi-v1-projects--project_id--budgets--budget_id--cost-items');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-projects--project_id--budgets--budget_id--cost-items"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/projects/{project_id}/budgets/{budget_id}/cost-items</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-projects--project_id--budgets--budget_id--cost-items"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-projects--project_id--budgets--budget_id--cost-items"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="GETapi-v1-projects--project_id--budgets--budget_id--cost-items"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>budget_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="budget_id"                data-endpoint="GETapi-v1-projects--project_id--budgets--budget_id--cost-items"
               value="17"
               data-component="url">
    <br>
<p>The ID of the budget. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items">POST api/v1/projects/{project_id}/budgets/{budget_id}/cost-items</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/projects/8/budgets/17/cost-items" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"vmqeopfuudtdsufvyvddq\",
    \"category\": \"amniihfqcoynlazghdtqt\",
    \"planned_amount\": 16,
    \"unit\": \"xbajwbpilpmufinllwloa\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/budgets/17/cost-items"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "vmqeopfuudtdsufvyvddq",
    "category": "amniihfqcoynlazghdtqt",
    "planned_amount": 16,
    "unit": "xbajwbpilpmufinllwloa"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items">
</span>
<span id="execution-results-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items" data-method="POST"
      data-path="api/v1/projects/{project_id}/budgets/{budget_id}/cost-items"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-projects--project_id--budgets--budget_id--cost-items', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items"
                    onclick="tryItOut('POSTapi-v1-projects--project_id--budgets--budget_id--cost-items');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items"
                    onclick="cancelTryOut('POSTapi-v1-projects--project_id--budgets--budget_id--cost-items');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-projects--project_id--budgets--budget_id--cost-items"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/projects/{project_id}/budgets/{budget_id}/cost-items</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-projects--project_id--budgets--budget_id--cost-items"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-projects--project_id--budgets--budget_id--cost-items"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="POSTapi-v1-projects--project_id--budgets--budget_id--cost-items"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>budget_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="budget_id"                data-endpoint="POSTapi-v1-projects--project_id--budgets--budget_id--cost-items"
               value="17"
               data-component="url">
    <br>
<p>The ID of the budget. Example: <code>17</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-projects--project_id--budgets--budget_id--cost-items"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>category</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="category"                data-endpoint="POSTapi-v1-projects--project_id--budgets--budget_id--cost-items"
               value="amniihfqcoynlazghdtqt"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>amniihfqcoynlazghdtqt</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>planned_amount</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="planned_amount"                data-endpoint="POSTapi-v1-projects--project_id--budgets--budget_id--cost-items"
               value="16"
               data-component="body">
    <br>
<p>Must be at least 0. Must not be greater than 9999999999999.99. Example: <code>16</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>unit</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="unit"                data-endpoint="POSTapi-v1-projects--project_id--budgets--budget_id--cost-items"
               value="xbajwbpilpmufinllwloa"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>xbajwbpilpmufinllwloa</code></p>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-v1-cost-items--costItem_id-">GET api/v1/cost-items/{costItem_id}</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-cost-items--costItem_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/cost-items/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/cost-items/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-cost-items--costItem_id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-cost-items--costItem_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-cost-items--costItem_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-cost-items--costItem_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-cost-items--costItem_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-cost-items--costItem_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-cost-items--costItem_id-" data-method="GET"
      data-path="api/v1/cost-items/{costItem_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-cost-items--costItem_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-cost-items--costItem_id-"
                    onclick="tryItOut('GETapi-v1-cost-items--costItem_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-cost-items--costItem_id-"
                    onclick="cancelTryOut('GETapi-v1-cost-items--costItem_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-cost-items--costItem_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/cost-items/{costItem_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-cost-items--costItem_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-cost-items--costItem_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>costItem_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="costItem_id"                data-endpoint="GETapi-v1-cost-items--costItem_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the costItem. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-PUTapi-v1-cost-items--costItem_id-">PUT api/v1/cost-items/{costItem_id}</h2>

<p>
</p>



<span id="example-requests-PUTapi-v1-cost-items--costItem_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/cost-items/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"vmqeopfuudtdsufvyvddq\",
    \"category\": \"amniihfqcoynlazghdtqt\",
    \"planned_amount\": 16,
    \"unit\": \"xbajwbpilpmufinllwloa\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/cost-items/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "vmqeopfuudtdsufvyvddq",
    "category": "amniihfqcoynlazghdtqt",
    "planned_amount": 16,
    "unit": "xbajwbpilpmufinllwloa"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-cost-items--costItem_id-">
</span>
<span id="execution-results-PUTapi-v1-cost-items--costItem_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-cost-items--costItem_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-cost-items--costItem_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-cost-items--costItem_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-cost-items--costItem_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-cost-items--costItem_id-" data-method="PUT"
      data-path="api/v1/cost-items/{costItem_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-cost-items--costItem_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-cost-items--costItem_id-"
                    onclick="tryItOut('PUTapi-v1-cost-items--costItem_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-cost-items--costItem_id-"
                    onclick="cancelTryOut('PUTapi-v1-cost-items--costItem_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-cost-items--costItem_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/cost-items/{costItem_id}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/cost-items/{costItem_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-cost-items--costItem_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-cost-items--costItem_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>costItem_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="costItem_id"                data-endpoint="PUTapi-v1-cost-items--costItem_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the costItem. Example: <code>17</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-v1-cost-items--costItem_id-"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>category</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="category"                data-endpoint="PUTapi-v1-cost-items--costItem_id-"
               value="amniihfqcoynlazghdtqt"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>amniihfqcoynlazghdtqt</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>planned_amount</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="planned_amount"                data-endpoint="PUTapi-v1-cost-items--costItem_id-"
               value="16"
               data-component="body">
    <br>
<p>Must be at least 0. Must not be greater than 9999999999999.99. Example: <code>16</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>unit</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="unit"                data-endpoint="PUTapi-v1-cost-items--costItem_id-"
               value="xbajwbpilpmufinllwloa"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>xbajwbpilpmufinllwloa</code></p>
        </div>
        </form>

                    <h2 id="endpoints-DELETEapi-v1-cost-items--costItem_id-">DELETE api/v1/cost-items/{costItem_id}</h2>

<p>
</p>



<span id="example-requests-DELETEapi-v1-cost-items--costItem_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/cost-items/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/cost-items/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-cost-items--costItem_id-">
</span>
<span id="execution-results-DELETEapi-v1-cost-items--costItem_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-cost-items--costItem_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-cost-items--costItem_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-cost-items--costItem_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-cost-items--costItem_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-cost-items--costItem_id-" data-method="DELETE"
      data-path="api/v1/cost-items/{costItem_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-cost-items--costItem_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-cost-items--costItem_id-"
                    onclick="tryItOut('DELETEapi-v1-cost-items--costItem_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-cost-items--costItem_id-"
                    onclick="cancelTryOut('DELETEapi-v1-cost-items--costItem_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-cost-items--costItem_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/cost-items/{costItem_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-cost-items--costItem_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-cost-items--costItem_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>costItem_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="costItem_id"                data-endpoint="DELETEapi-v1-cost-items--costItem_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the costItem. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-projects--project_id--expenses">GET api/v1/projects/{project_id}/expenses</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-projects--project_id--expenses">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/projects/8/expenses" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/expenses"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-projects--project_id--expenses">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-projects--project_id--expenses" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-projects--project_id--expenses"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-projects--project_id--expenses"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-projects--project_id--expenses" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-projects--project_id--expenses">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-projects--project_id--expenses" data-method="GET"
      data-path="api/v1/projects/{project_id}/expenses"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-projects--project_id--expenses', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-projects--project_id--expenses"
                    onclick="tryItOut('GETapi-v1-projects--project_id--expenses');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-projects--project_id--expenses"
                    onclick="cancelTryOut('GETapi-v1-projects--project_id--expenses');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-projects--project_id--expenses"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/projects/{project_id}/expenses</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-projects--project_id--expenses"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-projects--project_id--expenses"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="GETapi-v1-projects--project_id--expenses"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-projects--project_id--expenses">POST api/v1/projects/{project_id}/expenses</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-projects--project_id--expenses">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/projects/8/expenses" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "cost_item_id=17"\
    --form "amount=13"\
    --form "date=2026-01-01T11:12:26"\
    --form "description=Amet iste laborum eius est dolor dolores."\
    --form "receipt_path=tdsufvyvddqamniihfqco"\
    --form "status=consequatur"\
    --form "receipt=@/private/var/folders/1p/t__mjx8s4bl01p68ls9f79300000gn/T/phpAuJTtj" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/expenses"
);

const headers = {
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('cost_item_id', '17');
body.append('amount', '13');
body.append('date', '2026-01-01T11:12:26');
body.append('description', 'Amet iste laborum eius est dolor dolores.');
body.append('receipt_path', 'tdsufvyvddqamniihfqco');
body.append('status', 'consequatur');
body.append('receipt', document.querySelector('input[name="receipt"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-projects--project_id--expenses">
</span>
<span id="execution-results-POSTapi-v1-projects--project_id--expenses" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-projects--project_id--expenses"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-projects--project_id--expenses"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-projects--project_id--expenses" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-projects--project_id--expenses">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-projects--project_id--expenses" data-method="POST"
      data-path="api/v1/projects/{project_id}/expenses"
      data-authed="0"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-projects--project_id--expenses', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-projects--project_id--expenses"
                    onclick="tryItOut('POSTapi-v1-projects--project_id--expenses');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-projects--project_id--expenses"
                    onclick="cancelTryOut('POSTapi-v1-projects--project_id--expenses');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-projects--project_id--expenses"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/projects/{project_id}/expenses</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-projects--project_id--expenses"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-projects--project_id--expenses"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="POSTapi-v1-projects--project_id--expenses"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cost_item_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="cost_item_id"                data-endpoint="POSTapi-v1-projects--project_id--expenses"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the cost_items table. Example: <code>17</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>amount</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="amount"                data-endpoint="POSTapi-v1-projects--project_id--expenses"
               value="13"
               data-component="body">
    <br>
<p>Must be at least 0.01. Must not be greater than 9999999999999.99. Example: <code>13</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="date"                data-endpoint="POSTapi-v1-projects--project_id--expenses"
               value="2026-01-01T11:12:26"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-01-01T11:12:26</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="POSTapi-v1-projects--project_id--expenses"
               value="Amet iste laborum eius est dolor dolores."
               data-component="body">
    <br>
<p>Must not be greater than 1000 characters. Example: <code>Amet iste laborum eius est dolor dolores.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>receipt</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="receipt"                data-endpoint="POSTapi-v1-projects--project_id--expenses"
               value=""
               data-component="body">
    <br>
<p>Must be a file. Must not be greater than 10240 kilobytes. Example: <code>/private/var/folders/1p/t__mjx8s4bl01p68ls9f79300000gn/T/phpAuJTtj</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>receipt_path</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="receipt_path"                data-endpoint="POSTapi-v1-projects--project_id--expenses"
               value="tdsufvyvddqamniihfqco"
               data-component="body">
    <br>
<p>Must not be greater than 500 characters. Example: <code>tdsufvyvddqamniihfqco</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="POSTapi-v1-projects--project_id--expenses"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>draft</code></li> <li><code>approved</code></li></ul>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-v1-projects--project_id--pvxr">GET api/v1/projects/{project_id}/pvxr</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-projects--project_id--pvxr">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/projects/8/pvxr" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/pvxr"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-projects--project_id--pvxr">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-projects--project_id--pvxr" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-projects--project_id--pvxr"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-projects--project_id--pvxr"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-projects--project_id--pvxr" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-projects--project_id--pvxr">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-projects--project_id--pvxr" data-method="GET"
      data-path="api/v1/projects/{project_id}/pvxr"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-projects--project_id--pvxr', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-projects--project_id--pvxr"
                    onclick="tryItOut('GETapi-v1-projects--project_id--pvxr');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-projects--project_id--pvxr"
                    onclick="cancelTryOut('GETapi-v1-projects--project_id--pvxr');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-projects--project_id--pvxr"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/projects/{project_id}/pvxr</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-projects--project_id--pvxr"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-projects--project_id--pvxr"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="GETapi-v1-projects--project_id--pvxr"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-expenses--expense_id-">GET api/v1/expenses/{expense_id}</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-expenses--expense_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/expenses/1" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/expenses/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-expenses--expense_id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-expenses--expense_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-expenses--expense_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-expenses--expense_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-expenses--expense_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-expenses--expense_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-expenses--expense_id-" data-method="GET"
      data-path="api/v1/expenses/{expense_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-expenses--expense_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-expenses--expense_id-"
                    onclick="tryItOut('GETapi-v1-expenses--expense_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-expenses--expense_id-"
                    onclick="cancelTryOut('GETapi-v1-expenses--expense_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-expenses--expense_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/expenses/{expense_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-expenses--expense_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-expenses--expense_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>expense_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="expense_id"                data-endpoint="GETapi-v1-expenses--expense_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the expense. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-PUTapi-v1-expenses--expense_id-">PUT api/v1/expenses/{expense_id}</h2>

<p>
</p>



<span id="example-requests-PUTapi-v1-expenses--expense_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/expenses/1" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "cost_item_id=17"\
    --form "project_id=17"\
    --form "amount=13"\
    --form "date=2026-01-01T11:12:26"\
    --form "description=Amet iste laborum eius est dolor dolores."\
    --form "receipt_path=tdsufvyvddqamniihfqco"\
    --form "status=consequatur"\
    --form "receipt=@/private/var/folders/1p/t__mjx8s4bl01p68ls9f79300000gn/T/phplRGUFu" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/expenses/1"
);

const headers = {
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('cost_item_id', '17');
body.append('project_id', '17');
body.append('amount', '13');
body.append('date', '2026-01-01T11:12:26');
body.append('description', 'Amet iste laborum eius est dolor dolores.');
body.append('receipt_path', 'tdsufvyvddqamniihfqco');
body.append('status', 'consequatur');
body.append('receipt', document.querySelector('input[name="receipt"]').files[0]);

fetch(url, {
    method: "PUT",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-expenses--expense_id-">
</span>
<span id="execution-results-PUTapi-v1-expenses--expense_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-expenses--expense_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-expenses--expense_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-expenses--expense_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-expenses--expense_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-expenses--expense_id-" data-method="PUT"
      data-path="api/v1/expenses/{expense_id}"
      data-authed="0"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-expenses--expense_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-expenses--expense_id-"
                    onclick="tryItOut('PUTapi-v1-expenses--expense_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-expenses--expense_id-"
                    onclick="cancelTryOut('PUTapi-v1-expenses--expense_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-expenses--expense_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/expenses/{expense_id}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/expenses/{expense_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-expenses--expense_id-"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-expenses--expense_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>expense_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="expense_id"                data-endpoint="PUTapi-v1-expenses--expense_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the expense. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cost_item_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="cost_item_id"                data-endpoint="PUTapi-v1-expenses--expense_id-"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the cost_items table. Example: <code>17</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="PUTapi-v1-expenses--expense_id-"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the projects table. Example: <code>17</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>amount</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="amount"                data-endpoint="PUTapi-v1-expenses--expense_id-"
               value="13"
               data-component="body">
    <br>
<p>Must be at least 0.01. Must not be greater than 9999999999999.99. Example: <code>13</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="date"                data-endpoint="PUTapi-v1-expenses--expense_id-"
               value="2026-01-01T11:12:26"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-01-01T11:12:26</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="PUTapi-v1-expenses--expense_id-"
               value="Amet iste laborum eius est dolor dolores."
               data-component="body">
    <br>
<p>Must not be greater than 1000 characters. Example: <code>Amet iste laborum eius est dolor dolores.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>receipt</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="receipt"                data-endpoint="PUTapi-v1-expenses--expense_id-"
               value=""
               data-component="body">
    <br>
<p>Must be a file. Must not be greater than 10240 kilobytes. Example: <code>/private/var/folders/1p/t__mjx8s4bl01p68ls9f79300000gn/T/phplRGUFu</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>receipt_path</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="receipt_path"                data-endpoint="PUTapi-v1-expenses--expense_id-"
               value="tdsufvyvddqamniihfqco"
               data-component="body">
    <br>
<p>Must not be greater than 500 characters. Example: <code>tdsufvyvddqamniihfqco</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PUTapi-v1-expenses--expense_id-"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>draft</code></li> <li><code>approved</code></li></ul>
        </div>
        </form>

                    <h2 id="endpoints-DELETEapi-v1-expenses--expense_id-">DELETE api/v1/expenses/{expense_id}</h2>

<p>
</p>



<span id="example-requests-DELETEapi-v1-expenses--expense_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/expenses/1" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/expenses/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-expenses--expense_id-">
</span>
<span id="execution-results-DELETEapi-v1-expenses--expense_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-expenses--expense_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-expenses--expense_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-expenses--expense_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-expenses--expense_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-expenses--expense_id-" data-method="DELETE"
      data-path="api/v1/expenses/{expense_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-expenses--expense_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-expenses--expense_id-"
                    onclick="tryItOut('DELETEapi-v1-expenses--expense_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-expenses--expense_id-"
                    onclick="cancelTryOut('DELETEapi-v1-expenses--expense_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-expenses--expense_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/expenses/{expense_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-expenses--expense_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-expenses--expense_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>expense_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="expense_id"                data-endpoint="DELETEapi-v1-expenses--expense_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the expense. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-expenses--expense_id--receipt">GET api/v1/expenses/{expense_id}/receipt</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-expenses--expense_id--receipt">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/expenses/1/receipt" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/expenses/1/receipt"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-expenses--expense_id--receipt">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-expenses--expense_id--receipt" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-expenses--expense_id--receipt"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-expenses--expense_id--receipt"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-expenses--expense_id--receipt" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-expenses--expense_id--receipt">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-expenses--expense_id--receipt" data-method="GET"
      data-path="api/v1/expenses/{expense_id}/receipt"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-expenses--expense_id--receipt', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-expenses--expense_id--receipt"
                    onclick="tryItOut('GETapi-v1-expenses--expense_id--receipt');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-expenses--expense_id--receipt"
                    onclick="cancelTryOut('GETapi-v1-expenses--expense_id--receipt');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-expenses--expense_id--receipt"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/expenses/{expense_id}/receipt</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-expenses--expense_id--receipt"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-expenses--expense_id--receipt"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>expense_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="expense_id"                data-endpoint="GETapi-v1-expenses--expense_id--receipt"
               value="1"
               data-component="url">
    <br>
<p>The ID of the expense. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-projects--project_id--purchase-requests">GET api/v1/projects/{project_id}/purchase-requests</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-projects--project_id--purchase-requests">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/projects/8/purchase-requests" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/purchase-requests"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-projects--project_id--purchase-requests">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-projects--project_id--purchase-requests" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-projects--project_id--purchase-requests"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-projects--project_id--purchase-requests"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-projects--project_id--purchase-requests" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-projects--project_id--purchase-requests">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-projects--project_id--purchase-requests" data-method="GET"
      data-path="api/v1/projects/{project_id}/purchase-requests"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-projects--project_id--purchase-requests', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-projects--project_id--purchase-requests"
                    onclick="tryItOut('GETapi-v1-projects--project_id--purchase-requests');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-projects--project_id--purchase-requests"
                    onclick="cancelTryOut('GETapi-v1-projects--project_id--purchase-requests');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-projects--project_id--purchase-requests"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/projects/{project_id}/purchase-requests</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-projects--project_id--purchase-requests"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-projects--project_id--purchase-requests"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="GETapi-v1-projects--project_id--purchase-requests"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-projects--project_id--purchase-requests">POST api/v1/projects/{project_id}/purchase-requests</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-projects--project_id--purchase-requests">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/projects/8/purchase-requests" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"supplier_id\": 17,
    \"status\": \"draft\",
    \"notes\": \"mqeopfuudtdsufvyvddqa\",
    \"items\": [
        {
            \"cost_item_id\": 17,
            \"description\": \"Dolorum amet iste laborum eius est dolor.\",
            \"quantity\": 13,
            \"unit_price\": 19
        }
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/projects/8/purchase-requests"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "supplier_id": 17,
    "status": "draft",
    "notes": "mqeopfuudtdsufvyvddqa",
    "items": [
        {
            "cost_item_id": 17,
            "description": "Dolorum amet iste laborum eius est dolor.",
            "quantity": 13,
            "unit_price": 19
        }
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-projects--project_id--purchase-requests">
</span>
<span id="execution-results-POSTapi-v1-projects--project_id--purchase-requests" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-projects--project_id--purchase-requests"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-projects--project_id--purchase-requests"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-projects--project_id--purchase-requests" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-projects--project_id--purchase-requests">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-projects--project_id--purchase-requests" data-method="POST"
      data-path="api/v1/projects/{project_id}/purchase-requests"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-projects--project_id--purchase-requests', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-projects--project_id--purchase-requests"
                    onclick="tryItOut('POSTapi-v1-projects--project_id--purchase-requests');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-projects--project_id--purchase-requests"
                    onclick="cancelTryOut('POSTapi-v1-projects--project_id--purchase-requests');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-projects--project_id--purchase-requests"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/projects/{project_id}/purchase-requests</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-projects--project_id--purchase-requests"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-projects--project_id--purchase-requests"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>project_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="project_id"                data-endpoint="POSTapi-v1-projects--project_id--purchase-requests"
               value="8"
               data-component="url">
    <br>
<p>The ID of the project. Example: <code>8</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>supplier_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="supplier_id"                data-endpoint="POSTapi-v1-projects--project_id--purchase-requests"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the suppliers table. Example: <code>17</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="POSTapi-v1-projects--project_id--purchase-requests"
               value="draft"
               data-component="body">
    <br>
<p>Example: <code>draft</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>draft</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>notes</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="notes"                data-endpoint="POSTapi-v1-projects--project_id--purchase-requests"
               value="mqeopfuudtdsufvyvddqa"
               data-component="body">
    <br>
<p>Must not be greater than 1000 characters. Example: <code>mqeopfuudtdsufvyvddqa</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>items</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Must have at least 1 items.</p>
            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>cost_item_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="items.0.cost_item_id"                data-endpoint="POSTapi-v1-projects--project_id--purchase-requests"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the cost_items table. Example: <code>17</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="items.0.description"                data-endpoint="POSTapi-v1-projects--project_id--purchase-requests"
               value="Dolorum amet iste laborum eius est dolor."
               data-component="body">
    <br>
<p>Must not be greater than 500 characters. Example: <code>Dolorum amet iste laborum eius est dolor.</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>quantity</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="items.0.quantity"                data-endpoint="POSTapi-v1-projects--project_id--purchase-requests"
               value="13"
               data-component="body">
    <br>
<p>Must be at least 1. Example: <code>13</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>unit_price</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="items.0.unit_price"                data-endpoint="POSTapi-v1-projects--project_id--purchase-requests"
               value="19"
               data-component="body">
    <br>
<p>Must be at least 0. Must not be greater than 9999999999999.99. Example: <code>19</code></p>
                    </div>
                                    </details>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-v1-purchase-requests--purchaseRequest_id-">GET api/v1/purchase-requests/{purchaseRequest_id}</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-purchase-requests--purchaseRequest_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/purchase-requests/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/purchase-requests/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-purchase-requests--purchaseRequest_id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-purchase-requests--purchaseRequest_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-purchase-requests--purchaseRequest_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-purchase-requests--purchaseRequest_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-purchase-requests--purchaseRequest_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-purchase-requests--purchaseRequest_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-purchase-requests--purchaseRequest_id-" data-method="GET"
      data-path="api/v1/purchase-requests/{purchaseRequest_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-purchase-requests--purchaseRequest_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-purchase-requests--purchaseRequest_id-"
                    onclick="tryItOut('GETapi-v1-purchase-requests--purchaseRequest_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-purchase-requests--purchaseRequest_id-"
                    onclick="cancelTryOut('GETapi-v1-purchase-requests--purchaseRequest_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-purchase-requests--purchaseRequest_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/purchase-requests/{purchaseRequest_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-purchase-requests--purchaseRequest_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-purchase-requests--purchaseRequest_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>purchaseRequest_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="purchaseRequest_id"                data-endpoint="GETapi-v1-purchase-requests--purchaseRequest_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the purchaseRequest. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-PUTapi-v1-purchase-requests--purchaseRequest_id-">PUT api/v1/purchase-requests/{purchaseRequest_id}</h2>

<p>
</p>



<span id="example-requests-PUTapi-v1-purchase-requests--purchaseRequest_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/purchase-requests/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"supplier_id\": 17,
    \"status\": \"consequatur\",
    \"notes\": \"mqeopfuudtdsufvyvddqa\",
    \"items\": [
        {
            \"id\": 17,
            \"cost_item_id\": 17,
            \"description\": \"Dolorum amet iste laborum eius est dolor.\",
            \"quantity\": 13,
            \"unit_price\": 19
        }
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/purchase-requests/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "supplier_id": 17,
    "status": "consequatur",
    "notes": "mqeopfuudtdsufvyvddqa",
    "items": [
        {
            "id": 17,
            "cost_item_id": 17,
            "description": "Dolorum amet iste laborum eius est dolor.",
            "quantity": 13,
            "unit_price": 19
        }
    ]
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-purchase-requests--purchaseRequest_id-">
</span>
<span id="execution-results-PUTapi-v1-purchase-requests--purchaseRequest_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-purchase-requests--purchaseRequest_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-purchase-requests--purchaseRequest_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-purchase-requests--purchaseRequest_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-purchase-requests--purchaseRequest_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-purchase-requests--purchaseRequest_id-" data-method="PUT"
      data-path="api/v1/purchase-requests/{purchaseRequest_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-purchase-requests--purchaseRequest_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-purchase-requests--purchaseRequest_id-"
                    onclick="tryItOut('PUTapi-v1-purchase-requests--purchaseRequest_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-purchase-requests--purchaseRequest_id-"
                    onclick="cancelTryOut('PUTapi-v1-purchase-requests--purchaseRequest_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-purchase-requests--purchaseRequest_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/purchase-requests/{purchaseRequest_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-purchase-requests--purchaseRequest_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-purchase-requests--purchaseRequest_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>purchaseRequest_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="purchaseRequest_id"                data-endpoint="PUTapi-v1-purchase-requests--purchaseRequest_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the purchaseRequest. Example: <code>17</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>supplier_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="supplier_id"                data-endpoint="PUTapi-v1-purchase-requests--purchaseRequest_id-"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the suppliers table. Example: <code>17</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PUTapi-v1-purchase-requests--purchaseRequest_id-"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>draft</code></li> <li><code>submitted</code></li> <li><code>approved</code></li> <li><code>rejected</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>notes</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="notes"                data-endpoint="PUTapi-v1-purchase-requests--purchaseRequest_id-"
               value="mqeopfuudtdsufvyvddqa"
               data-component="body">
    <br>
<p>Must not be greater than 1000 characters. Example: <code>mqeopfuudtdsufvyvddqa</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>items</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
<br>
<p>Must have at least 1 items.</p>
            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="items.0.id"                data-endpoint="PUTapi-v1-purchase-requests--purchaseRequest_id-"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the purchase_request_items table. Example: <code>17</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>cost_item_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="items.0.cost_item_id"                data-endpoint="PUTapi-v1-purchase-requests--purchaseRequest_id-"
               value="17"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the cost_items table. Example: <code>17</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="items.0.description"                data-endpoint="PUTapi-v1-purchase-requests--purchaseRequest_id-"
               value="Dolorum amet iste laborum eius est dolor."
               data-component="body">
    <br>
<p>This field is required when <code>items</code> is present. Must not be greater than 500 characters. Example: <code>Dolorum amet iste laborum eius est dolor.</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>quantity</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="items.0.quantity"                data-endpoint="PUTapi-v1-purchase-requests--purchaseRequest_id-"
               value="13"
               data-component="body">
    <br>
<p>This field is required when <code>items</code> is present. Must be at least 1. Example: <code>13</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>unit_price</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="items.0.unit_price"                data-endpoint="PUTapi-v1-purchase-requests--purchaseRequest_id-"
               value="19"
               data-component="body">
    <br>
<p>This field is required when <code>items</code> is present. Must be at least 0. Must not be greater than 9999999999999.99. Example: <code>19</code></p>
                    </div>
                                    </details>
        </div>
        </form>

                    <h2 id="endpoints-DELETEapi-v1-purchase-requests--purchaseRequest_id-">DELETE api/v1/purchase-requests/{purchaseRequest_id}</h2>

<p>
</p>



<span id="example-requests-DELETEapi-v1-purchase-requests--purchaseRequest_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/purchase-requests/17" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/purchase-requests/17"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-purchase-requests--purchaseRequest_id-">
</span>
<span id="execution-results-DELETEapi-v1-purchase-requests--purchaseRequest_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-purchase-requests--purchaseRequest_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-purchase-requests--purchaseRequest_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-purchase-requests--purchaseRequest_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-purchase-requests--purchaseRequest_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-purchase-requests--purchaseRequest_id-" data-method="DELETE"
      data-path="api/v1/purchase-requests/{purchaseRequest_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-purchase-requests--purchaseRequest_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-purchase-requests--purchaseRequest_id-"
                    onclick="tryItOut('DELETEapi-v1-purchase-requests--purchaseRequest_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-purchase-requests--purchaseRequest_id-"
                    onclick="cancelTryOut('DELETEapi-v1-purchase-requests--purchaseRequest_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-purchase-requests--purchaseRequest_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/purchase-requests/{purchaseRequest_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-purchase-requests--purchaseRequest_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-purchase-requests--purchaseRequest_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>purchaseRequest_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="purchaseRequest_id"                data-endpoint="DELETEapi-v1-purchase-requests--purchaseRequest_id-"
               value="17"
               data-component="url">
    <br>
<p>The ID of the purchaseRequest. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-purchase-requests--purchaseRequest_id--submit">POST api/v1/purchase-requests/{purchaseRequest_id}/submit</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-purchase-requests--purchaseRequest_id--submit">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/purchase-requests/17/submit" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/purchase-requests/17/submit"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-purchase-requests--purchaseRequest_id--submit">
</span>
<span id="execution-results-POSTapi-v1-purchase-requests--purchaseRequest_id--submit" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-purchase-requests--purchaseRequest_id--submit"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-purchase-requests--purchaseRequest_id--submit"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-purchase-requests--purchaseRequest_id--submit" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-purchase-requests--purchaseRequest_id--submit">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-purchase-requests--purchaseRequest_id--submit" data-method="POST"
      data-path="api/v1/purchase-requests/{purchaseRequest_id}/submit"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-purchase-requests--purchaseRequest_id--submit', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-purchase-requests--purchaseRequest_id--submit"
                    onclick="tryItOut('POSTapi-v1-purchase-requests--purchaseRequest_id--submit');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-purchase-requests--purchaseRequest_id--submit"
                    onclick="cancelTryOut('POSTapi-v1-purchase-requests--purchaseRequest_id--submit');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-purchase-requests--purchaseRequest_id--submit"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/purchase-requests/{purchaseRequest_id}/submit</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-purchase-requests--purchaseRequest_id--submit"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-purchase-requests--purchaseRequest_id--submit"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>purchaseRequest_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="purchaseRequest_id"                data-endpoint="POSTapi-v1-purchase-requests--purchaseRequest_id--submit"
               value="17"
               data-component="url">
    <br>
<p>The ID of the purchaseRequest. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-purchase-requests--purchaseRequest_id--approve">POST api/v1/purchase-requests/{purchaseRequest_id}/approve</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-purchase-requests--purchaseRequest_id--approve">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/purchase-requests/17/approve" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/purchase-requests/17/approve"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-purchase-requests--purchaseRequest_id--approve">
</span>
<span id="execution-results-POSTapi-v1-purchase-requests--purchaseRequest_id--approve" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-purchase-requests--purchaseRequest_id--approve"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-purchase-requests--purchaseRequest_id--approve"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-purchase-requests--purchaseRequest_id--approve" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-purchase-requests--purchaseRequest_id--approve">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-purchase-requests--purchaseRequest_id--approve" data-method="POST"
      data-path="api/v1/purchase-requests/{purchaseRequest_id}/approve"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-purchase-requests--purchaseRequest_id--approve', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-purchase-requests--purchaseRequest_id--approve"
                    onclick="tryItOut('POSTapi-v1-purchase-requests--purchaseRequest_id--approve');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-purchase-requests--purchaseRequest_id--approve"
                    onclick="cancelTryOut('POSTapi-v1-purchase-requests--purchaseRequest_id--approve');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-purchase-requests--purchaseRequest_id--approve"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/purchase-requests/{purchaseRequest_id}/approve</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-purchase-requests--purchaseRequest_id--approve"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-purchase-requests--purchaseRequest_id--approve"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>purchaseRequest_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="purchaseRequest_id"                data-endpoint="POSTapi-v1-purchase-requests--purchaseRequest_id--approve"
               value="17"
               data-component="url">
    <br>
<p>The ID of the purchaseRequest. Example: <code>17</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-purchase-requests--purchaseRequest_id--reject">POST api/v1/purchase-requests/{purchaseRequest_id}/reject</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-purchase-requests--purchaseRequest_id--reject">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/purchase-requests/17/reject" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/purchase-requests/17/reject"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-purchase-requests--purchaseRequest_id--reject">
</span>
<span id="execution-results-POSTapi-v1-purchase-requests--purchaseRequest_id--reject" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-purchase-requests--purchaseRequest_id--reject"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-purchase-requests--purchaseRequest_id--reject"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-purchase-requests--purchaseRequest_id--reject" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-purchase-requests--purchaseRequest_id--reject">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-purchase-requests--purchaseRequest_id--reject" data-method="POST"
      data-path="api/v1/purchase-requests/{purchaseRequest_id}/reject"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-purchase-requests--purchaseRequest_id--reject', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-purchase-requests--purchaseRequest_id--reject"
                    onclick="tryItOut('POSTapi-v1-purchase-requests--purchaseRequest_id--reject');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-purchase-requests--purchaseRequest_id--reject"
                    onclick="cancelTryOut('POSTapi-v1-purchase-requests--purchaseRequest_id--reject');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-purchase-requests--purchaseRequest_id--reject"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/purchase-requests/{purchaseRequest_id}/reject</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-purchase-requests--purchaseRequest_id--reject"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-purchase-requests--purchaseRequest_id--reject"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>purchaseRequest_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="purchaseRequest_id"                data-endpoint="POSTapi-v1-purchase-requests--purchaseRequest_id--reject"
               value="17"
               data-component="url">
    <br>
<p>The ID of the purchaseRequest. Example: <code>17</code></p>
            </div>
                    </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
