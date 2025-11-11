# Test Time Tracking API dengan Postman/Thunder Client

## Setup
1. Login terlebih dahulu untuk mendapatkan token
2. Simpan token di environment variable atau header

## 1. Login untuk mendapatkan token

```http
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

Response:
```json
{
  "token": "1|xxxxxxxxxxxxx",
  "user": {...}
}
```

## 2. Test Get All Time Logs

```http
GET http://localhost:8000/api/v1/time-logs
Authorization: Bearer YOUR_TOKEN_HERE
```

## 3. Test Get Ongoing Timer

```http
GET http://localhost:8000/api/v1/time-logs/ongoing
Authorization: Bearer YOUR_TOKEN_HERE
```

## 4. Test Start Tracking

```http
POST http://localhost:8000/api/v1/time-logs/start
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "card_id": 1,
  "description": "Testing time tracking API"
}
```

## 5. Test Stop Tracking (ganti {id} dengan ID dari response start)

```http
POST http://localhost:8000/api/v1/time-logs/{id}/stop
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "description": "Completed testing"
}
```

## 6. Test Get Total Time by Card

```http
GET http://localhost:8000/api/v1/time-logs/card/1/total
Authorization: Bearer YOUR_TOKEN_HERE
```
