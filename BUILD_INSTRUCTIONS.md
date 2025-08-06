# SnapTik Pro - APK Oluşturma Talimatları

## Gereksinimler

1. **Android Studio** (en son sürüm)
2. **Java Development Kit (JDK)** 17 veya üzeri
3. **Android SDK** (Android Studio ile birlikte gelir)

## APK Oluşturma Adımları

### 1. Projeyi İndirin
```bash
git clone https://github.com/yourusername/SnapTikPro.git
cd SnapTikPro
```

### 2. Android Studio'da Açın
- Android Studio'yu açın
- "Open an existing project" seçin
- SnapTikPro klasörünü seçin

### 3. API URL'sini Güncelleyin
`app/src/main/java/com/snaptikpro/app/MainActivity.kt` dosyasında:
```kotlin
private const val BASE_URL = "https://your-domain.com/api/"
```

### 4. Debug APK Oluşturun
- Build > Build Bundle(s) / APK(s) > Build APK(s)
- Veya terminal'de: `./gradlew assembleDebug`

### 5. Release APK Oluşturun
- Build > Generate Signed Bundle / APK
- APK seçin
- Keystore oluşturun veya mevcut olanı kullanın
- Release build type seçin
- Build > Build Bundle(s) / APK(s) > Build APK(s)

### 6. APK Dosyasını Bulun
- Debug APK: `app/build/outputs/apk/debug/app-debug.apk`
- Release APK: `app/build/outputs/apk/release/app-release.apk`

## Alternatif: Command Line ile APK Oluşturma

### Debug APK
```bash
./gradlew assembleDebug
```

### Release APK
```bash
./gradlew assembleRelease
```

## Sorun Giderme

### SDK Hatası
Eğer "SDK location not found" hatası alırsanız:
1. Android Studio'da SDK Manager'ı açın
2. Gerekli SDK platformlarını indirin
3. `local.properties` dosyasında SDK yolunu kontrol edin

### Gradle Hatası
Eğer Gradle hatası alırsanız:
```bash
./gradlew clean
./gradlew --refresh-dependencies
```

### Java Sürüm Hatası
Java 17 veya üzeri kullandığınızdan emin olun:
```bash
java -version
```

## APK Test Etme

1. **Emülatörde Test:**
   - Android Studio'da emülatör oluşturun
   - APK'yı emülatöre yükleyin

2. **Gerçek Cihazda Test:**
   - Cihazınızda "Geliştirici Seçenekleri"ni açın
   - "USB Hata Ayıklama"yı etkinleştirin
   - APK'yı cihaza yükleyin

## Yayınlama

### Google Play Store
1. Google Play Console'da hesap oluşturun
2. Uygulama ekleyin
3. Release APK'yı yükleyin
4. Gerekli bilgileri doldurun
5. Yayınlayın

### Alternatif Mağazalar
- APKPure
- APKMirror
- F-Droid

## Güvenlik Notları

- Release APK'yı imzalamayı unutmayın
- ProGuard/R8 ile kod karıştırma kullanın
- API anahtarlarını güvenli şekilde saklayın
- HTTPS kullanın

## Destek

Sorun yaşarsanız:
1. GitHub Issues'da sorun açın
2. Detaylı hata mesajını paylaşın
3. Kullandığınız Android Studio ve Java sürümünü belirtin