# 📝 Fitur Edit Profil & Upload Foto Profil - PasarNgalam

## ✅ Apa yang Sudah Ditambahkan

### 1️⃣ **Database Updates**
- Migrasi baru: `2024_12_07_add_profile_fields_to_users.php`
- Kolom baru di tabel `users`:
  - `profile_picture` - Menyimpan path foto profil user
  - `address` - Menyimpan alamat lengkap
  - `banner` - Menyimpan banner untuk merchant

### 2️⃣ **User Model**
- Update `$fillable` untuk include kolom baru: `profile_picture`, `address`, `banner`

### 3️⃣ **ProfileController** 
- Tambah method `show()` untuk menampilkan halaman profil customer
- Update method `update()` untuk handle upload file:
  - Upload foto profil ke folder `storage/app/public/profile-pictures/`
  - Upload banner ke folder `storage/app/public/banners/`
  - Hapus file lama saat upload file baru
  - Support untuk semua role: user, merchant, driver

### 4️⃣ **Views untuk Setiap Aktor**

#### **Customer Profile** (New) ✨
- **File**: `resources/views/customer/profile.blade.php`
- **Fitur**:
  - Tampil foto profil dengan preview
  - Form edit nama, email, phone, alamat
  - Upload foto profil dengan preview realtime
  - Ganti password
  - Logout
  - Statistik orders

#### **Merchant Dashboard** (Updated)
- **File**: `resources/views/merchant/partials/profile.blade.php`
- **Fitur baru**:
  - Upload foto profil dengan preview realtime
  - Sidebar foto profil (sebelah kiri)
  - Layout lebih bagus dengan grid 3 kolom

#### **Driver Dashboard** (Updated)
- **File**: `resources/views/driver/dashboard.blade.php`
- **Modal profile**:
  - Upload foto profil dengan preview
  - Form yang lebih lengkap
  - Support untuk alamat

### 5️⃣ **Routes**
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});
```

### 6️⃣ **UI Updates**
- Welcome page: Tambah button profil di navbar (icon user)
- Link profil untuk customer di navbar

---

## 🎯 Cara Menggunakan

### **Untuk Customer:**
1. Login sebagai customer (pembeli)
2. Klik icon user (👤) di navbar kanan
3. Buka halaman profil: `/profile`
4. Upload foto profil dengan drag & drop
5. Edit data dan klik "Simpan Perubahan"

### **Untuk Merchant:**
1. Login sebagai merchant
2. Buka menu "Profil" di dashboard
3. Upload foto profil dan banner dengan drag & drop
4. Edit data warung dan klik "Simpan Perubahan"

### **Untuk Driver:**
1. Login sebagai driver
2. Klik nama/profil di bagian atas
3. Upload foto profil di modal yang muncul
4. Edit data dan klik "Simpan Profil"

---

## 📁 File yang Diubah/Dibuat

### **Dibuat:**
- ✅ `database/migrations/2024_12_07_add_profile_fields_to_users.php`
- ✅ `resources/views/customer/profile.blade.php`

### **Diupdate:**
- ✅ `app/Models/User.php`
- ✅ `app/Http/Controllers/ProfileController.php`
- ✅ `resources/views/merchant/partials/profile.blade.php`
- ✅ `resources/views/driver/dashboard.blade.php`
- ✅ `resources/views/welcome.blade.php`
- ✅ `routes/web.php`

---

## 🔒 Validasi File Upload
- **Tipe**: jpeg, png, jpg, gif
- **Max size**: 2MB
- **Folder**: 
  - Profile pictures: `storage/app/public/profile-pictures/`
  - Banners: `storage/app/public/banners/`

---

## 🌟 Features Highlights

✨ **Upload dengan Preview Real-time** - Lihat foto sebelum disimpan  
📱 **Responsive Design** - Bekerja di mobile dan desktop  
🎨 **Drag & Drop** - Upload mudah dengan area khusus  
🔄 **Auto Delete Old Files** - Foto lama otomatis terhapus saat upload baru  
🛡️ **Validasi File** - Hanya terima format gambar yang valid  
🚀 **Semua Aktor Support** - Customer, Merchant, Driver  

---

## 📸 Default Profile Picture
Jika user tidak upload foto, akan menampilkan avatar generator dari UI Avatars:
- Parameter: nama user
- Warna: Hijau (#00E073) untuk background, hitam untuk text
- Size: sesuai kebutuhan

---

## 🔧 Troubleshooting

### Foto tidak tersimpan?
1. Pastikan folder `storage/app/public/` ada
2. Jalankan: `php artisan storage:link`
3. Check permission folder (chmod 755)

### Preview tidak muncul?
- Refresh browser
- Check browser console untuk error

### File terlalu besar?
- Maksimal 2MB, kurangi ukuran foto sebelum upload
