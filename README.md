# 🚀 SnapTik Pro - Video Downloader

Modern ve güzel tasarımlı TikTok, Instagram, Facebook ve Twitter video indirme uygulaması.

## ✨ Özellikler

- 📱 **Modern UI/UX** - Material Design 3 ile güzel arayüz
- 🎨 **Dark Theme** - Göz yormayan karanlık tema
- ⚡ **Hızlı İndirme** - Optimize edilmiş indirme sistemi
- 🔄 **Çoklu Platform** - TikTok, Instagram, Facebook, Twitter
- 📁 **Downloads Klasörü** - Videolar otomatik olarak Downloads klasörüne kaydedilir
- 🎯 **Watermark Yok** - Temiz video indirme
- 🌐 **TikWM API Entegrasyonu** - Doğrudan Android uygulamasında

## 📥 İndirme

### 🔥 En Güncel APK (TikWM API Entegrasyonu)
**[SnapTikPro-TikWM-API.apk](https://github.com/kenanbabayevofficial/aze/raw/main/SnapTikPro-TikWM-API.apk)**

### 📋 Önceki Sürümler
- [SnapTikPro-Updated.apk](https://github.com/kenanbabayevofficial/aze/raw/main/SnapTikPro-Updated.apk) - HTTP 404 hatası düzeltildi
- [SnapTikPro-Fixed.apk](https://github.com/kenanbabayevofficial/aze/raw/main/SnapTikPro-Fixed.apk) - Splash screen hatası düzeltildi
- [SnapTikPro.apk](https://github.com/kenanbabayevofficial/aze/raw/main/SnapTikPro.apk) - İlk sürüm

## 🛠️ Teknik Detaylar

### 📱 Platform Desteği
- **Android**: API 26+ (Android 8.0+)
- **Kotlin**: 1.8.0
- **Gradle**: 8.5
- **Android Gradle Plugin**: 8.2.0

### 🔧 Kullanılan Teknolojiler
- **Kotlin** - Modern Android geliştirme
- **Material Design 3** - Güzel UI/UX
- **Retrofit** - HTTP API istekleri
- **Kotlin Coroutines** - Asenkron işlemler
- **ViewBinding** - Güvenli view erişimi
- **Lottie** - Animasyonlar
- **Glide** - Resim yükleme

### 🌐 API Entegrasyonu
- **TikTok**: TikWM API (✅ Doğrudan entegre - API key gerektirmez)
- **Instagram**: SnapInsta API (✅ Çalışıyor)
- **Facebook**: FB Downloader API (✅ Çalışıyor)
- **Twitter**: Twitter API v2 (⚠️ API key gerekli)

## 🎯 Son Güncellemeler

### ✅ TikWM API Doğrudan Entegrasyonu
- **Özellik**: TikWM API artık doğrudan Android uygulamasında
- **Avantaj**: PHP backend'e gerek yok
- **Sonuç**: Daha hızlı ve güvenilir video indirme

### ✅ Tam TikTok Desteği
- **Video İndirme**: Watermark'sız yüksek kalite
- **Video Bilgileri**: Başlık, thumbnail, süre
- **Müzik Bilgileri**: Orijinal ses dosyası
- **İstatistikler**: Beğeni, yorum, paylaşım sayıları

### ✅ HTTP 404 Hatası Düzeltildi
- **Sorun**: API endpoint'leri bulunamıyordu
- **Çözüm**: TikWM API entegrasyonu
- **Sonuç**: Artık videolar başarıyla indiriliyor

### ✅ API Entegrasyonu
- **TikWM API**: TikTok için güvenilir API
- **Fallback Sistemi**: Bir API çalışmazsa diğeri
- **Hata Yönetimi**: Detaylı hata mesajları

### ✅ Splash Screen Düzeltildi
- **Sorun**: `Resources$NotFoundException: Drawable splash_background`
- **Çözüm**: Theme'de background referansı düzeltildi
- **Sonuç**: Uygulama artık açılıyor

## 📋 Kurulum

1. **APK'yı indirin** - Yukarıdaki linklerden
2. **Bilinmeyen kaynaklara izin verin** - Ayarlar > Güvenlik
3. **APK'yı yükleyin** - İndirilen dosyaya tıklayın
4. **İzinleri verin** - Depolama ve internet erişimi
5. **Kullanmaya başlayın** - TikTok video linkini yapıştırın

## 🎨 Ekran Görüntüleri

### Ana Ekran
- Modern ve temiz tasarım
- Platform seçimi (TikTok, Instagram, Facebook, Twitter)
- Link yapıştırma alanı
- İndirme butonu

### İndirme Ekranı
- İlerleme çubuğu
- Video bilgileri (başlık, thumbnail)
- İndirme durumu

### Downloads Klasörü
- İndirilen videolar listesi
- Video önizlemeleri
- Paylaşım seçenekleri

## 🔧 Geliştirme

### Gereksinimler
- Android Studio Arctic Fox veya üzeri
- Android SDK 34
- Java 8 veya üzeri

### Kurulum
```bash
git clone https://github.com/kenanbabayevofficial/aze.git
cd aze
./gradlew build
```

### API Test
```bash
# TikTok API test
curl "https://www.tikwm.com/api/?url=https://vt.tiktok.com/ZSSCUTrC2/"
```

## 📝 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 🤝 Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/AmazingFeature`)
3. Commit yapın (`git commit -m 'Add some AmazingFeature'`)
4. Push yapın (`git push origin feature/AmazingFeature`)
5. Pull Request açın

## 📞 İletişim

- **GitHub**: [@kenanbabayevofficial](https://github.com/kenanbabayevofficial)
- **Proje**: [SnapTik Pro](https://github.com/kenanbabayevofficial/aze)

## 🎉 Teşekkürler

- **Material Design** - Güzel UI/UX için
- **TikWM API** - TikTok video indirme için
- **SnapInsta API** - Instagram video indirme için
- **FB Downloader API** - Facebook video indirme için

---

⭐ **Bu projeyi beğendiyseniz yıldız vermeyi unutmayın!**