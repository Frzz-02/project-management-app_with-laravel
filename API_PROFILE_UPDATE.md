# Profile Update API Documentation

API endpoint untuk update profile user yang sedang login.

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
Memerlukan Bearer Token (Laravel Sanctum).

```
Authorization: Bearer {your_token}
```

---

## 📝 Update Profile Endpoint

**Update profile user yang sedang login.**

**PENTING:** Email **TIDAK BISA** diubah untuk alasan keamanan.

```http
PUT /profile
```

### Request Body

Semua field bersifat **optional**. Hanya kirim field yang ingin diubah.

```json
{
  "username": "new_username",           // Optional - Username baru (unique)
  "full_name": "New Full Name",         // Optional - Nama lengkap baru
  "password": "newpassword123",         // Optional - Password baru (min 6 karakter)
  "current_task_status": "Working on login feature",  // Optional - Status task saat ini
  "role": "admin"                       // Optional - Role: "admin" atau "user"
}
```

### Field yang Bisa Di-edit:

| Field | Type | Rules | Description |
|-------|------|-------|-------------|
| `username` | string | max:255, unique | Username baru (harus unique) |
| `full_name` | string | max:255 | Nama lengkap user |
| `password` | string | min:6 | Password baru (akan di-hash otomatis) |
| `current_task_status` | string | max:255 | Status task yang sedang dikerjakan |
| `role` | string | in:admin,user | Role user dalam sistem |

### Field yang TIDAK Bisa Di-edit:

- ❌ **email** - Tidak bisa diubah untuk alasan keamanan

---

## 📋 Example Requests

### 1. Update Username Saja

```bash
curl -X PUT "http://localhost:8000/api/v1/profile" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "username": "john_doe_2024"
  }'
```

### 2. Update Full Name dan Password

```bash
curl -X PUT "http://localhost:8000/api/v1/profile" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "full_name": "John Doe Updated",
    "password": "newSecurePassword123"
  }'
```

### 3. Update Multiple Fields

```bash
curl -X PUT "http://localhost:8000/api/v1/profile" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "username": "johndoe2024",
    "full_name": "John Doe Updated",
    "current_task_status": "Working on Flutter app",
    "role": "admin"
  }'
```

### 4. Update Current Task Status Only

```bash
curl -X PUT "http://localhost:8000/api/v1/profile" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "current_task_status": "Completing time tracking feature"
  }'
```

---

## ✅ Success Response

**Status Code:** `200 OK`

```json
{
  "success": true,
  "message": "Profile berhasil diupdate",
  "data": {
    "id": 1,
    "username": "johndoe2024",
    "full_name": "John Doe Updated",
    "email": "john@example.com",
    "role": "admin",
    "current_task_status": "Working on Flutter app",
    "created_projects": [...],
    "project_memberships": [...],
    "created_cards": [...],
    "comments": [...],
    "time_logs": [...]
  }
}
```

---

## ❌ Error Responses

### 1. Validation Error - Username Already Taken

**Status Code:** `422 Unprocessable Entity`

```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "username": [
      "Username sudah digunakan oleh user lain"
    ]
  }
}
```

### 2. Validation Error - Password Too Short

**Status Code:** `422 Unprocessable Entity`

```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "password": [
      "Password minimal 6 karakter"
    ]
  }
}
```

### 3. Validation Error - Invalid Role

**Status Code:** `422 Unprocessable Entity`

```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "role": [
      "Role harus admin atau user"
    ]
  }
}
```

### 4. Unauthorized

**Status Code:** `401 Unauthorized`

```json
{
  "message": "Unauthenticated."
}
```

### 5. Server Error

**Status Code:** `500 Internal Server Error`

```json
{
  "success": false,
  "message": "Gagal mengupdate profile: [error message]"
}
```

---

## 📱 Flutter Integration Example

### Setup Dio Client

```dart
import 'package:dio/dio.dart';

class ApiService {
  final Dio dio = Dio(BaseOptions(
    baseUrl: 'http://localhost:8000/api/v1',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  ));

  // Set bearer token after login
  void setToken(String token) {
    dio.options.headers['Authorization'] = 'Bearer $token';
  }
}
```

