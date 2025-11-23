<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentationTitle }}</title>
    <link rel="stylesheet" type="text/css" href="{{ l5_swagger_asset($documentation, 'swagger-ui.css') }}">
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-32x32.png') }}" sizes="32x32"/>
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-16x16.png') }}" sizes="16x16"/>
    <style>
    /* ===== BASE STYLES ===== */
    html {
        box-sizing: border-box;
        overflow: -moz-scrollbars-vertical;
        overflow-y: scroll;
    }
    *,
    *:before,
    *:after {
        box-sizing: inherit;
    }

    body {
        margin: 0;
        background: #ffffff;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }

    /* ===== SIMPLE HEADER ===== */
    .swagger-simple-header {
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-bottom: 1px solid rgba(16, 185, 129, 0.15);
        padding: 1.5rem 2rem;
        margin-bottom: 0;
    }

    .swagger-simple-header-content {
        max-width: 1200px;
        margin: 0 auto;
    }

    .swagger-simple-header h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #065f46;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .swagger-simple-header h1 i {
        font-size: 1.5rem;
        color: #10b981;
        background: rgba(255, 255, 255, 0.5);
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 6px;
        padding: 0.4rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
    }

    .swagger-simple-header p {
        margin: 0.5rem 0 0 0;
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 300;
    }

    /* ===== SWAGGER UI CUSTOMIZATION ===== */
    .swagger-ui {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem 2rem 2rem 2rem;
    }

    /* Top Bar - Glassy */
    .swagger-ui .topbar {
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(0, 0, 0, 0.12);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    /* Info Section - Glassy Card */
    .swagger-ui .info {
        margin: 1rem 0;
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(0, 0, 0, 0.12);
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .swagger-ui .info .title {
        color: #065f46;
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .swagger-ui .info .description {
        color: #4b5563;
        line-height: 1.6;
        font-weight: 300;
    }

    /* HTTP Method Colors - Subtle Greenish Theme */
    .swagger-ui .opblock.opblock-get {
        background: rgba(16, 185, 129, 0.05);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .swagger-ui .opblock.opblock-get .opblock-summary-method {
        background: #10b981;
    }

    .swagger-ui .opblock.opblock-post {
        background: rgba(5, 150, 105, 0.05);
        border: 1px solid rgba(5, 150, 105, 0.2);
    }

    .swagger-ui .opblock.opblock-post .opblock-summary-method {
        background: #059669;
    }

    .swagger-ui .opblock.opblock-put {
        background: rgba(5, 95, 70, 0.05);
        border: 1px solid rgba(5, 95, 70, 0.2);
    }

    .swagger-ui .opblock.opblock-put .opblock-summary-method {
        background: #065f46;
    }

    .swagger-ui .opblock.opblock-delete {
        background: rgba(239, 68, 68, 0.05);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .swagger-ui .opblock.opblock-delete .opblock-summary-method {
        background: #ef4444;
    }

    .swagger-ui .opblock.opblock-patch {
        background: rgba(139, 92, 246, 0.05);
        border: 1px solid rgba(139, 92, 246, 0.2);
    }

    .swagger-ui .opblock.opblock-patch .opblock-summary-method {
        background: #8b5cf6;
    }

    /* Operation Blocks - Glassy */
    .swagger-ui .opblock {
        border-radius: 8px;
        margin-bottom: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: all 0.2s ease;
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .swagger-ui .opblock:hover {
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    .swagger-ui .opblock-summary {
        border-radius: 8px 8px 0 0;
    }

    .swagger-ui .opblock-summary-method {
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    /* Tags - Glassy Cards */
    .swagger-ui .opblock-tag {
        border-radius: 8px;
        margin-bottom: 1rem;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(0, 0, 0, 0.12);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .swagger-ui .opblock-tag-section {
        border-left: 2px solid #10b981;
        padding-left: 0.75rem;
    }

    .swagger-ui .opblock-tag {
        font-size: 1.125rem;
        font-weight: 600;
        color: #065f46;
    }

    /* Buttons - Outlined Style */
    .swagger-ui .btn {
        border-radius: 6px;
        font-weight: 300;
        transition: all 0.2s ease;
        border: 1px solid;
    }

    .swagger-ui .btn.authorize {
        background: transparent;
        border-color: rgba(16, 185, 129, 0.6);
        color: rgba(16, 185, 129, 0.9);
    }

    .swagger-ui .btn.authorize:hover {
        background: rgba(16, 185, 129, 0.05);
        border-color: rgba(16, 185, 129, 0.8);
        color: rgba(16, 185, 129, 1);
    }

    .swagger-ui .btn.execute {
        background: transparent;
        border-color: rgba(16, 185, 129, 0.6);
        color: rgba(16, 185, 129, 0.9);
    }

    .swagger-ui .btn.execute:hover {
        background: rgba(16, 185, 129, 0.05);
        border-color: rgba(16, 185, 129, 0.8);
        color: rgba(16, 185, 129, 1);
    }

    .swagger-ui .btn.cancel {
        background: transparent;
        border-color: rgba(107, 114, 128, 0.6);
        color: rgba(107, 114, 128, 0.9);
    }

    .swagger-ui .btn.cancel:hover {
        background: rgba(107, 114, 128, 0.05);
        border-color: rgba(107, 114, 128, 0.8);
    }

    /* Input Fields - Glassy */
    .swagger-ui input[type=text],
    .swagger-ui input[type=password],
    .swagger-ui input[type=search],
    .swagger-ui textarea,
    .swagger-ui select {
        border-radius: 6px;
        border: 1px solid rgba(0, 0, 0, 0.12);
        padding: 0.5rem 0.75rem;
        transition: all 0.2s ease;
        background: rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }

    .swagger-ui input[type=text]:focus,
    .swagger-ui input[type=password]:focus,
    .swagger-ui input[type=search]:focus,
    .swagger-ui textarea:focus,
    .swagger-ui select:focus {
        border-color: rgba(16, 185, 129, 0.4);
        outline: none;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        background: rgba(255, 255, 255, 0.7);
    }

    /* Code Blocks */
    .swagger-ui .highlight-code {
        border-radius: 6px;
        background: rgba(249, 250, 251, 0.5);
        border: 1px solid rgba(0, 0, 0, 0.12);
    }

    .swagger-ui .microlight {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    }

    /* Response Codes */
    .swagger-ui .response-col_status {
        font-weight: 600;
    }

    .swagger-ui .response-200 {
        color: #10b981;
    }

    .swagger-ui .response-201 {
        color: #059669;
    }

    .swagger-ui .response-400,
    .swagger-ui .response-401,
    .swagger-ui .response-404,
    .swagger-ui .response-422,
    .swagger-ui .response-500 {
        color: #ef4444;
    }

    /* Tables */
    .swagger-ui table {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.12);
    }

    .swagger-ui table thead tr {
        background: rgba(249, 250, 251, 0.5);
    }

    .swagger-ui table thead tr th {
        color: #065f46;
        font-weight: 600;
        border-bottom: 1px solid rgba(16, 185, 129, 0.2);
    }

    /* Models */
    .swagger-ui .model-box {
        border-radius: 8px;
        border: 1px solid rgba(0, 0, 0, 0.12);
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .swagger-ui .model-title {
        color: #065f46;
        font-weight: 600;
    }

    /* Filter Input */
    .swagger-ui .operation-filter-input {
        border-radius: 6px;
        border: 1px solid rgba(0, 0, 0, 0.12);
        padding: 0.75rem 1rem;
        background: rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }

    .swagger-ui .operation-filter-input:focus {
        border-color: rgba(16, 185, 129, 0.4);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        background: rgba(255, 255, 255, 0.7);
    }

    /* Loading */
    .swagger-ui .loading-container {
        color: #10b981;
    }

    /* Scrollbar Styling - Subtle */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f9fafb;
    }

    ::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, 0.3);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: rgba(16, 185, 129, 0.5);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .swagger-simple-header {
            padding: 1rem;
        }

        .swagger-simple-header h1 {
            font-size: 1.25rem;
        }

        .swagger-ui {
            padding: 0.5rem 1rem 1rem 1rem;
        }
    }
    </style>
    @if(config('l5-swagger.defaults.ui.display.dark_mode'))
        <style>
            body#dark-mode,
            #dark-mode .scheme-container {
                background: #1b1b1b;
            }
            #dark-mode .swagger-simple-header {
                background: rgba(0, 0, 0, 0.4);
                border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            }
            #dark-mode .swagger-simple-header h1 {
                color: rgba(255, 255, 255, 0.95);
            }
            #dark-mode .swagger-simple-header h1 i {
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.18);
                color: #10b981;
            }
            #dark-mode .swagger-simple-header p {
                color: rgba(255, 255, 255, 0.7);
            }
            #dark-mode .scheme-container,
            #dark-mode .opblock .opblock-section-header{
                box-shadow: 0 1px 2px 0 rgba(255, 255, 255, 0.15);
            }
            #dark-mode .operation-filter-input,
            #dark-mode .dialog-ux .modal-ux,
            #dark-mode input[type=email],
            #dark-mode input[type=file],
            #dark-mode input[type=password],
            #dark-mode input[type=search],
            #dark-mode input[type=text],
            #dark-mode textarea{
                background: #343434;
                color: #e7e7e7;
            }
            #dark-mode .title,
            #dark-mode li,
            #dark-mode p,
            #dark-mode table,
            #dark-mode label,
            #dark-mode .opblock-tag,
            #dark-mode .opblock .opblock-summary-operation-id,
            #dark-mode .opblock .opblock-summary-path,
            #dark-mode .opblock .opblock-summary-path__deprecated,
            #dark-mode h1,
            #dark-mode h2,
            #dark-mode h3,
            #dark-mode h4,
            #dark-mode h5,
            #dark-mode .btn,
            #dark-mode .tab li,
            #dark-mode .parameter__name,
            #dark-mode .parameter__type,
            #dark-mode .prop-format,
            #dark-mode .loading-container .loading:after{
                color: #e7e7e7;
            }
            #dark-mode .opblock-description-wrapper p,
            #dark-mode .opblock-external-docs-wrapper p,
            #dark-mode .opblock-title_normal p,
            #dark-mode .response-col_status,
            #dark-mode table thead tr td,
            #dark-mode table thead tr th,
            #dark-mode .response-col_links,
            #dark-mode .swagger-ui{
                color: wheat;
            }
            #dark-mode .parameter__extension,
            #dark-mode .parameter__in,
            #dark-mode .model-title{
                color: #949494;
            }
            #dark-mode table thead tr td,
            #dark-mode table thead tr th{
                border-color: rgba(120,120,120,.2);
            }
            #dark-mode .opblock .opblock-section-header{
                background: transparent;
            }
            #dark-mode .opblock.opblock-post{
                background: rgba(16, 185, 129, 0.15);
            }
            #dark-mode .opblock.opblock-get{
                background: rgba(16, 185, 129, 0.15);
            }
            #dark-mode .opblock.opblock-put{
                background: rgba(5, 95, 70, 0.15);
            }
            #dark-mode .opblock.opblock-delete{
                background: rgba(249,62,62,.15);
            }
            #dark-mode .loading-container .loading:before{
                border-color: rgba(255,255,255,10%);
                border-top-color: rgba(255,255,255,.6);
            }
            #dark-mode svg:not(:root){
                fill: #e7e7e7;
            }
            #dark-mode .opblock-summary-description {
                color: #fafafa;
            }
            #dark-mode .swagger-ui .info,
            #dark-mode .swagger-ui .opblock-tag,
            #dark-mode .swagger-ui .opblock {
                background: rgba(0, 0, 0, 0.4);
                border: 1px solid rgba(255, 255, 255, 0.12);
            }
        </style>
    @endif
