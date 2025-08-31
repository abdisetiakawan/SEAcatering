## SEA Catering - COMPFEST 17 Software Engineering Academy Selection Submission

**Nama:** Abdi Setiawan

---

### Repository

* [GitHub Repository](https://github.com/abdisetiakawan/seacatering)

### Deployment

* [https://abdisetiawan.my.id](https://seacatering.abdisetiawan.my.id)

---

## 🚀 Cara Menjalankan Project

1️⃣ **Clone Repository**

```bash
git clone https://github.com/abdisetiakawan/seacatering.git
cd seacatering
```

2️⃣ **Install Dependencies**

```bash
composer install
npm install
```

3️⃣ **Salin file environment**

```bash
cp .env.example .env
```

4️⃣ **Generate key aplikasi**

```bash
php artisan key:generate
```

5️⃣ **Setup database**

* Buat database baru sesuai konfigurasi di `.env`
* Jalankan migrasi dan seeder:

```bash
php artisan migrate:fresh
php artisan db:seed
```

6️⃣ **Jalankan aplikasi**

```bash
php artisan serve
npm run dev
```

---

## 🗄️ Seeder User

Seeder akan otomatis membuat akun **Admin** dan 5 **User Regular** dengan data:

### Admin

```json
{
  "name": "Admin SEA Catering",
  "email": "admin@seacatering.com",
  "phone": "081234567890",
  "role": "admin",
  "password": "admin123"
}
```

### Regular Users

| Name          | Email                                             | Phone        | Password |
| ------------- | ------------------------------------------------- | ------------ | -------- |
| John Doe      | [john@example.com](mailto:john@example.com)       | 081234567891 | password |
| Jane Smith    | [jane@example.com](mailto:jane@example.com)       | 081234567892 | password |
| Bob Wilson    | [bob@example.com](mailto:bob@example.com)         | 081234567893 | password |
| Alice Johnson | [alice@example.com](mailto:alice@example.com)     | 081234567894 | password |
| Charlie Brown | [charlie@example.com](mailto:charlie@example.com) | 081234567895 | password |

---

## 📩 Credentials

### Admin Login

* **Email:** [admin@seacatering.com](mailto:admin@seacatering.com)
* **Password:** admin123

### User Login

* **Email:** [john@example.com](mailto:john@example.com) *(atau user lain)*
* **Password:** password

---

## 📌 Catatan

✅ Aplikasi dibuat untuk seleksi **Software Engineering Academy COMPFEST 17**.

✅ Teknologi yang digunakan:

* Laravel 11
* Inertia.js + Vue 3
* TailwindCSS
* PostgreSQL

✅ Jika membutuhkan detail workflow CI/CD atau penjelasan fitur lebih lanjut, silakan hubungi saya.

---

Terima kasih telah memeriksa hasil pengerjaan ini 🙏.
