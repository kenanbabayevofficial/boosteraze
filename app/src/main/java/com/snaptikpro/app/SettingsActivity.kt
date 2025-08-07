package com.snaptikpro.app

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import com.snaptikpro.app.databinding.ActivitySettingsBinding

class SettingsActivity : AppCompatActivity() {
    
    private lateinit var binding: ActivitySettingsBinding
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivitySettingsBinding.inflate(layoutInflater)
        setContentView(binding.root)
        
        setupUI()
    }
    
    private fun setupUI() {
        // Back button
        binding.ivBack.setOnClickListener {
            finish()
        }
        
        // Auto download setting
        binding.switchAutoDownload.setOnCheckedChangeListener { _, isChecked ->
            // Save auto download preference
            getSharedPreferences("settings", MODE_PRIVATE)
                .edit()
                .putBoolean("auto_download", isChecked)
                .apply()
            
            Toast.makeText(this, 
                if (isChecked) "Otomatik video indirme açıldı" else "Otomatik video indirme kapatıldı", 
                Toast.LENGTH_SHORT).show()
        }
        
        // Load saved preference
        val autoDownload = getSharedPreferences("settings", MODE_PRIVATE)
            .getBoolean("auto_download", true)
        binding.switchAutoDownload.isChecked = autoDownload
        
        // Download quality setting
        binding.rgQuality.setOnCheckedChangeListener { _, checkedId ->
            val quality = when (checkedId) {
                R.id.rbHD -> "HD"
                R.id.rbSD -> "SD"
                else -> "HD"
            }
            
            getSharedPreferences("settings", MODE_PRIVATE)
                .edit()
                .putString("download_quality", quality)
                .apply()
            
            Toast.makeText(this, "Video indirme kalitesi: $quality", Toast.LENGTH_SHORT).show()
        }
        
        // Load saved quality preference
        val savedQuality = getSharedPreferences("settings", MODE_PRIVATE)
            .getString("download_quality", "HD") ?: "HD"
        
        when (savedQuality) {
            "HD" -> binding.rbHD.isChecked = true
            "SD" -> binding.rbSD.isChecked = true
        }
        
        // About section
        binding.tvAbout.setOnClickListener {
            showAboutDialog()
        }
        
        // Rate app
        binding.tvRateApp.setOnClickListener {
            rateApp()
        }
        
        // Share app
        binding.tvShareApp.setOnClickListener {
            shareApp()
        }
        
        // Privacy policy
        binding.tvPrivacyPolicy.setOnClickListener {
            openPrivacyPolicy()
        }
        
        // Terms of service
        binding.tvTermsOfService.setOnClickListener {
            openTermsOfService()
        }
        
        // Clear download history
        binding.tvClearHistory.setOnClickListener {
            showClearHistoryDialog()
        }
        
        // App version
        binding.tvVersion.text = "Sürüm 1.0"
    }
    
    private fun showAboutDialog() {
        AlertDialog.Builder(this)
            .setTitle("Video Downloader Pro Hakkında")
            .setMessage("Video Downloader Pro, video platformlarından videoları kolayca indirmenizi sağlayan ücretsiz bir uygulamadır.\n\n" +
                    "Özellikler:\n" +
                    "• Video indirme\n" +
                    "• Otomatik link algılama\n" +
                    "• Uygulama içi video oynatıcı\n" +
                    "• Çoklu dil desteği\n" +
                    "• Paylaşım özelliği\n\n" +
                    "Geliştirici: Video Downloader Pro Ekibi")
            .setPositiveButton("Tamam", null)
            .show()
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
    
    private fun shareApp() {
        val shareText = "Video Downloader Pro - Videoları kolayca indirin!\n\n" +
                "https://play.google.com/store/apps/details?id=${packageName}"
        
        val intent = Intent(Intent.ACTION_SEND).apply {
            type = "text/plain"
            putExtra(Intent.EXTRA_TEXT, shareText)
        }
        startActivity(Intent.createChooser(intent, "Uygulamayı paylaş"))
    }
    
    private fun openPrivacyPolicy() {
        val intent = Intent(Intent.ACTION_VIEW).apply {
            data = Uri.parse("https://snaptikpro.com/privacy")
        }
        startActivity(intent)
    }
    
    private fun openTermsOfService() {
        val intent = Intent(Intent.ACTION_VIEW).apply {
            data = Uri.parse("https://snaptikpro.com/terms")
        }
        startActivity(intent)
    }
    
    private fun showClearHistoryDialog() {
        AlertDialog.Builder(this)
            .setTitle("İndirme Geçmişini Temizle")
            .setMessage("Tüm indirme geçmişini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.")
            .setPositiveButton("Temizle") { _, _ ->
                clearDownloadHistory()
            }
            .setNegativeButton("İptal", null)
            .show()
    }
    
    private fun clearDownloadHistory() {
        getSharedPreferences("download_history", MODE_PRIVATE)
            .edit()
            .clear()
            .apply()
        
        Toast.makeText(this, "İndirme geçmişi temizlendi", Toast.LENGTH_SHORT).show()
    }
}