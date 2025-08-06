# TRLike SMM Android Uygulaması

Bu proje, sosyal medya pazarlama (SMM) hizmetleri sunan bir Android uygulaması ve PHP admin paneli içerir.

## Özellikler

### Android Uygulaması
- **Google ile Giriş**: Kullanıcılar Google hesapları ile giriş yapabilir
- **Kredi Sistemi**: Uygulama içi kredi sistemi
- **Hizmet Satın Alma**: Instagram, TikTok ve diğer platformlar için takipçi, beğeni vb. hizmetler
- **Kupon Sistemi**: İndirim kuponları kullanma
- **Sipariş Takibi**: Kullanıcılar siparişlerini takip edebilir
- **Google Play Billing**: Kredi satın alma için Google Play entegrasyonu

### Admin Paneli
- **Kullanıcı Yönetimi**: Kullanıcıları görüntüleme ve yasaklama
- **Hizmet Yönetimi**: Yeni hizmetler ekleme, düzenleme ve silme
- **Sipariş Yönetimi**: Siparişleri görüntüleme ve durum güncelleme
- **Kupon Yönetimi**: Kupon oluşturma ve yönetimi
- **API Sağlayıcı Yönetimi**: SMM API sağlayıcılarını yönetme
- **İstatistikler**: Dashboard ile genel istatistikler

## Kurulum

### Gereksinimler
- Android Studio
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx web sunucusu

### Android Uygulaması Kurulumu

1. **Projeyi klonlayın**:
```bash
git clone <repository-url>
cd trlike-smm-app
```

2. **Google Services yapılandırması**:
   - Google Cloud Console'da yeni bir proje oluşturun
   - Google Sign-In API'yi etkinleştirin
   - `google-services.json` dosyasını `app/` klasörüne ekleyin
   - Google Play Billing için ürün ID'lerini yapılandırın

3. **API URL'sini güncelleyin**:
   - `app/src/main/java/com/trlike/smmapp/data/api/RetrofitClient.kt` dosyasında `BASE_URL`'yi güncelleyin

4. **Uygulamayı derleyin**:
```bash
./gradlew build
```

### Admin Paneli Kurulumu

1. **Veritabanını oluşturun**:
```sql
CREATE DATABASE trlike_smm;
```

2. **Veritabanı bağlantısını yapılandırın**:
   - `admin/config/database.php` dosyasında veritabanı bilgilerini güncelleyin

3. **Web sunucusuna yükleyin**:
   - `admin/` klasörünü web sunucunuza yükleyin
   - `api/` klasörünü web sunucunuza yükleyin

4. **Admin kullanıcısı oluşturun**:
```sql
INSERT INTO users (id, email, name, is_admin, password) 
VALUES (UNIQID(), 'admin@example.com', 'Admin', 1, '$2y$10$...');
```

## API Endpoints

### Kimlik Doğrulama
- `POST /api/auth` - Google ile giriş

### Kullanıcı
- `GET /api/user` - Kullanıcı profili
- `PUT /api/user` - Kredi güncelleme

### Hizmetler
- `GET /api/services` - Tüm hizmetler
- `GET /api/services?category=followers` - Kategoriye göre hizmetler

### Siparişler
- `GET /api/orders` - Kullanıcı siparişleri
- `GET /api/orders?id=ORDER_ID` - Belirli sipariş
- `POST /api/orders` - Yeni sipariş oluşturma

### Kredi Paketleri
- `GET /api/credit-packages` - Kredi paketleri

### Kuponlar
- `POST /api/coupons?action=validate` - Kupon doğrulama
- `POST /api/coupons?action=apply` - Kupon uygulama

## Güvenlik

### Android Uygulaması
- ProGuard/R8 ile kod karıştırma
- API anahtarlarını güvenli şekilde saklama
- SSL pinning (production'da)

### Admin Paneli
- Session tabanlı kimlik doğrulama
- SQL injection koruması
- XSS koruması
- CSRF koruması

## Üretim Hazırlığı

### Android Uygulaması
1. Release build oluşturun
2. Google Play Console'da uygulama yayınlayın
3. Google Play Billing ürünlerini yapılandırın
4. Firebase Analytics ekleyin

### Admin Paneli
1. HTTPS sertifikası ekleyin
2. Güvenlik duvarı yapılandırın
3. Veritabanı yedekleme sistemi kurun
4. Monitoring ve logging ekleyin

## Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit yapın (`git commit -m 'Add amazing feature'`)
4. Push yapın (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## İletişim

- Email: support@trlike.com
- Website: https://trlike.com

## Sürüm Geçmişi

### v1.0.0
- İlk sürüm
- Temel SMM özellikleri
- Google ile giriş
- Admin paneli
- API sistemi