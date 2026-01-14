<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Import Projects - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f3f4f6;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 24px;
            border-radius: 8px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #111827;
            margin-bottom: 8px;
        }
        .header p {
            color: #6b7280;
        }
        .card {
            background: white;
            padding: 24px;
            border-radius: 8px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
        }
        .hint {
            display: block;
            margin-top: 4px;
            color: #6b7280;
            font-size: 14px;
        }
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }
        textarea {
            resize: vertical;
            font-family: monospace;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background: #2563eb;
        }
        .btn-primary:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        .alert {
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .error-list {
            margin-top: 12px;
            padding-left: 20px;
        }
        .error-list li {
            margin: 4px 0;
        }
        .instructions {
            color: #4b5563;
            line-height: 1.6;
        }
        .instructions h3 {
            margin-top: 20px;
            margin-bottom: 12px;
            color: #111827;
        }
        .instructions ul {
            margin-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
        }
        .instructions pre {
            background: #f9fafb;
            padding: 16px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 12px;
            margin: 12px 0;
        }
        .loader {
            display: none;
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            margin-left: 12px;
            display: inline-block;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loading .loader {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Import Projects from CSV</h1>
            <p>Mass import projects with images from CSV files</p>
        </div>

        <div id="alert-container"></div>

        <div class="card">
            <form id="import-form" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label for="csv_file">CSV File</label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt">
                    <span class="hint">Select CSV file (max 10MB). File should contain headers.</span>
                </div>

                <div class="form-group">
                    <label for="csv_content">Or Paste CSV Content</label>
                    <textarea id="csv_content" name="csv_content" rows="6" placeholder="Paste CSV content here if you don't have a file"></textarea>
                    <span class="hint">Alternative to file upload</span>
                </div>

                <div class="form-group">
                    <label for="mode">Import Mode</label>
                    <select id="mode" name="mode" required>
                        <option value="create_or_update">Create or Update (recommended)</option>
                        <option value="create">Create Only (skip existing)</option>
                        <option value="update">Update Only (error if not found)</option>
                    </select>
                    <span class="hint">Choose how to handle existing projects</span>
                </div>

                <button type="submit" class="btn btn-primary" id="submit-btn">
                    Import Projects
                    <span class="loader" style="display: none;"></span>
                </button>
            </form>
        </div>

        <div class="card">
            <div class="instructions">
                <h3>CSV File Format</h3>
                <p>The CSV file must contain the following columns (delimiter: semicolon or comma):</p>
                
                <h4 style="margin-top: 20px;">Required Columns:</h4>
                <ul>
                    <li><strong>title</strong> - Project title (string, max 255 chars)</li>
                    <li><strong>category_slug</strong> - Category slug (must exist in database)</li>
                    <li><strong>price_from</strong> - Price from (number, can be empty)</li>
                    <li><strong>price_to</strong> - Price to (number, can be empty)</li>
                    <li><strong>area</strong> - Area in sq.m (number, can be empty)</li>
                    <li><strong>floors</strong> - Number of floors (1-3, can be empty)</li>
                    <li><strong>bedrooms</strong> - Number of bedrooms (0-20, can be empty)</li>
                    <li><strong>bathrooms</strong> - Number of bathrooms (0-20, can be empty)</li>
                    <li><strong>has_garage</strong> - Has garage (0/1, true/false, yes/no)</li>
                    <li><strong>roof_type</strong> - Roof type (string)</li>
                    <li><strong>style</strong> - Project style (string)</li>
                </ul>

                <h4 style="margin-top: 20px;">Optional Columns:</h4>
                <ul>
                    <li><strong>external_id</strong> - External identifier for updates (string)</li>
                    <li><strong>description</strong> - Project description (text)</li>
                    <li><strong>main_image_url</strong> - URL to main image (will be downloaded)</li>
                    <li><strong>gallery_urls</strong> - Comma-separated URLs for gallery images</li>
                    <li><strong>meta_title</strong> - SEO meta title</li>
                    <li><strong>meta_description</strong> - SEO meta description</li>
                </ul>

                <h4 style="margin-top: 20px;">Example CSV:</h4>
                <pre>external_id;title;description;category_slug;price_from;price_to;area;floors;bedrooms;bathrooms;has_garage;roof_type;style;main_image_url;gallery_urls
proj_001;Cozy House;Small one-story house;one-story-houses;2500000;3000000;120;1;3;2;1;gable;modern;https://example.com/img1.jpg;https://ex.com/g1.jpg,https://ex.com/g2.jpg
proj_002;Classic Bath;Traditional Russian bath;baths-with-steam-room;800000;1200000;40;1;0;1;0;shed;russian;https://example.com/bath.jpg;https://ex.com/bg1.jpg</pre>

                <h4 style="margin-top: 20px;">Notes:</h4>
                <ul>
                    <li>Files larger than 100 rows will be processed in background queue</li>
                    <li>Images will be automatically downloaded from provided URLs</li>
                    <li>Slugs will be auto-generated from titles</li>
                    <li>All projects will be published by default</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('import-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submit-btn');
            const loader = submitBtn.querySelector('.loader');
            const alertContainer = document.getElementById('alert-container');
            
            // Show loader
            submitBtn.disabled = true;
            loader.style.display = 'inline-block';
            alertContainer.innerHTML = '';
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('{{ route("admin.projects.import.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    let message = data.message;
                    
                    if (data.queued) {
                        alertContainer.innerHTML = `
                            <div class="alert alert-success">
                                ${message}
                                <br><small>You will receive results when processing completes.</small>
                            </div>
                        `;
                    } else {
                        let html = `<div class="alert alert-success">${message}</div>`;
                        
                        if (data.results && data.results.errors && data.results.errors.length > 0) {
                            html += '<div class="alert alert-error"><strong>Errors:</strong>';
                            html += '<ul class="error-list">';
                            data.results.errors.forEach(err => {
                                html += `<li>Row ${err.row}: ${err.error}</li>`;
                            });
                            html += '</ul></div>';
                        }
                        
                        if (data.results && data.results.warnings && data.results.warnings.length > 0) {
                            html += '<div class="alert alert-error"><strong>Warnings:</strong>';
                            html += '<ul class="error-list">';
                            data.results.warnings.forEach(warn => {
                                html += `<li>${warn}</li>`;
                            });
                            html += '</ul></div>';
                        }
                        
                        alertContainer.innerHTML = html;
                    }
                    
                    // Reset form
                    this.reset();
                } else {
                    let html = `<div class="alert alert-error">${data.message}`;
                    
                    if (data.errors) {
                        html += '<ul class="error-list">';
                        Object.values(data.errors).forEach(errArr => {
                            errArr.forEach(err => {
                                html += `<li>${err}</li>`;
                            });
                        });
                        html += '</ul>';
                    }
                    
                    html += '</div>';
                    alertContainer.innerHTML = html;
                }
            } catch (error) {
                alertContainer.innerHTML = `<div class="alert alert-error">Error: ${error.message}</div>`;
            } finally {
                submitBtn.disabled = false;
                loader.style.display = 'none';
                window.scrollTo(0, 0);
            }
        });
    </script>
</body>
</html>