</head>

<body @if(config('l5-swagger.defaults.ui.display.dark_mode')) id="dark-mode" @endif>
    <!-- Simple Header -->
    <div class="swagger-simple-header">
        <div class="swagger-simple-header-content">
            <h1>
                <i class="ri-book-open-line"></i>
                <span>{{ $documentationTitle }}</span>
            </h1>
            <p>API documentation for the NCDMB Document Management System</p>
        </div>
    </div>

    <div id="swagger-ui"></div>

    <script src="{{ l5_swagger_asset($documentation, 'swagger-ui-bundle.js') }}"></script>
    <script src="{{ l5_swagger_asset($documentation, 'swagger-ui-standalone-preset.js') }}"></script>
    <script>
    window.onload = function() {
        const urls = [];

        @foreach($urlsToDocs as $title => $url)
            urls.push({name: "{{ $title }}", url: "{{ $url }}"});
        @endforeach

        // Build a system
        const ui = SwaggerUIBundle({
            dom_id: '#swagger-ui',
            urls: urls,
            "urls.primaryName": "{{ $documentationTitle }}",
            operationsSorter: {!! isset($operationsSorter) ? '"' . $operationsSorter . '"' : 'null' !!},
            configUrl: {!! isset($configUrl) ? '"' . $configUrl . '"' : 'null' !!},
            validatorUrl: {!! isset($validatorUrl) ? '"' . $validatorUrl . '"' : 'null' !!},
            oauth2RedirectUrl: "{{ route('l5-swagger.'.$documentation.'.oauth2_callback', [], $useAbsolutePath) }}",

            requestInterceptor: function(request) {
                request.headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
                return request;
            },

            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],

            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],

            layout: "StandaloneLayout",
            docExpansion : "{!! config('l5-swagger.defaults.ui.display.doc_expansion', 'none') !!}",
            deepLinking: true,
            filter: {!! config('l5-swagger.defaults.ui.display.filter') ? 'true' : 'false' !!},
            persistAuthorization: "{!! config('l5-swagger.defaults.ui.authorization.persist_authorization') ? 'true' : 'false' !!}",

        })

        window.ui = ui

        @if(in_array('oauth2', array_column(config('l5-swagger.defaults.securityDefinitions.securitySchemes'), 'type')))
        ui.initOAuth({
            usePkceWithAuthorizationCodeGrant: "{!! (bool)config('l5-swagger.defaults.ui.authorization.oauth2.use_pkce_with_authorization_code_grant') !!}"
        })
        @endif
    }
    </script>
</body>
</html>
