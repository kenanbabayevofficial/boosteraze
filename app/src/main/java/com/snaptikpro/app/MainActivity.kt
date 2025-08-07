package com.snaptikpro.app

import android.Manifest
import android.content.ClipData
import android.content.ClipboardManager
import android.content.Context
import android.content.Intent
import android.net.Uri
import android.content.pm.PackageManager
import android.os.Bundle
import android.os.Environment
import android.view.LayoutInflater
import android.view.View
import android.widget.Button
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.app.ActivityCompat
import androidx.core.content.ContextCompat
import androidx.core.content.FileProvider
import androidx.lifecycle.lifecycleScope
import com.snaptikpro.app.databinding.ActivityMainBinding
import com.snaptikpro.app.network.ApiService
import com.snaptikpro.app.network.TikWMResponse
import com.snaptikpro.app.utils.DownloadManager
import kotlinx.coroutines.launch
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.io.File
import java.text.DecimalFormat
import java.text.SimpleDateFormat
import java.util.*

data class DownloadRecord(
    val title: String,
    val path: String,
    val size: Long,
    val date: String
)

class MainActivity : AppCompatActivity() {
    
    private lateinit var binding: ActivityMainBinding
    private lateinit var apiService: ApiService
    private lateinit var downloadManager: DownloadManager
    private var selectedPlatform = "tiktok"
    
