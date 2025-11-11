# Test login and cards access with session
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

# First get login page to get CSRF token
$loginPage = Invoke-WebRequest -Uri "http://127.0.0.1:8000/login" -Method GET -SessionVariable session

# Extract CSRF token from hidden input
$csrfToken = ""
if ($loginPage.InputFields) {
    foreach ($field in $loginPage.InputFields) {
        if ($field.name -eq "_token") {
            $csrfToken = $field.value
            break
        }
    }
}

Write-Host "CSRF Token: $csrfToken"
Write-Host "Token Length: $($csrfToken.Length)"

# Prepare login data
$loginData = @{
    "_token" = $csrfToken
    "email" = "admin"
    "password" = "password"
}

# Convert to form data
$formData = ""
foreach ($key in $loginData.Keys) {
    if ($formData -ne "") { $formData += "&" }
    $formData += [System.Web.HttpUtility]::UrlEncode($key) + "=" + [System.Web.HttpUtility]::UrlEncode($loginData[$key])
}

try {
    # Attempt login
    $loginResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/login" -Method POST -Body $formData -ContentType "application/x-www-form-urlencoded" -WebSession $session -MaximumRedirection 0 -ErrorAction SilentlyContinue
    Write-Host "Login Status: $($loginResponse.StatusCode)"
    Write-Host "Login Headers: $($loginResponse.Headers | Out-String)"
    
    # Now try to access cards with the session
    $cardsResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/cards" -Method GET -WebSession $session
    Write-Host "Cards Status: $($cardsResponse.StatusCode)"
    Write-Host "Cards page accessed successfully!"
    Write-Host "Response length: $($cardsResponse.Content.Length) characters"
    
} catch {
    Write-Host "Error occurred: $($_.Exception.Message)"
    Write-Host "Status Code: $($_.Exception.Response.StatusCode.value__)"
}