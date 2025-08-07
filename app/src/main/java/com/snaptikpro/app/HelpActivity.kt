package com.snaptikpro.app

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import com.snaptikpro.app.databinding.ActivityHelpBinding

data class HelpItem(
    val title: String,
    val description: String,
    val icon: Int,
    val action: (() -> Unit)? = null
)

class HelpActivity : AppCompatActivity() {

    private lateinit var binding: ActivityHelpBinding
    private lateinit var adapter: HelpAdapter
    private val helpItems = mutableListOf<HelpItem>()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityHelpBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setupUI()
        loadHelpItems()
    }

    private fun setupUI() {
        // Back button
        binding.ivBack.setOnClickListener {
            finish()
        }

        // Setup RecyclerView
        adapter = HelpAdapter(helpItems) { helpItem ->
            helpItem.action?.invoke()
        }

        binding.rvHelp.layoutManager = LinearLayoutManager(this)
        binding.rvHelp.adapter = adapter
    }

    private fun loadHelpItems() {
        helpItems.clear()

        // Quick Start Guide
        helpItems.add(
            HelpItem(
                title = "🚀 Hızlı Başlangıç Rehberi",
                description = "Uygulamayı nasıl kullanacağınızı öğrenin",
                icon = R.drawable.ic_help
            ) {
                showQuickStartGuide()
            }
        )

        // How to Download Videos
        helpItems.add(
            HelpItem(
                title = "📥 Video İndirme Nasıl Yapılır?",
                description = "Adım adım video indirme talimatları",
                icon = R.drawable.ic_download
            ) {
                showDownloadTutorial()
            }
        )

        // Supported Platforms
        helpItems.add(
            HelpItem(
                title = "📱 Desteklenen Platformlar",
                description = "Hangi platformlardan video indirebilirsiniz",
                icon = R.drawable.ic_help
            ) {
                showSupportedPlatforms()
            }
        )

        // Auto Download Feature
        helpItems.add(
            HelpItem(
                title = "⚡ Otomatik İndirme Özelliği",
                description = "Otomatik link algılama nasıl çalışır",
                icon = R.drawable.ic_help
            ) {
                showAutoDownloadGuide()
            }
        )

        // Video Player Guide
        helpItems.add(
            HelpItem(
                title = "▶️ Video Oynatıcı Kullanımı",
                description = "İndirilen videoları nasıl oynatırsınız",
                icon = R.drawable.ic_play
            ) {
                showVideoPlayerGuide()
            }
        )

        // Gallery Integration
        helpItems.add(
            HelpItem(
                title = "🖼️ Galeri Entegrasyonu",
                description = "Videoların galeride nasıl görüneceği",
                icon = R.drawable.ic_help
            ) {
                showGalleryIntegration()
            }
        )

        // Troubleshooting
        helpItems.add(
            HelpItem(
                title = "🔧 Sorun Giderme",
                description = "Yaygın sorunlar ve çözümleri",
                icon = R.drawable.ic_help
            ) {
                showTroubleshooting()
            }
        )

        // FAQ
        helpItems.add(
            HelpItem(
                title = "❓ Sık Sorulan Sorular",
                description = "En çok sorulan sorular ve cevapları",
                icon = R.drawable.ic_help
            ) {
                showFAQ()
            }
        )

        // Privacy & Security
        helpItems.add(
            HelpItem(
                title = "🔒 Gizlilik ve Güvenlik",
                description = "Verileriniz nasıl korunur",
                icon = R.drawable.ic_help
            ) {
                showPrivacySecurity()
            }
        )

        // Contact Support
        helpItems.add(
            HelpItem(
                title = "📞 Destek ile İletişim",
                description = "Sorunlarınız için bize ulaşın",
                icon = R.drawable.ic_help
            ) {
                contactSupport()
            }
        )

        // Rate App
        helpItems.add(
            HelpItem(
                title = "⭐ Uygulamayı Değerlendirin",
                description = "Deneyiminizi paylaşın",
                icon = R.drawable.ic_help
            ) {
                rateApp()
            }
        )

        adapter.notifyDataSetChanged()
    }

    private fun showQuickStartGuide() {
        AlertDialog.Builder(this)
            .setTitle("🚀 Hızlı Başlangıç Rehberi")
            .setMessage("""
                Video Downloader Pro'yu kullanmaya başlamak için:
                
                1️⃣ Video linkini kopyalayın
                2️⃣ Uygulamayı açın
                3️⃣ Link otomatik olarak yapıştırılacak
                4️⃣ "Yüklə" düğmesine basın
                5️⃣ Video indirilecek ve galeriye eklenecek
                6️⃣ "Yükləmələr" bölümünden videolarınızı görüntüleyin
                
                🎉 Bu kadar! Artık videolarınızı kolayca indirebilirsiniz.
            """.trimIndent())
            .setPositiveButton("Anladım", null)
            .show()
    }

    private fun showDownloadTutorial() {
        AlertDialog.Builder(this)
            .setTitle("📥 Video İndirme Nasıl Yapılır?")
            .setMessage("""
                Video indirme adımları:
                
                📋 Manuel İndirme:
                1. Video linkini kopyalayın
                2. Uygulamada link alanına yapıştırın
                3. "Yüklə" düğmesine basın
                4. İndirme tamamlanana kadar bekleyin
                
                ⚡ Otomatik İndirme:
                1. Video linkini kopyalayın
                2. Uygulamayı açın
                3. Link otomatik algılanacak
                4. İndirme otomatik başlayacak
                
                📱 İndirilen videolar:
                • DCIM/SnapTikPro klasörüne kaydedilir
                • Galeri uygulamasında görünür
                • Uygulama içi oynatıcıda açılabilir
            """.trimIndent())
            .setPositiveButton("Anladım", null)
            .show()
    }

    private fun showSupportedPlatforms() {
        AlertDialog.Builder(this)
            .setTitle("📱 Desteklenen Platformlar")
            .setMessage("""
                Şu anda desteklenen platformlar:
                
                ✅ TikTok
                • Tüm TikTok video linkleri
                • vm.tiktok.com linkleri
                • vt.tiktok.com linkleri
                
                🔄 Yakında Eklenecek:
                • Instagram
                • Facebook
                • Twitter
                • YouTube
                
                📝 Not: Sadece video linkleri desteklenir.
                Canlı yayınlar ve özel içerikler indirilemez.
            """.trimIndent())
            .setPositiveButton("Anladım", null)
            .show()
    }

    private fun showAutoDownloadGuide() {
        AlertDialog.Builder(this)
            .setTitle("⚡ Otomatik İndirme Özelliği")
            .setMessage("""
                Otomatik indirme nasıl çalışır:
                
                🔍 Otomatik Algılama:
                • Video linkini kopyalayın
                • Uygulamayı açın veya ön plana getirin
                • Link otomatik olarak algılanır
                • İndirme otomatik başlar
                
                ⚙️ Ayarlar:
                • Ayarlar > Otomatik İndirme
                • Bu özelliği açıp kapatabilirsiniz
                
                🔄 Tekrar İndirme:
                • Aynı link tekrar indirilmez
                • Uyarı mesajı gösterilir
                • "Yine de İndir" seçeneği mevcuttur
                
                💡 İpucu: Bu özellik sayesinde
                tek tıkla video indirebilirsiniz!
            """.trimIndent())
            .setPositiveButton("Anladım", null)
            .show()
    }

    private fun showVideoPlayerGuide() {
        AlertDialog.Builder(this)
            .setTitle("▶️ Video Oynatıcı Kullanımı")
            .setMessage("""
                Video oynatıcı özellikleri:
                
                🎬 Oynatma:
                • İndirilen videoları oynatın
                • Tam ekran desteği
                • İleri/geri sarma
                • Ses kontrolü
                
                📤 Paylaşım:
                • Videoları diğer uygulamalarla paylaşın
                • WhatsApp, Telegram, vb.
                • E-posta ile gönderin
                
                🗑️ Silme:
                • Videoları doğrudan oynatıcıdan silin
                • Onay mesajı gösterilir
                • Galeri ve uygulama listesinden kaldırılır
                
                📱 Kontroller:
                • Dokunmatik kontroller
                • Otomatik gizlenme
                • Çift dokunma ile tam ekran
            """.trimIndent())
            .setPositiveButton("Anladım", null)
            .show()
    }

    private fun showGalleryIntegration() {
        AlertDialog.Builder(this)
            .setTitle("🖼️ Galeri Entegrasyonu")
            .setMessage("""
                Galeri entegrasyonu:
                
                📁 Dosya Konumu:
                • DCIM/SnapTikPro klasörü
                • Android'in standart medya klasörü
                • Tüm galeri uygulamalarında görünür
                
                🔄 Otomatik Tarama:
                • İndirme tamamlandığında otomatik tarama
                • Galeri uygulamasında anında görünür
                • Google Photos'ta otomatik yedeklenir
                
                📱 Desteklenen Uygulamalar:
                • Google Photos
                • Samsung Gallery
                • Xiaomi Gallery
                • Huawei Gallery
                • Tüm galeri uygulamaları
                
                ⚠️ Sorun Yaşarsanız:
                • Galeri uygulamasını yenileyin
                • Telefonu yeniden başlatın
                • Ayarlar > Uygulamalar > Galeri > Depolama > Temizle
            """.trimIndent())
            .setPositiveButton("Anladım", null)
            .show()
    }

    private fun showTroubleshooting() {
        AlertDialog.Builder(this)
            .setTitle("🔧 Sorun Giderme")
            .setMessage("""
                Yaygın sorunlar ve çözümleri:
                
                ❌ Video İndirilmiyor:
                • İnternet bağlantınızı kontrol edin
                • Linkin doğru olduğundan emin olun
                • Uygulamayı yeniden başlatın
                
                📱 Galeri'de Görünmüyor:
                • Galeri uygulamasını yenileyin
                • Telefonu yeniden başlatın
                • DCIM/SnapTikPro klasörünü kontrol edin
                
                ⏯️ Video Oynatılmıyor:
                • Video dosyasının tamamlandığından emin olun
                • Farklı bir video oynatıcı deneyin
                • Dosyayı yeniden indirin
                
                🔄 Otomatik İndirme Çalışmıyor:
                • Ayarlar > Otomatik İndirme açık mı?
                • Linkin doğru formatta olduğundan emin olun
                • Uygulamayı yeniden başlatın
                
                📞 Hala Sorun Varsa:
                • Destek ile iletişime geçin
                • Hata mesajını paylaşın
            """.trimIndent())
            .setPositiveButton("Anladım", null)
            .show()
    }

    private fun showFAQ() {
        AlertDialog.Builder(this)
            .setTitle("❓ Sık Sorulan Sorular")
            .setMessage("""
                Sık sorulan sorular:
                
                Q: Hangi platformlardan video indirebilirim?
                A: Şu anda TikTok destekleniyor. Diğer platformlar yakında eklenecek.
                
                Q: Videolar nereye kaydediliyor?
                A: DCIM/SnapTikPro klasörüne kaydedilir ve galeri'de görünür.
                
                Q: Otomatik indirme nasıl çalışır?
                A: Video linkini kopyalayıp uygulamayı açın, otomatik algılanır.
                
                Q: Aynı videoyu tekrar indirebilir miyim?
                A: Evet, "Yine de İndir" seçeneği ile tekrar indirebilirsiniz.
                
                Q: Video oynatıcıda sorun yaşıyorum?
                A: Video dosyasının tamamlandığından emin olun.
                
                Q: Galeri'de videolar görünmüyor?
                A: Galeri uygulamasını yenileyin veya telefonu yeniden başlatın.
                
                Q: Uygulama ücretsiz mi?
                A: Evet, tamamen ücretsizdir ve reklam içermez.
            """.trimIndent())
            .setPositiveButton("Anladım", null)
            .show()
    }

    private fun showPrivacySecurity() {
        AlertDialog.Builder(this)
            .setTitle("🔒 Gizlilik ve Güvenlik")
            .setMessage("""
                Gizlilik ve güvenlik bilgileri:
                
                🔐 Veri Güvenliği:
                • Videolar sadece telefonunuza kaydedilir
                • Sunucularımıza hiçbir veri gönderilmez
                • Kişisel bilgileriniz toplanmaz
                
                📱 İzinler:
                • Depolama: Videoları kaydetmek için
                • İnternet: Video indirmek için
                • Ağ Durumu: Bağlantı kontrolü için
                
                🚫 Toplanmayan Veriler:
                • Kişisel bilgiler
                • İndirilen videolar
                • Kullanım geçmişi
                • Konum bilgileri
                
                ✅ Güvenli Özellikler:
                • Şifreli bağlantılar
                • Güvenli dosya erişimi
                • Gizlilik odaklı tasarım
                
                📄 Gizlilik Politikası:
                • Detaylı bilgi için gizlilik politikamızı okuyun
                • Ayarlar > Gizlilik Politikası
            """.trimIndent())
            .setPositiveButton("Anladım", null)
            .show()
    }

    private fun contactSupport() {
        val options = arrayOf("E-posta Gönder", "Telegram Grubu", "WhatsApp Destek", "İptal")
        
        AlertDialog.Builder(this)
            .setTitle("📞 Destek ile İletişim")
            .setItems(options) { _, which ->
                when (which) {
                    0 -> sendEmail()
                    1 -> openTelegram()
                    2 -> openWhatsApp()
                }
            }
            .show()
    }

    private fun sendEmail() {
        try {
            val intent = Intent(Intent.ACTION_SENDTO).apply {
                data = Uri.parse("mailto:support@snaptikpro.com")
                putExtra(Intent.EXTRA_SUBJECT, "Video Downloader Pro - Destek")
                putExtra(Intent.EXTRA_TEXT, """
                    Merhaba,
                    
                    Video Downloader Pro uygulaması ile ilgili sorun yaşıyorum.
                    
                    Telefon Modeli: ${android.os.Build.MODEL}
                    Android Sürümü: ${android.os.Build.VERSION.RELEASE}
                    Uygulama Sürümü: 1.0
                    
                    Sorun Açıklaması:
                    
                    
                    Teşekkürler.
                """.trimIndent())
            }
            startActivity(Intent.createChooser(intent, "E-posta uygulaması seçin"))
        } catch (e: Exception) {
            Toast.makeText(this, "E-posta uygulaması bulunamadı", Toast.LENGTH_SHORT).show()
        }
    }

    private fun openTelegram() {
        try {
            val intent = Intent(Intent.ACTION_VIEW, Uri.parse("https://t.me/snaptikpro_support"))
            startActivity(intent)
        } catch (e: Exception) {
            Toast.makeText(this, "Telegram açılamadı", Toast.LENGTH_SHORT).show()
        }
    }

    private fun openWhatsApp() {
        try {
            val intent = Intent(Intent.ACTION_VIEW, Uri.parse("https://wa.me/905555555555"))
            startActivity(intent)
        } catch (e: Exception) {
            Toast.makeText(this, "WhatsApp açılamadı", Toast.LENGTH_SHORT).show()
        }
    }

    private fun rateApp() {
        try {
            val intent = Intent(Intent.ACTION_VIEW).apply {
                data = Uri.parse("market://details?id=${packageName}")
            }
            startActivity(intent)
        } catch (e: Exception) {
            // If Play Store is not available, open in browser
            val intent = Intent(Intent.ACTION_VIEW).apply {
                data = Uri.parse("https://play.google.com/store/apps/details?id=${packageName}")
            }
            startActivity(intent)
        }
    }
}