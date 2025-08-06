# SnapTik Pro - Video Downloader App

Modern ve güzel tasarımlı TikTok, Instagram, Facebook ve Twitter video indirme Android uygulaması.

## Özellikler

- 🎨 **Modern Dark Theme**: Koyu tema ile göz yormayan arayüz
- 📱 **Platform Desteği**: TikTok, Instagram, Facebook, Twitter
- 🔗 **Kolay Kullanım**: Link yapıştır ve indir
- 📁 **İndirilenler Klasörü**: Tüm indirilen videoları görüntüle
- ⚡ **Hızlı İndirme**: Progress bar ile indirme durumu
- 🎯 **Splash Screen**: Güzel açılış animasyonu
- 🔒 **Güvenli**: Filigransız video indirme

## Teknolojiler

### Android (Kotlin)
- **Kotlin**: Modern Android geliştirme
- **Retrofit**: API çağrıları
- **Coroutines**: Asenkron işlemler
- **ViewBinding**: Güvenli view erişimi
- **Material Design**: Modern UI bileşenleri
- **Lottie**: Animasyonlar

### Backend (PHP)
- **PHP**: API sunucusu
- **cURL**: HTTP istekleri
- **JSON**: Veri formatı

## 📥 Kurulum

### 🚀 Android Uygulaması - Direkt İndirme
**Hazır APK dosyası direkt indirme için mevcut:**

🔗 **[SnapTikPro.apk İndir](https://github.com/kenanbabayevofficial/aze/raw/main/SnapTikPro.apk)**

⚠️ **Splash Screen Hatası Düzeltildi!**
🔗 **[SnapTikPro-Fixed.apk İndir](https://github.com/kenanbabayevofficial/aze/raw/main/SnapTikPro-Fixed.apk)** *(Önerilen)*

### 📱 Android Uygulaması - Kaynak Koddan

1. **Projeyi klonlayın:**
```bash
git clone https://github.com/yourusername/SnapTikPro.git
cd SnapTikPro
```

2. **Android Studio'da açın:**
- Android Studio'yu açın
- "Open an existing project" seçin
- SnapTikPro klasörünü seçin

3. **API URL'sini güncelleyin:**
`app/src/main/java/com/snaptikpro/app/MainActivity.kt` dosyasında:
```kotlin
private const val BASE_URL = "https://your-domain.com/api/"
```

4. **Uygulamayı derleyin:**
- Build > Make Project
- Run > Run 'app'

### PHP API

1. **API dosyalarını sunucuya yükleyin:**
```bash
# api/ klasörünü web sunucunuza yükleyin
```

2. **Sunucu gereksinimleri:**
- PHP 7.4+
- cURL extension
- mod_rewrite (Apache)

3. **API URL'sini test edin:**
```
https://your-domain.com/api/?action=download&url=VIDEO_URL&platform=tiktok
```

## Kullanım

1. **Uygulamayı açın**
2. **Platform seçin** (TikTok, Instagram, Facebook, Twitter)
3. **Video linkini yapıştırın** veya "Paste" butonuna basın
4. **Download butonuna basın**
5. **İndirilenler klasöründen videoları görüntüleyin**

## API Endpoints

### Video İndirme
```
GET /api/?action=download&url={VIDEO_URL}&platform={PLATFORM}
```

**Parametreler:**
- `url`: Video linki
- `platform`: Platform (tiktok, instagram, facebook, twitter)

**Örnek:**
```
GET /api/?action=download&url=https://www.tiktok.com/@user/video/123456789&platform=tiktok
```

**Yanıt:**
```json
{
  "success": true,
  "message": "Video found",
  "downloadUrl": "https://example.com/video.mp4",
  "title": "Video Title",
  "thumbnail": "https://example.com/thumb.jpg"
}
```

## Dosya Yapısı

```
SnapTikPro/
├── app/
│   └── src/main/
│       ├── java/com/snaptikpro/app/
│       │   ├── MainActivity.kt
│       │   ├── SplashActivity.kt
│       │   ├── DownloadsActivity.kt
│       │   ├── DownloadsAdapter.kt
│       │   ├── network/
│       │   │   └── ApiService.kt
│       │   └── utils/
│       │       └── DownloadManager.kt
│       └── res/
│           ├── layout/
│           ├── drawable/
│           ├── values/
│           └── raw/
├── api/
│   ├── index.php
│   ├── VideoDownloader.php
│   └── .htaccess
└── README.md
```

## Özelleştirme

### Renk Teması
`app/src/main/res/values/colors.xml` dosyasında renkleri değiştirin:

```xml
<color name="accent">#00FF88</color>
<color name="background">#121212</color>
```

### API Entegrasyonu
`api/VideoDownloader.php` dosyasında gerçek API entegrasyonu ekleyin:

```php
private function getTikTokVideoInfo($videoId) {
    // Gerçek TikTok API entegrasyonu
    $apiUrl = "https://api.tiktok.com/video/{$videoId}";
    $response = $this->makeRequest($apiUrl);
    return json_decode($response, true);
}
```

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit yapın (`git commit -m 'Add amazing feature'`)
4. Push yapın (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

## İletişim

- **Email**: your-email@example.com
- **GitHub**: https://github.com/yourusername

## Sürüm Geçmişi

### v1.0.0
- İlk sürüm
- TikTok, Instagram, Facebook, Twitter desteği
- Modern dark theme
- İndirilenler klasörü
- Splash screen

---

**Not**: Bu uygulama eğitim amaçlıdır. Telif hakkı korumalı içeriklerin indirilmesi yasal olmayabilir. Kullanıcılar kendi sorumluluklarında kullanmalıdır.