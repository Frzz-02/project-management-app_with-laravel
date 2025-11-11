# OVERRIDE DELETE FUNCTION - Paste di Console

**INSTRUKSI:**
1. Buka browser (http://localhost:8000)
2. Login dan navigate ke board
3. Tekan F12 (buka Console)
4. **PASTE CODE INI DI CONSOLE** (sebelum delete card):

```javascript
// OVERRIDE deleteCard function
window.deleteCardOverride = async function(cardId) {
    console.log('🔧 USING OVERRIDE FUNCTION v3.0');
    
    if (!cardId) {
        alert('Error: Card ID tidak ditemukan');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken || !csrfToken.content) {
        alert('Error: CSRF token tidak ditemukan');
        return;
    }

    if (!confirm('Apakah Anda yakin ingin menghapus card ini?')) {
        return;
    }

    try {
        console.log('🗑️ Deleting card:', cardId);
        console.log('🔑 CSRF Token:', csrfToken.content.substring(0, 20) + '...');

        // Use XMLHttpRequest for better header control
        const xhr = new XMLHttpRequest();
        xhr.open('DELETE', `/cards/${cardId}`, true);
        
        // Set headers explicitly
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken.content);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.withCredentials = true;  // Include cookies
        
        xhr.onload = function() {
            console.log('📡 Response status:', xhr.status);
            console.log('📡 Response headers:', xhr.getAllResponseHeaders());
            console.log('📄 Raw response:', xhr.responseText);
            
            if (xhr.status === 200) {
                try {
                    const result = JSON.parse(xhr.responseText);
                    console.log('✅ Parsed result:', result);
                    console.log('✅ Card deleted successfully!');
                    
                    // Close modal
                    if (window.Alpine && Alpine.store('modal')) {
                        Alpine.store('modal').close();
                    }
                    
                    alert('Card berhasil dihapus!');
                    
                    // Reload page
                    console.log('🔄 Reloading page...');
                    window.location.reload();
                } catch (e) {
                    console.error('❌ JSON parse error:', e);
                    console.error('Response was:', xhr.responseText);
                    alert('Delete berhasil tapi parsing error: ' + e.message);
                }
            } else {
                console.error('❌ Delete failed with status:', xhr.status);
                console.error('Response:', xhr.responseText);
                alert('Gagal menghapus card (Status: ' + xhr.status + ')');
            }
        };
        
        xhr.onerror = function() {
            console.error('❌ Network error');
            alert('Terjadi kesalahan jaringan');
        };
        
        console.log('📤 Sending DELETE request...');
        xhr.send();
        
    } catch (error) {
        console.error('❌ Catch error:', error);
        alert('Error: ' + error.message);
    }
};

console.log('✅ Override function registered! Now click delete button.');
```

5. **Setelah paste, test delete card**
6. **Screenshot atau copy semua console output**

---

## Alternative: Manual Test via Console

Atau test langsung delete via console:

```javascript
// Get card ID from selected card
const cardId = 19;  // Ganti dengan ID card yang mau dihapus

// Call override function
window.deleteCardOverride(cardId);
```

---

## Expected Output:

```
🔧 USING OVERRIDE FUNCTION v3.0
🗑️ Deleting card: 19
🔑 CSRF Token: AbCdEf1234567890...
📤 Sending DELETE request...
📡 Response status: 200
📄 Raw response: {"success":true,"message":"Card 'xxx' deleted successfully!","board_id":5}
✅ Parsed result: {success: true, message: "...", board_id: 5}
✅ Card deleted successfully!
🔄 Reloading page...
```

---

## If This Works:

Berarti **100% browser cache issue**. 

**Permanent Fix:**
1. Add version to script tag:
```blade
<script src="{{ asset('js/app.js') }}?v={{ time() }}"></script>
```

2. Or add to layout head:
```blade
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
```

---

**PASTE CODE DI ATAS KE CONSOLE DAN TEST!** 🚀

