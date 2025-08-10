# Birbank APK Kurulum Talimatları

## 🔍 Sorun: İmza Uyumsuzluğu

**Hata:** `INSTALL_FAILED_UPDATE_INCOMPATIBLE: Package az.kapitalbank.mbanking signatures do not match`

## 🔧 Çözüm Yöntemleri

### **Yöntem 1: Mevcut Uygulamayı Kaldır**

1. **Ayarlar → Uygulamalar → Birbank**
2. **"Kaldır" butonuna tıkla**
3. **Tüm verileri sil**
4. **`birbank_no_root.apk` dosyasını yükle**

### **Yöntem 2: SAI (Split APK Installer) Kullan**

1. **SAI uygulamasını indir:**
   ```
   https://play.google.com/store/apps/details?id=com.aefyr.sai
   ```

2. **APK'yı yükle:**
   - SAI'yi aç
   - `birbank_no_root.apk` dosyasını seç
   - "Install" butonuna tıkla

### **Yöntem 3: ADB ile Yükleme**

1. **USB Debugging aç:**
   - Ayarlar → Telefon Hakkında → Yapı Numarası (7 kez tıkla)
   - Ayarlar → Geliştirici Seçenekleri → USB Debugging

2. **ADB komutları:**
   ```bash
   adb uninstall az.kapitalbank.mbanking
   adb install birbank_no_root.apk
   ```

### **Yöntem 4: Root ile Yükleme**

1. **Root Explorer ile:**
   - `/data/app/` klasörüne git
   - `az.kapitalbank.mbanking` klasörünü sil
   - APK'yı yükle

## ⚠️ Önemli Notlar

- **Yedek al:** Mevcut uygulamadan veri yedekle
- **Root gerekli:** Bu APK root'lu cihazlarda çalışır
- **Eğitim amaçlı:** Sadece eğitim amaçlı kullanın

## 📱 Cihaz Bilgileri

- **Model:** Redmi Note 9 Pro
- **Android:** 12 (SDK 31)
- **ABI:** arm64-v8a
- **Root:** Gerekli