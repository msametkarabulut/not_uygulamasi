# NoteFlow

Modern, hızlı ve kullanışlı bir not alma uygulaması. Firebase Firestore ile bulut senkronizasyonu veya yerel JSON depolama desteği sunar.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?logo=php&logoColor=white)
![Firebase](https://img.shields.io/badge/Firebase-Firestore-FFCA28?logo=firebase&logoColor=black)
![License](https://img.shields.io/badge/License-MIT-green)

---

## Özellikler

- **Not Yönetimi** — Oluştur, düzenle, sil. Modal editör ile hızlı düzenleme.
- **Renkli Notlar** — 8 farklı renk seçeneği ile notlarını kategorize et.
- **Klasörler** — Notlarını klasörlere ayırarak düzenli tut.
- **Sürükle & Bırak** — Notları klasörlere sürükleyip bırakarak taşı.
- **Favoriler** — Önemli notlarını tek tıkla favorilere ekle.
- **Arama** — Başlık ve içerik üzerinden anlık arama.
- **Grid & Liste Görünümü** — İki farklı görünüm modu arasında geçiş yap.
- **Gerçek Zamanlı Senkronizasyon** — Birden fazla sekme veya cihazda anlık güncelleme.
- **Responsive Tasarım** — Masaüstü, tablet ve mobil uyumlu arayüz.
- **Admin Paneli** — Dashboard, kullanıcı yönetimi ve istatistikler.
- **Çift Depolama** — Firebase Firestore veya yerel JSON dosyaları (otomatik geçiş).

---

## Ekran Görüntüleri

| Masaüstü | Mobil |
|----------|-------|
| Not grid görünümü, koyu sidebar | Hamburger menü, tek kolon grid |

---

## Teknolojiler

| Katman | Teknoloji |
|--------|-----------|
| Backend | PHP 7.4+ |
| Frontend | Vanilla JavaScript |
| Stil | Custom CSS (CSS Variables, Flexbox, Grid) |
| Veritabanı | Firebase Firestore / Yerel JSON |
| HTTP Client | GuzzleHttp 7.x |
| İkonlar | Inline SVG |
| Sunucu | Apache (XAMPP) |

---

## Kurulum

### Gereksinimler

- PHP 7.4 veya üzeri
- Apache (XAMPP / WAMP / MAMP)
- Composer
- (Opsiyonel) Firebase projesi ve Service Account

### 1. Projeyi klonla

```bash
git clone https://github.com/KULLANICI_ADI/noteflow.git
cd noteflow
```

### 2. Bağımlılıkları yükle

```bash
composer install
```

### 3. Apache'de çalıştır

Proje dosyalarını XAMPP `htdocs` klasörüne yerleştir ve Apache'yi başlat.

```
http://localhost/
```

### 4. (Opsiyonel) Firebase Yapılandırması

Firebase Firestore kullanmak istiyorsan:

1. [Firebase Console](https://console.firebase.google.com) üzerinden bir proje oluştur.
2. Firestore Database'i etkinleştir.
3. **Proje Ayarları > Hizmet hesapları** bölümünden yeni bir özel anahtar oluştur.
4. İndirilen JSON dosyasını proje kök dizinine `config/firebase-service-account.json` olarak kaydet.

Firebase yapılandırılmadığında uygulama otomatik olarak `data/` klasöründeki JSON dosyalarını kullanır.

---

## Demo Hesaplar

| Rol | E-posta | Şifre |
|-----|---------|-------|
| Admin | `admin@test.com` | `admin123` |
| Kullanıcı | `kullanici@test.com` | `123456` |

---

## Proje Yapısı

```
noteflow/
├── admin/                  # Admin paneli sayfaları
│   ├── index.php           # Dashboard
│   ├── settings.php        # Admin ayarları
│   ├── stats.php           # İstatistikler
│   └── users.php           # Kullanıcı yönetimi
├── assets/
│   ├── css/
│   │   └── style.css       # Tüm stiller (responsive dahil)
│   └── js/
│       └── app.js          # Uygulama JS (AJAX, Drag&Drop, Sync)
├── config/
│   ├── config.php          # Ana konfigürasyon
│   └── firebase_config.php # Firebase ayarları
├── data/                   # Yerel JSON depolama
│   ├── folders/
│   └── notes/
├── includes/
│   ├── db.php              # Veritabanı işlemleri
│   ├── firestore_client.php# Firebase REST API client
│   ├── icons.php           # SVG ikon fonksiyonları
│   ├── layout.php          # Ana layout template
│   ├── note_card.php       # Not kartı komponenti
│   ├── note_row.php        # Not satırı komponenti
│   ├── sidebar.php         # Sidebar navigasyonu
│   └── user_nav.php        # Kullanıcı dropdown menüsü
├── api.php                 # AJAX API endpoint
├── composer.json            # PHP bağımlılıkları
├── favorites.php           # Favori notlar sayfası
├── folder.php              # Klasör görünümü
├── login.php               # Giriş sayfası
├── register.php            # Kayıt sayfası
├── notes.php               # Ana not listesi
├── note_edit.php           # Not düzenleme sayfası
├── profile.php             # Kullanıcı profili
├── settings.php            # Kullanıcı ayarları
└── README.md
```

---

## API Endpoint'leri

Tüm API istekleri `api.php` üzerinden POST/GET ile yapılır.

| Action | Method | Açıklama |
|--------|--------|----------|
| `save_note` | POST | Not oluştur veya güncelle |
| `delete_note` | POST | Not sil |
| `toggle_favorite` | POST | Favori durumunu değiştir |
| `move_note` | POST | Notu başka klasöre taşı |
| `get_notes` | GET | Tüm notları ve klasörleri getir |
| `check_version` | GET | Veri hash'i al (senkronizasyon için) |

---

## Gerçek Zamanlı Senkronizasyon

NoteFlow iki katmanlı senkronizasyon kullanır:

1. **BroadcastChannel API** — Aynı tarayıcıdaki sekmeler arası anlık bildirim.
2. **Short Polling** (3 saniye) — Farklı tarayıcı/cihazlar arası veri hash karşılaştırması.

Bir sekmede not eklendiğinde veya düzenlendiğinde, diğer açık sekmeler otomatik güncellenir.

---

## Responsive Tasarım

| Breakpoint | Davranış |
|-----------|----------|
| > 1024px | Tam sidebar, geniş not grid |
| ≤ 1024px | Daraltılmış sidebar |
| ≤ 768px | Sadece ikon sidebar, kompakt arayüz |
| ≤ 640px | Gizli sidebar + hamburger menü, tek kolon, modal sheet |
| ≤ 380px | Ekstra küçük font ve padding ayarları |

---

## Katkıda Bulunma

1. Fork'la
2. Feature branch oluştur (`git checkout -b feature/yeni-ozellik`)
3. Commit'le (`git commit -m 'Yeni özellik eklendi'`)
4. Push'la (`git push origin feature/yeni-ozellik`)
5. Pull Request aç

---

## Lisans

Bu proje [MIT Lisansı](LICENSE) ile lisanslanmıştır.
