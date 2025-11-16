<?php
/**
 * Generate Laravel APP_KEY
 * 
 * File ini digunakan untuk generate APP_KEY tanpa CLI
 * Useful untuk cPanel yang tidak punya terminal access
 * 
 * Cara pakai:
 * 1. Upload file ini ke folder root Laravel
 * 2. Akses via browser: https://yourdomain.com/generate-key.php
 * 3. Copy APP_KEY yang dihasilkan
 * 4. Paste ke file .env
 * 5. DELETE file ini setelah selesai (PENTING!)
 */

// Generate random 32 character string
$key = base64_encode(random_bytes(32));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel APP_KEY Generator</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .key-container {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        
        .label {
            color: #666;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .key {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            color: #333;
            word-break: break-all;
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            margin-bottom: 15px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .instructions {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .instructions h3 {
            color: #856404;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .instructions ol {
            color: #856404;
            padding-left: 20px;
            font-size: 14px;
            line-height: 1.8;
        }
        
        .warning {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            color: #721c24;
            font-weight: 600;
            font-size: 14px;
        }
        
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            color: #155724;
            font-size: 14px;
            display: none;
        }
        
        .success.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔑 Laravel APP_KEY Generator</h1>
        <p class="subtitle">Generate secure application key for your Laravel project</p>
        
        <div class="key-container">
            <div class="label">Your Generated APP_KEY:</div>
            <div class="key" id="appKey">base64:<?php echo $key; ?></div>
            <button class="btn" onclick="copyKey()">📋 Copy to Clipboard</button>
        </div>
        
        <div class="success" id="successMessage">
            ✅ APP_KEY copied to clipboard successfully!
        </div>
        
        <div class="instructions">
            <h3>📝 How to Use:</h3>
            <ol>
                <li>Click the "Copy to Clipboard" button above</li>
                <li>Open your <code>.env</code> file in cPanel File Manager</li>
                <li>Find the line: <code>APP_KEY=</code></li>
                <li>Paste the copied key after the equals sign</li>
                <li>Save the <code>.env</code> file</li>
                <li>Refresh your Laravel application</li>
            </ol>
        </div>
        
        <div class="warning">
            ⚠️ IMPORTANT: Delete this file (generate-key.php) after copying the key for security reasons!
        </div>
    </div>
    
    <script>
        function copyKey() {
            const keyElement = document.getElementById('appKey');
            const successMessage = document.getElementById('successMessage');
            
            // Create temporary textarea
            const textarea = document.createElement('textarea');
            textarea.value = keyElement.textContent;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            
            // Select and copy
            textarea.select();
            document.execCommand('copy');
            
            // Remove temporary textarea
            document.body.removeChild(textarea);
            
            // Show success message
            successMessage.classList.add('show');
            
            // Hide success message after 3 seconds
            setTimeout(() => {
                successMessage.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>