    companion object {
        private const val PERMISSION_REQUEST_CODE = 100
        private const val BASE_URL = "https://www.tikwm.com/" // TikWM API URL
        private const val PREFS_NAME = "clipboard_prefs"
        private const val KEY_LAST_LINK = "last_processed_link"
    }
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)
        
        setupApiService()
        setupDownloadManager()
        setupUI()
        checkPermissions()
        
                       // Check clipboard for video link on app start with delay
               binding.root.postDelayed({
                   checkClipboardForVideoLink()
               }, 1000) // 1 second delay to ensure app is fully loaded
    }
    
    private fun setupApiService() {
        val loggingInterceptor = HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BODY
        }
        
        val client = OkHttpClient.Builder()
            .addInterceptor(loggingInterceptor)
            .build()
        
        val retrofit = Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(client)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
        
        apiService = retrofit.create(ApiService::class.java)
    }
    
    private fun setupDownloadManager() {
        downloadManager = DownloadManager(this)
    }
    
    private fun setupUI() {
        // Platform tabs - Only TikTok supported
        binding.tvTikTok.setOnClickListener { selectPlatform("tiktok", binding.tvTikTok) }
        
        // Hide other platforms
        binding.tvInstagram.visibility = View.GONE
        binding.tvFacebook.visibility = View.GONE
        binding.tvTwitter.visibility = View.GONE
        
                       // Buttons
               binding.btnDownload.setOnClickListener { downloadVideo() }
        
        // Header buttons
        binding.ivDownloads.setOnClickListener { openDownloads() }
        binding.ivSettings.setOnClickListener { openSettings() }
        binding.ivHelp.setOnClickListener { openHelp() }
        
        // Set TikTok as default selected
        selectPlatform("tiktok", binding.tvTikTok)
    }
    
    private fun selectPlatform(platform: String, selectedView: View) {
        selectedPlatform = platform
        
        // Reset TikTok tab
        binding.tvTikTok.setBackgroundResource(0)
        binding.tvTikTok.setTextColor(ContextCompat.getColor(this, R.color.text_secondary))
        binding.tvTikTok.isSelected = false
        
        // Set selected tab
        selectedView.setBackgroundResource(R.drawable.tab_background)
        (selectedView as TextView).setTextColor(ContextCompat.getColor(this, R.color.text_primary))
        selectedView.isSelected = true
    }
    
    
    
               private fun downloadVideo() {
               val link = binding.etLink.text.toString().trim()

               if (link.isEmpty()) {
                   Toast.makeText(this, getString(R.string.enter_link), Toast.LENGTH_SHORT).show()
                   return
               }

               if (!isValidUrl(link)) {
                   Toast.makeText(this, getString(R.string.invalid_link), Toast.LENGTH_SHORT).show()
                   return
               }

               // Only support TikTok for now with TikWM API
               if (selectedPlatform != "tiktok") {
                   Toast.makeText(this, getString(R.string.only_tiktok_supported), Toast.LENGTH_SHORT).show()
                   return
               }

               // Check if this TikTok link was already downloaded
               if (isVideoAlreadyDownloaded(link)) {
                   showVideoAlreadyExistsDialog(link)
                   return
               }

               showDownloadProgress()

               lifecycleScope.launch {
                   try {
                       val response = apiService.downloadTikTokVideo(link)

                       if (response.code == 0 && response.data != null) {
                           val videoUrl = response.data.play ?: response.data.wmplay
                           val title = response.data.title ?: "TikTok Video"
                           val thumbnail = response.data.cover

                           if (videoUrl != null) {
                               downloadFile(videoUrl, title, link)
                           } else {
                               hideDownloadProgress()
                               Toast.makeText(this@MainActivity, getString(R.string.no_video_url), Toast.LENGTH_LONG).show()
                           }
                       } else {
                           hideDownloadProgress()
                           Toast.makeText(this@MainActivity, response.msg ?: "Download failed", Toast.LENGTH_LONG).show()
                       }
                   } catch (e: Exception) {
                       hideDownloadProgress()
                       Toast.makeText(this@MainActivity, "Network error: ${e.message}", Toast.LENGTH_LONG).show()
                   }
               }
           }
    
                          private fun downloadFile(url: String, title: String, tikTokLink: String) {
               try {
                   // Use DCIM directory so videos appear in gallery
                   val downloadsDir = File(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DCIM), "SnapTikPro")
                   if (!downloadsDir.exists()) {
                       val created = downloadsDir.mkdirs()
                       android.util.Log.d("DownloadManager", "Created directory: $created, Path: ${downloadsDir.absolutePath}")
                   }

                   // Clean filename
                   val randomNumber = (1000000000..9999999999).random()
                   val fileName = "${randomNumber}.mp4"
                   val file = File(downloadsDir, fileName)

                   android.util.Log.d("DownloadManager", "Download path: ${file.absolutePath}")
                   android.util.Log.d("DownloadManager", "Directory exists: ${downloadsDir.exists()}")
                   android.util.Log.d("DownloadManager", "Directory writable: ${downloadsDir.canWrite()}")

                   downloadManager.downloadFile(url, file, object : DownloadManager.DownloadCallback {
                       override fun onProgress(progress: Int) {
                           updateDownloadProgress(progress)
                       }

                       override fun onSuccess(file: File) {
                           hideDownloadProgress()
                           // Save the video link to prevent re-downloading
                           saveDownloadedLink(tikTokLink)
                           Toast.makeText(this@MainActivity, getString(R.string.download_complete), Toast.LENGTH_LONG).show()
                           saveDownloadRecord(title, file.absolutePath, file.length())

                           // Add to MediaStore for Android 10+
                           if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.Q) {
                               addToMediaStore(file)
                           }

                           // Trigger media scanner to make video appear in gallery
                           triggerMediaScanner(file.absolutePath)

                           // Show success dialog with options
                           showDownloadSuccessDialog(title, file.absolutePath)
                       }

                       override fun onError(error: String) {
                           hideDownloadProgress()
                           Toast.makeText(this@MainActivity, getString(R.string.download_failed), Toast.LENGTH_LONG).show()
                       }
                   })

               } catch (e: Exception) {
                   android.util.Log.e("DownloadManager", "Error setting up download: ${e.message}")
                   Toast.makeText(this, "Error setting up download: ${e.message}", Toast.LENGTH_LONG).show()
               }
           }

           @androidx.annotation.RequiresApi(android.os.Build.VERSION_CODES.Q)
           private fun addToMediaStore(file: File) {
               try {
                   val contentValues = android.content.ContentValues().apply {
                       put(android.provider.MediaStore.Video.Media.DISPLAY_NAME, file.name)
                       put(android.provider.MediaStore.Video.Media.MIME_TYPE, "video/mp4")
                       put(android.provider.MediaStore.Video.Media.RELATIVE_PATH, "DCIM/SnapTikPro")
                       put(android.provider.MediaStore.Video.Media.SIZE, file.length())
                       put(android.provider.MediaStore.Video.Media.DATE_ADDED, System.currentTimeMillis() / 1000)
                       put(android.provider.MediaStore.Video.Media.DATE_MODIFIED, file.lastModified() / 1000)
                   }

                   val resolver = contentResolver
                   val uri = resolver.insert(android.provider.MediaStore.Video.Media.EXTERNAL_CONTENT_URI, contentValues)

                   if (uri != null) {
                       android.util.Log.d("MediaStore", "Video added to MediaStore: $uri")
                   } else {
                       android.util.Log.e("MediaStore", "Failed to add video to MediaStore")
                   }
               } catch (e: Exception) {
                   android.util.Log.e("MediaStore", "Error adding video to MediaStore: ${e.message}")
               }
           }
    
    private fun showDownloadProgress() {
        binding.progressBar.visibility = View.VISIBLE
        binding.tvStatus.visibility = View.VISIBLE
        binding.tvStatus.text = getString(R.string.downloading)
        binding.btnDownload.isEnabled = false
    }
    
    private fun hideDownloadProgress() {
        binding.progressBar.visibility = View.GONE
        binding.tvStatus.visibility = View.GONE
        binding.btnDownload.isEnabled = true
    }
    
    private fun updateDownloadProgress(progress: Int) {
        binding.progressBar.progress = progress
        binding.tvStatus.text = "Downloading... $progress%"
    }
    
    private fun saveDownloadRecord(title: String, path: String, size: Long) {
        val downloadRecord = DownloadRecord(
            title = title,
            path = path,
            size = size,
            date = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault()).format(Date())
        )
        
        // Save to local database or shared preferences
        // For now, we'll just show a success message
    }
    
    private fun showDownloadSuccessDialog(title: String, filePath: String) {
        val dialogView = LayoutInflater.from(this).inflate(R.layout.dialog_download_success, null)
        
        // Set video title
        val tvTitle = dialogView.findViewById<TextView>(R.id.tvVideoTitle)
        tvTitle.text = title
        
        // Set file info
        val file = File(filePath)
        val tvFileInfo = dialogView.findViewById<TextView>(R.id.tvFileInfo)
        val fileSize = formatFileSize(file.length())
        val fileName = file.name
        tvFileInfo.text = "📁 $fileName\n💾 $fileSize"
        
        // Set download location
        val tvLocation = dialogView.findViewById<TextView>(R.id.tvLocation)
        tvLocation.text = "📂 DCIM/SnapTikPro"
        
        val dialog = AlertDialog.Builder(this)
            .setView(dialogView)
            .setCancelable(false)
            .create()
        
        // Set button click listeners
        dialogView.findViewById<Button>(R.id.btnPlayVideo).setOnClickListener {
            dialog.dismiss()
            playVideo(filePath)
        }
        
        dialogView.findViewById<Button>(R.id.btnViewDownloads).setOnClickListener {
            dialog.dismiss()
            openDownloads()
        }
        
        dialogView.findViewById<Button>(R.id.btnShareVideo).setOnClickListener {
            dialog.dismiss()
            shareVideo(filePath)
        }
        
        dialogView.findViewById<Button>(R.id.btnDownloadAnother).setOnClickListener {
            dialog.dismiss()
            binding.etLink.text.clear()
            binding.etLink.requestFocus()
        }
        
        dialogView.findViewById<Button>(R.id.btnClose).setOnClickListener {
            dialog.dismiss()
        }
        
        dialog.show()
    }
    
    private fun formatFileSize(size: Long): String {
        val df = DecimalFormat("#.##")
        val sizeKb = size / 1024.0
        val sizeMb = sizeKb / 1024.0
        val sizeGb = sizeMb / 1024.0

        return when {
            sizeGb >= 1 -> "${df.format(sizeGb)} GB"
            sizeMb >= 1 -> "${df.format(sizeMb)} MB"
            sizeKb >= 1 -> "${df.format(sizeKb)} KB"
            else -> "$size bytes"
        }
    }
    
    private fun shareVideo(filePath: String) {
        try {
            val file = File(filePath)
            if (file.exists()) {
                val uri = try {
                    FileProvider.getUriForFile(
                        this,
                        "${packageName}.fileprovider",
                        file
                    )
                } catch (e: Exception) {
                    Uri.fromFile(file)
                }

                val shareIntent = Intent(Intent.ACTION_SEND).apply {
                    type = "video/*"
                    putExtra(Intent.EXTRA_STREAM, uri)
                    putExtra(Intent.EXTRA_SUBJECT, "Check out this video!")
                    addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
                }

                startActivity(Intent.createChooser(shareIntent, "Share video via"))
            } else {
                Toast.makeText(this, getString(R.string.file_not_found), Toast.LENGTH_SHORT).show()
            }
        } catch (e: Exception) {
            Toast.makeText(this, "Error sharing video: ${e.message}", Toast.LENGTH_LONG).show()
        }
    }
    
    private fun playVideo(filePath: String) {
        val file = File(filePath)
        if (file.exists()) {
            try {
                val uri = androidx.core.content.FileProvider.getUriForFile(
                    this,
                    "${packageName}.fileprovider",
                    file
                )
                
                val intent = Intent(Intent.ACTION_VIEW)
                intent.setDataAndType(uri, "video/*")
                intent.addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
                
                startActivity(intent)
            } catch (e: Exception) {
                Toast.makeText(this, getString(R.string.no_video_player), Toast.LENGTH_SHORT).show()
            }
        } else {
            Toast.makeText(this, getString(R.string.file_not_found), Toast.LENGTH_SHORT).show()
        }
    }
    
    private fun isValidUrl(url: String): Boolean {
        return url.startsWith("http://") || url.startsWith("https://")
    }
    
    private fun checkPermissions() {
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.WRITE_EXTERNAL_STORAGE) 
            != PackageManager.PERMISSION_GRANTED) {
            ActivityCompat.requestPermissions(
                this,
                arrayOf(Manifest.permission.WRITE_EXTERNAL_STORAGE),
                PERMISSION_REQUEST_CODE
            )
        }
    }
    
    override fun onRequestPermissionsResult(
        requestCode: Int,
        permissions: Array<out String>,
        grantResults: IntArray
    ) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults)
        
        if (requestCode == PERMISSION_REQUEST_CODE) {
            if (grantResults.isNotEmpty() && grantResults[0] == PackageManager.PERMISSION_GRANTED) {
                Toast.makeText(this, getString(R.string.storage_permission_granted), Toast.LENGTH_SHORT).show()
            } else {
                Toast.makeText(this, getString(R.string.permission_required), Toast.LENGTH_LONG).show()
            }
        }
    }
    
    private fun openDownloads() {
        val intent = Intent(this, DownloadsActivity::class.java)
        startActivity(intent)
    }
    
               private fun openSettings() {
               val intent = Intent(this, SettingsActivity::class.java)
               startActivity(intent)
           }
    
    private fun openHelp() {
        AlertDialog.Builder(this)
            .setTitle("📱 Video Downloader Pro - Nasıl Kullanılır?")
            .setMessage("""
                🚀 Uygulamayı kullanmaya başlamak için:
                
                1️⃣ Video linkini kopyalayın
                2️⃣ Uygulamayı açın
                3️⃣ Link otomatik olarak yapıştırılacak
                4️⃣ "Yüklə" düğmesine basın
                5️⃣ Video indirilecek ve galeriye eklenecek
                6️⃣ "Yükləmələr" bölümünden videolarınızı görüntüleyin
                
                ⚡ Otomatik İndirme:
                • Video linkini kopyalayın
                • Uygulamayı açın
                • İndirme otomatik başlayacak
                
                📱 Desteklenen Platformlar:
                • TikTok (şu anda aktif)
                • Diğer platformlar yakında eklenecek
                
                🎬 Video Oynatıcı:
                • İndirilen videoları oynatın
                • Paylaşın ve silin
                • Tam ekran desteği
                
                🖼️ Galeri Entegrasyonu:
                • Videolar galeri'de görünür
                • DCIM/SnapTikPro klasörüne kaydedilir
                
                🎉 Bu kadar! Artık videolarınızı kolayca indirebilirsiniz.
            """.trimIndent())
            .setPositiveButton("Anladım", null)
            .show()
    }
    
    private fun checkClipboardForVideoLink() {
        try {
            val clipboard = getSystemService(Context.CLIPBOARD_SERVICE) as ClipboardManager
            val prefs = getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            
            if (clipboard.hasPrimaryClip()) {
                val clipData = clipboard.primaryClip
                if (clipData != null && clipData.itemCount > 0) {
                    val text = clipData.getItemAt(0).text.toString()
                    
                    // Check if this is a video link and not already processed
                    if (isVideoLink(text)) {
                        val lastProcessedLink = prefs.getString(KEY_LAST_LINK, "")
                        
                        // Only process if it's a new link
                        if (text != lastProcessedLink) {
                            // Save the processed link
                            prefs.edit().putString(KEY_LAST_LINK, text).apply()
                            
                            // Auto-paste the link
                            binding.etLink.setText(text)
                            
                            // Show a brief message
                            Toast.makeText(this, getString(R.string.tiktok_link_found_downloading), Toast.LENGTH_SHORT).show()
                            
                            // Automatically start download
                            downloadVideo()
                        }
                    }
                }
            }
        } catch (e: Exception) {
            android.util.Log.e("ClipboardCheck", "Error checking clipboard: ${e.message}")
        }
    }
    
               private fun isVideoLink(text: String): Boolean {
               return text.contains("tiktok.com") ||
                      text.contains("vm.tiktok.com") ||
                      text.contains("vt.tiktok.com") ||
                      text.contains("www.tiktok.com") ||
                      text.contains("instagram.com") ||
                      text.contains("facebook.com") ||
                      text.contains("twitter.com")
           }
           
           private fun isVideoAlreadyDownloaded(videoLink: String): Boolean {
               // Check if this video link was already processed
               val prefs = getSharedPreferences("download_history", Context.MODE_PRIVATE)
               val downloadedLinks = prefs.getStringSet("downloaded_links", setOf()) ?: setOf()
               
               // Clean the link for comparison (remove query parameters)
               val cleanLink = cleanVideoLink(videoLink)
               
               return downloadedLinks.contains(cleanLink)
           }
           
           private fun cleanVideoLink(link: String): String {
               // Remove query parameters and get the base TikTok URL
               return try {
                   val uri = Uri.parse(link)
                   val baseUrl = "${uri.scheme}://${uri.host}${uri.path}"
                   baseUrl
               } catch (e: Exception) {
                   // If parsing fails, return the original link
                   link
               }
           }
           
           private fun saveDownloadedLink(videoLink: String) {
               val prefs = getSharedPreferences("download_history", Context.MODE_PRIVATE)
               val downloadedLinks = prefs.getStringSet("downloaded_links", setOf())?.toMutableSet() ?: mutableSetOf()
               
               val cleanLink = cleanVideoLink(videoLink)
               downloadedLinks.add(cleanLink)
               
               prefs.edit().putStringSet("downloaded_links", downloadedLinks).apply()
           }
           
           private fun showVideoAlreadyExistsDialog(videoLink: String) {
               AlertDialog.Builder(this)
                   .setTitle(getString(R.string.video_already_exists_title))
                   .setMessage(getString(R.string.video_already_exists_message_link).format(videoLink))
                   .setPositiveButton(getString(R.string.view_downloads)) { _, _ ->
                       openDownloads()
                   }
                   .setNegativeButton(getString(R.string.download_anyway)) { _, _ ->
                       // Force download anyway by removing from history
                       val prefs = getSharedPreferences("download_history", Context.MODE_PRIVATE)
                       val downloadedLinks = prefs.getStringSet("downloaded_links", setOf())?.toMutableSet() ?: mutableSetOf()
                       val cleanLink = cleanVideoLink(videoLink)
                       downloadedLinks.remove(cleanLink)
                       prefs.edit().putStringSet("downloaded_links", downloadedLinks).apply()
                       
                       // Start download
                       downloadVideo()
                   }
                   .setNeutralButton(getString(R.string.cancel), null)
                   .show()
           }
    

    

    
               override fun onResume() {
               super.onResume()
               // Check clipboard when returning to the app with a small delay
               binding.root.postDelayed({
                   checkClipboardForVideoLink()
               }, 500) // 500ms delay to ensure clipboard is ready
           }

           private fun triggerMediaScanner(filePath: String) {
               try {
                   val file = File(filePath)
                   
                   // Trigger media scanner to make video appear in gallery
                   val intent = Intent(Intent.ACTION_MEDIA_SCANNER_SCAN_FILE)
                   val uri = Uri.fromFile(file)
                   intent.data = uri
                   sendBroadcast(intent)
                   
                   android.util.Log.d("MediaScanner", "Triggered media scanner for: $filePath")
               } catch (e: Exception) {
                   android.util.Log.e("MediaScanner", "Error triggering media scanner: ${e.message}")
               }
           }
}