### Update Profile Method

```dart
class ProfileService {
  final ApiService _apiService;

  ProfileService(this._apiService);

  /// Update user profile
  /// 
  /// Parameters bersifat optional, hanya kirim yang ingin diubah
  Future<Map<String, dynamic>> updateProfile({
    String? username,
    String? fullName,
    String? password,
    String? currentTaskStatus,
    String? role,
  }) async {
    try {
      // Buat request body hanya dengan field yang tidak null
      final Map<String, dynamic> data = {};
      
      if (username != null) data['username'] = username;
      if (fullName != null) data['full_name'] = fullName;
      if (password != null) data['password'] = password;
      if (currentTaskStatus != null) data['current_task_status'] = currentTaskStatus;
      if (role != null) data['role'] = role;

      final response = await _apiService.dio.put('/profile', data: data);

      if (response.data['success']) {
        return response.data;
      } else {
        throw Exception('Failed to update profile');
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 422) {
        // Validation error
        throw Exception(e.response?.data['message'] ?? 'Validation failed');
      } else if (e.response?.statusCode == 401) {
        // Unauthorized
        throw Exception('Unauthorized. Please login again.');
      } else {
        throw Exception('Failed to update profile: ${e.message}');
      }
    }
  }
}
```

### UI Example - Edit Profile Screen

```dart
class EditProfileScreen extends StatefulWidget {
  @override
  _EditProfileScreenState createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _usernameController = TextEditingController();
  final _fullNameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _currentTaskController = TextEditingController();
  
  bool _isLoading = false;
  String? _selectedRole;

  @override
  void initState() {
    super.initState();
    _loadCurrentUserData();
  }

  Future<void> _loadCurrentUserData() async {
    // Load data user dari endpoint /me
    try {
      final response = await dio.get('/me');
      final userData = response.data['data'];
      
      setState(() {
        _usernameController.text = userData['username'] ?? '';
        _fullNameController.text = userData['full_name'] ?? '';
        _currentTaskController.text = userData['current_task_status'] ?? '';
        _selectedRole = userData['role'];
      });
    } catch (e) {
      print('Error loading user data: $e');
    }
  }

  Future<void> _updateProfile() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final profileService = ProfileService(ApiService());
      
      await profileService.updateProfile(
        username: _usernameController.text.isNotEmpty 
            ? _usernameController.text 
            : null,
        fullName: _fullNameController.text.isNotEmpty 
            ? _fullNameController.text 
            : null,
        password: _passwordController.text.isNotEmpty 
            ? _passwordController.text 
            : null,
        currentTaskStatus: _currentTaskController.text.isNotEmpty 
            ? _currentTaskController.text 
            : null,
        role: _selectedRole,
      );

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Profile berhasil diupdate!')),
      );

      // Navigate back or refresh
      Navigator.pop(context, true);
      
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal update profile: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Edit Profile')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _usernameController,
              decoration: InputDecoration(
                labelText: 'Username',
                border: OutlineInputBorder(),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Username tidak boleh kosong';
                }
                return null;
              },
            ),
            SizedBox(height: 16),
            
            TextFormField(
              controller: _fullNameController,
              decoration: InputDecoration(
                labelText: 'Full Name',
                border: OutlineInputBorder(),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Full name tidak boleh kosong';
                }
                return null;
              },
            ),
            SizedBox(height: 16),
            
            TextFormField(
              controller: _passwordController,
              decoration: InputDecoration(
                labelText: 'Password Baru (Opsional)',
                border: OutlineInputBorder(),
                helperText: 'Kosongkan jika tidak ingin mengubah password',
              ),
              obscureText: true,
              validator: (value) {
                if (value != null && value.isNotEmpty && value.length < 6) {
                  return 'Password minimal 6 karakter';
                }
                return null;
              },
            ),
            SizedBox(height: 16),
            
            TextFormField(
              controller: _currentTaskController,
              decoration: InputDecoration(
                labelText: 'Current Task Status',
                border: OutlineInputBorder(),
              ),
              maxLines: 2,
            ),
            SizedBox(height: 16),
            
            DropdownButtonFormField<String>(
              value: _selectedRole,
              decoration: InputDecoration(
                labelText: 'Role',
                border: OutlineInputBorder(),
              ),
              items: [
                DropdownMenuItem(value: 'user', child: Text('User')),
                DropdownMenuItem(value: 'admin', child: Text('Admin')),
              ],
              onChanged: (value) {
                setState(() => _selectedRole = value);
              },
            ),
            SizedBox(height: 24),
            
            ElevatedButton(
              onPressed: _isLoading ? null : _updateProfile,
              style: ElevatedButton.styleFrom(
                padding: EdgeInsets.symmetric(vertical: 16),
              ),
              child: _isLoading
                  ? CircularProgressIndicator()
                  : Text('Update Profile'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    _usernameController.dispose();
    _fullNameController.dispose();
    _passwordController.dispose();
    _currentTaskController.dispose();
    super.dispose();
  }
}
```

---

## 🔍 Important Notes

### 1. Email Cannot Be Changed
- Email field **TIDAK BISA** diubah
- Ini untuk alasan keamanan dan identifikasi user
- Jika ingin ubah email, harus melalui proses verifikasi terpisah

### 2. Password Hashing
- Password otomatis di-hash menggunakan `Hash::make()`
- Tidak perlu hash password di client side
- Kirim password plain text via HTTPS

### 3. Username Uniqueness
- Username harus unique di seluruh sistem
- Validasi mengecek username lain kecuali username user sendiri
- Error 422 jika username sudah digunakan user lain

### 4. Optional Fields
- Semua field bersifat optional
- Hanya kirim field yang ingin diubah
- Field yang tidak dikirim tetap menggunakan nilai lama

### 5. Relations Loaded Automatically
- Setelah update, response otomatis load semua relasi user:
  - `createdProjects`
  - `projectMemberships.project`
  - `createdCards.board`
  - `comments.card`
  - `timeLogs.card`

---

## 🧪 Testing with Postman/Thunder Client

### Test Case 1: Update Username Only

```
PUT http://localhost:8000/api/v1/profile
Headers:
  Authorization: Bearer YOUR_TOKEN
  Content-Type: application/json
  Accept: application/json

Body:
{
  "username": "new_username_test"
}
```

**Expected:** Success dengan username baru

---

### Test Case 2: Update Password

```
PUT http://localhost:8000/api/v1/profile
Headers:
  Authorization: Bearer YOUR_TOKEN
  Content-Type: application/json
  Accept: application/json

Body:
{
  "password": "newPassword123"
}
```

**Expected:** Success, password ter-hash di database

---

### Test Case 3: Update Multiple Fields

```
PUT http://localhost:8000/api/v1/profile
Headers:
  Authorization: Bearer YOUR_TOKEN
  Content-Type: application/json
  Accept: application/json

Body:
{
  "username": "johndoe2024",
  "full_name": "John Doe Updated",
  "current_task_status": "Working on feature X"
}
```

**Expected:** Success dengan semua field ter-update

---

### Test Case 4: Duplicate Username Error

```
PUT http://localhost:8000/api/v1/profile
Headers:
  Authorization: Bearer YOUR_TOKEN
  Content-Type: application/json
  Accept: application/json

Body:
{
  "username": "existing_username"
}
```

**Expected:** Error 422 dengan message "Username sudah digunakan oleh user lain"

---

### Test Case 5: Password Too Short Error

```
PUT http://localhost:8000/api/v1/profile
Headers:
  Authorization: Bearer YOUR_TOKEN
  Content-Type: application/json
  Accept: application/json

Body:
{
  "password": "12345"
}
```

**Expected:** Error 422 dengan message "Password minimal 6 karakter"

---

## 📚 Related Endpoints

- `GET /api/v1/me` - Get current user with relations
- `POST /api/login` - Login user
- `POST /api/register` - Register new user
- `POST /api/v1/logout` - Logout user

---

## 🤝 Support

Jika ada pertanyaan atau issue, silakan hubungi tim development.
