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
import android.view.View
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
import java.text.SimpleDateFormat
import java.util.*
import okhttp3.MediaType.Companion.toMediaType

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
        private const val INSTAGRAM_API_URL = "https://api.instagram.com/" // Instagram API URL
        private const val INSTAGRAM_DOWNLOADER_URL = "https://www.instagram.com/" // Instagram Downloader URL
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
    
    private fun setupInstagramApiService(): ApiService {
        val loggingInterceptor = HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BODY
        }

        val client = OkHttpClient.Builder()
            .addInterceptor(loggingInterceptor)
            .addInterceptor { chain ->
                val original = chain.request()
                val request = original.newBuilder()
                    .header("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36")
                    .header("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8")
                    .header("Accept-Language", "en-US,en;q=0.5")
                    .header("Accept-Encoding", "gzip, deflate")
                    .header("Connection", "keep-alive")
                    .header("Upgrade-Insecure-Requests", "1")
                    .method(original.method, original.body)
                    .build()
                chain.proceed(request)
            }
            .build()

        val retrofit = Retrofit.Builder()
            .baseUrl(INSTAGRAM_API_URL)
            .client(client)
            .addConverterFactory(GsonConverterFactory.create())
            .build()

        return retrofit.create(ApiService::class.java)
    }
    
    private fun setupDownloadManager() {
        downloadManager = DownloadManager(this)
    }
    
        private fun setupUI() {
        // Platform tabs - TikTok and Instagram supported
        binding.tvTikTok.setOnClickListener { selectPlatform("tiktok", binding.tvTikTok) }
        binding.tvInstagram.setOnClickListener { 
            selectPlatform("instagram", binding.tvInstagram)
            // Instagram seçildiğinde Instagram linklerini kontrol et
            checkForInstagramLinks()
        }
        
        // Hide other platforms
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
        
        // Reset all tabs
        binding.tvTikTok.setBackgroundResource(0)
        binding.tvTikTok.setTextColor(ContextCompat.getColor(this, R.color.text_secondary))
        binding.tvTikTok.isSelected = false
        
        binding.tvInstagram.setBackgroundResource(0)
        binding.tvInstagram.setTextColor(ContextCompat.getColor(this, R.color.text_secondary))
        binding.tvInstagram.isSelected = false
        
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

        // Check if this video link was already downloaded
        if (isVideoAlreadyDownloaded(link)) {
            showVideoAlreadyExistsDialog(link)
            return
        }

        showDownloadProgress()

        lifecycleScope.launch {
            try {
                when (selectedPlatform) {
                    "tiktok" -> downloadTikTokVideo(link)
                    "instagram" -> downloadInstagramVideo(link)
                    else -> {
                        hideDownloadProgress()
                        Toast.makeText(this@MainActivity, "Unsupported platform", Toast.LENGTH_SHORT).show()
                    }
                }
            } catch (e: Exception) {
                hideDownloadProgress()
                Toast.makeText(this@MainActivity, "Network error: ${e.message}", Toast.LENGTH_LONG).show()
            }
        }
    }
    
    private suspend fun downloadTikTokVideo(link: String) {
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
            Toast.makeText(this@MainActivity, "TikTok download error: ${e.message}", Toast.LENGTH_LONG).show()
        }
    }
    
    private suspend fun downloadInstagramVideo(link: String) {
        try {
            // Instagram için basit ve etkili yöntem
            downloadInstagramWithSimpleMethod(link)
        } catch (e: Exception) {
            hideDownloadProgress()
            android.util.Log.e("InstagramDownload", "Error: ${e.message}")
            showInstagramErrorDialog()
        }
    }
    
    private suspend fun downloadInstagramWithSimpleMethod(link: String) {
        try {
            // Instagram için çok basit yöntem - Instagram sayfasını direkt çek
            val client = OkHttpClient.Builder()
                .addInterceptor(HttpLoggingInterceptor().apply {
                    level = HttpLoggingInterceptor.Level.BODY
                })
                .build()
            
            // Instagram sayfasını direkt olarak çek
            val request = okhttp3.Request.Builder()
                .url(link)
                .header("User-Agent", "Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1")
                .header("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8")
                .header("Accept-Language", "en-US,en;q=0.5")
                .header("Accept-Encoding", "gzip, deflate")
                .header("Connection", "keep-alive")
                .header("Upgrade-Insecure-Requests", "1")
                .build()
            
            val response = client.newCall(request).execute()
            
            if (response.isSuccessful) {
                val responseBody = response.body?.string()
                if (responseBody != null) {
                    // Instagram video URL'sini çıkar
                    val videoUrl = extractInstagramVideoUrlFromHTML(responseBody)
                    if (videoUrl != null) {
                        val title = "Instagram Video"
                        downloadFile(videoUrl, title, link)
                    } else {
                        // Instagram video bulunamadı, kullanıcıya bilgi ver
                        showInstagramNotVideoDialog()
                    }
                } else {
                    showInstagramErrorDialog()
                }
            } else {
                showInstagramErrorDialog()
            }
            
        } catch (e: Exception) {
            android.util.Log.e("InstagramSimple", "Simple method error: ${e.message}")
            showInstagramErrorDialog()
        }
    }
    
    private suspend fun downloadInstagramFromPage(link: String) {
        try {
            // Instagram sayfasını direkt olarak çek
            val client = OkHttpClient.Builder()
                .addInterceptor(HttpLoggingInterceptor().apply {
                    level = HttpLoggingInterceptor.Level.BODY
                })
                .build()
            
            val request = okhttp3.Request.Builder()
                .url(link)
                .header("User-Agent", "Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1")
                .header("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8")
                .header("Accept-Language", "en-US,en;q=0.5")
                .header("Accept-Encoding", "gzip, deflate")
                .header("Connection", "keep-alive")
                .build()
            
            val response = client.newCall(request).execute()
            
            if (response.isSuccessful) {
                val responseBody = response.body?.string()
                if (responseBody != null) {
                    // Instagram video URL'sini çıkar
                    val videoUrl = extractInstagramVideoUrlFromHTML(responseBody)
                    if (videoUrl != null) {
                        val title = "Instagram Video"
                        downloadFile(videoUrl, title, link)
                    } else {
                        // Instagram video bulunamadı, kullanıcıya bilgi ver
                        showInstagramNotVideoDialog()
                    }
                } else {
                    showInstagramErrorDialog()
                }
            } else {
                showInstagramErrorDialog()
            }
            
        } catch (e: Exception) {
            android.util.Log.e("InstagramPage", "Page method error: ${e.message}")
            showInstagramErrorDialog()
        }
    }
    
    private fun extractFromOEmbedResponse(jsonResponse: String, originalLink: String): String? {
        try {
            // oEmbed response'dan video URL'sini çıkar
            val patterns = listOf(
                "\"video_url\":\"([^\"]+)\"",
                "\"contentUrl\":\"([^\"]+)\"",
                "\"url\":\"([^\"]+)\"",
                "\"thumbnail_url\":\"([^\"]+)\""
            )
            
            for (pattern in patterns) {
                val regex = Regex(pattern)
                val matchResult = regex.find(jsonResponse)
                if (matchResult != null) {
                    val videoUrl = matchResult.groupValues[1]
                    val cleanUrl = videoUrl.replace("\\u0026", "&")
                    if (cleanUrl.contains(".mp4") || cleanUrl.contains("video") || cleanUrl.contains("cdninstagram")) {
                        return cleanUrl
                    }
                }
            }
            
            return null
        } catch (e: Exception) {
            android.util.Log.e("InstagramExtract", "Error extracting from oEmbed: ${e.message}")
            return null
        }
    }
    
    private fun extractInstagramVideoUrlFromHTML(htmlContent: String): String? {
        try {
            // Instagram video URL'sini çıkarmak için basit pattern
            val videoUrlPattern = "\"video_url\":\"([^\"]+)\""
            val regex = Regex(videoUrlPattern)
            val matchResult = regex.find(htmlContent)
            
            if (matchResult != null) {
                val videoUrl = matchResult.groupValues[1]
                return videoUrl.replace("\\u0026", "&")
            }
            
            return null
        } catch (e: Exception) {
            android.util.Log.e("InstagramExtract", "Error extracting video URL: ${e.message}")
            return null
        }
    }
    
    private fun showInstagramNotVideoDialog() {
        AlertDialog.Builder(this@MainActivity)
            .setTitle("📸 Instagram İçeriği")
            .setMessage("Bu Instagram linki bir video değil. Instagram'da sadece video içerikler indirilebilir:\n\n" +
                       "✅ Desteklenen:\n" +
                       "• Instagram Reels\n" +
                       "• Video postları\n" +
                       "• IGTV videoları\n\n" +
                       "❌ Desteklenmeyen:\n" +
                       "• Fotoğraf postları\n" +
                       "• Story'ler\n" +
                       "• Özel/gizli içerikler\n\n" +
                       "Lütfen bir video linki deneyin.")
            .setPositiveButton("Tamam", null)
            .setNegativeButton("TikTok'a Geç") { _, _ ->
                selectPlatform("tiktok", binding.tvTikTok)
            }
            .show()
    }
    
    private fun extractInstagramPostId(link: String): String? {
        try {
            // Instagram linkinden post ID'sini çıkar
            val patterns = listOf(
                "/p/([^/]+)",
                "/reel/([^/]+)",
                "/tv/([^/]+)"
            )
            
            for (pattern in patterns) {
                val regex = Regex(pattern)
                val matchResult = regex.find(link)
                if (matchResult != null) {
                    return matchResult.groupValues[1]
                }
            }
            
            return null
        } catch (e: Exception) {
            android.util.Log.e("InstagramExtract", "Error extracting post ID: ${e.message}")
            return null
        }
    }
    
    private fun extractFromGraphQLResponse(jsonResponse: String): String? {
        try {
            // GraphQL response'dan video URL'sini çıkar
            val patterns = listOf(
                "\"video_url\":\"([^\"]+)\"",
                "\"display_url\":\"([^\"]+)\"",
                "\"video_versions\":\\[\\{[^}]*\"url\":\"([^\"]+)\"",
                "\"video_versions\":\\[\\{[^}]*\"url\":\"([^\"]+)\""
            )
            
            for (pattern in patterns) {
                val regex = Regex(pattern)
                val matchResult = regex.find(jsonResponse)
                if (matchResult != null) {
                    val videoUrl = matchResult.groupValues[1]
                    val cleanUrl = videoUrl.replace("\\u0026", "&")
                    if (cleanUrl.contains(".mp4") || cleanUrl.contains("video")) {
                        return cleanUrl
                    }
                }
            }
            
            return null
        } catch (e: Exception) {
            android.util.Log.e("InstagramExtract", "Error extracting from GraphQL: ${e.message}")
            return null
        }
    }
    
    private fun extractInstagramVideoUrlSimple(htmlContent: String): String? {
        try {
            // Instagram video URL'sini çıkarmak için basit regex
            val patterns = listOf(
                "\"video_url\":\"([^\"]+)\"",
                "\"contentUrl\":\"([^\"]+)\"",
                "\"url\":\"([^\"]+)\"",
                "property=\"og:video\" content=\"([^\"]+)\"",
                "property=\"og:video:url\" content=\"([^\"]+)\""
            )
            
            for (pattern in patterns) {
                val regex = Regex(pattern)
                val matchResult = regex.find(htmlContent)
                if (matchResult != null) {
                    val videoUrl = matchResult.groupValues[1]
                    val cleanUrl = videoUrl.replace("\\u0026", "&")
                    if (cleanUrl.contains(".mp4") || cleanUrl.contains("video")) {
                        return cleanUrl
                    }
                }
            }
            
            return null
        } catch (e: Exception) {
            android.util.Log.e("InstagramExtract", "Error extracting video URL: ${e.message}")
            return null
        }
    }
    
    private suspend fun downloadInstagramWithWebScraping(link: String) {
        try {
            // Instagram için gelişmiş web scraping yöntemi
            val client = OkHttpClient.Builder()
                .addInterceptor(HttpLoggingInterceptor().apply {
                    level = HttpLoggingInterceptor.Level.BODY
                })
                .build()
            
            // Instagram sayfasını direkt olarak çek
            val request = okhttp3.Request.Builder()
                .url(link)
                .header("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36")
                .header("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8")
                .header("Accept-Language", "en-US,en;q=0.5")
                .header("Accept-Encoding", "gzip, deflate")
                .header("Connection", "keep-alive")
                .header("Upgrade-Insecure-Requests", "1")
                .build()
            
            val response = client.newCall(request).execute()
            
            if (response.isSuccessful) {
                val responseBody = response.body?.string()
                if (responseBody != null) {
                    // Instagram video URL'sini çıkar
                    val videoUrl = extractInstagramVideoUrl(responseBody, link)
                    if (videoUrl != null) {
                        val title = "Instagram Video"
                        downloadFile(videoUrl, title, link)
                    } else {
                        // Alternatif yöntem: Instagram API endpoint'i dene
                        tryAlternativeInstagramMethod(link)
                    }
                } else {
                    tryAlternativeInstagramMethod(link)
                }
            } else {
                tryAlternativeInstagramMethod(link)
            }
            
        } catch (e: Exception) {
            android.util.Log.e("InstagramScraping", "Web scraping error: ${e.message}")
            tryAlternativeInstagramMethod(link)
        }
    }
    
    private suspend fun tryAlternativeInstagramMethod(link: String) {
        try {
            // Instagram için alternatif yöntem: JSON-LD structured data
            val client = OkHttpClient.Builder()
                .addInterceptor(HttpLoggingInterceptor().apply {
                    level = HttpLoggingInterceptor.Level.BODY
                })
                .build()
            
            val request = okhttp3.Request.Builder()
                .url("https://www.instagram.com/oembed/?url=$link&format=json")
                .header("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36")
                .build()
            
            val response = client.newCall(request).execute()
            
            if (response.isSuccessful) {
                val responseBody = response.body?.string()
                if (responseBody != null) {
                    // JSON response'dan video URL'sini çıkar
                    val videoUrl = extractFromJsonResponse(responseBody)
                    if (videoUrl != null) {
                        val title = "Instagram Video"
                        downloadFile(videoUrl, title, link)
                    } else {
                        showInstagramErrorDialog()
                    }
                } else {
                    showInstagramErrorDialog()
                }
            } else {
                showInstagramErrorDialog()
            }
            
        } catch (e: Exception) {
            android.util.Log.e("InstagramAlternative", "Alternative method error: ${e.message}")
            showInstagramErrorDialog()
        }
    }
    
    private fun extractFromJsonResponse(jsonResponse: String): String? {
        try {
            // JSON response'dan video URL'sini çıkar
            val videoUrlPattern = "\"video_url\":\"([^\"]+)\""
            val regex = Regex(videoUrlPattern)
            val matchResult = regex.find(jsonResponse)
            
            if (matchResult != null) {
                val videoUrl = matchResult.groupValues[1]
                return videoUrl.replace("\\u0026", "&")
            }
            
            // Alternatif pattern'lar
            val patterns = listOf(
                "\"contentUrl\":\"([^\"]+)\"",
                "\"url\":\"([^\"]+)\"",
                "\"src\":\"([^\"]+)\""
            )
            
            for (pattern in patterns) {
                val regex2 = Regex(pattern)
                val match = regex2.find(jsonResponse)
                if (match != null) {
                    val url = match.groupValues[1]
                    if (url.contains(".mp4") || url.contains("video")) {
                        return url.replace("\\u0026", "&")
                    }
                }
            }
            
            return null
        } catch (e: Exception) {
            android.util.Log.e("InstagramExtract", "Error extracting from JSON: ${e.message}")
            return null
        }
    }
    
    private fun extractInstagramVideoUrl(htmlContent: String, originalLink: String): String? {
        try {
            // Instagram video URL'sini çıkarmak için regex kullan
            val videoUrlPattern = "\"video_url\":\"([^\"]+)\""
            val regex = Regex(videoUrlPattern)
            val matchResult = regex.find(htmlContent)
            
            if (matchResult != null) {
                val videoUrl = matchResult.groupValues[1]
                return videoUrl.replace("\\u0026", "&")
            }
            
            // Alternatif pattern
            val altPattern = "\"contentUrl\":\"([^\"]+)\""
            val altRegex = Regex(altPattern)
            val altMatch = altRegex.find(htmlContent)
            
            if (altMatch != null) {
                val videoUrl = altMatch.groupValues[1]
                return videoUrl.replace("\\u0026", "&")
            }
            
            return null
        } catch (e: Exception) {
            android.util.Log.e("InstagramExtract", "Error extracting video URL: ${e.message}")
            return null
        }
    }
    
    private fun cleanInstagramLink(link: String): String {
        return try {
            // Instagram linkini temizle
            var cleanLink = link.trim()
            
            // Query parametrelerini kaldır
            if (cleanLink.contains("?")) {
                cleanLink = cleanLink.substring(0, cleanLink.indexOf("?"))
            }
            
            // Trailing slash'i kaldır
            if (cleanLink.endsWith("/")) {
                cleanLink = cleanLink.substring(0, cleanLink.length - 1)
            }
            
            cleanLink
        } catch (e: Exception) {
            link
        }
    }
    
    private fun showInstagramErrorDialog() {
        AlertDialog.Builder(this@MainActivity)
            .setTitle("⚠️ Instagram İndirme Hatası")
            .setMessage("Instagram video indirilemedi. Bu durum şu sebeplerden olabilir:\n\n" +
                       "• Video özel/gizli\n" +
                       "• Instagram API değişikliği\n" +
                       "• Ağ bağlantı sorunu\n\n" +
                       "Alternatif çözümler:\n" +
                       "• TikTok seçeneğini deneyin\n" +
                       "• Farklı bir Instagram linki deneyin\n" +
                       "• Daha sonra tekrar deneyin")
            .setPositiveButton("TikTok'a Geç") { _, _ ->
                selectPlatform("tiktok", binding.tvTikTok)
            }
            .setNegativeButton("Tamam", null)
            .show()
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
        AlertDialog.Builder(this)
            .setTitle(getString(R.string.download_complete_title))
            .setMessage(getString(R.string.download_complete_message, title))
            .setPositiveButton(getString(R.string.view_downloads)) { _, _ ->
                openDownloads()
            }
            .setNegativeButton(getString(R.string.download_another)) { _, _ ->
                binding.etLink.text.clear()
                binding.etLink.requestFocus()
            }
            .setNeutralButton(getString(R.string.play_video)) { _, _ ->
                playVideo(filePath)
            }
            .show()
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
               text.contains("www.instagram.com") ||
               text.contains("reels") ||
               text.contains("facebook.com") ||
               text.contains("twitter.com")
    }
    
    private fun isInstagramLink(text: String): Boolean {
        return text.contains("instagram.com") ||
               text.contains("www.instagram.com") ||
               text.contains("reels") ||
               text.contains("reel/") ||
               text.contains("p/")
    }
    
    private fun checkForInstagramLinks() {
        try {
            val clipboard = getSystemService(Context.CLIPBOARD_SERVICE) as ClipboardManager
            
            if (clipboard.hasPrimaryClip()) {
                val clipData = clipboard.primaryClip
                if (clipData != null && clipData.itemCount > 0) {
                    val text = clipData.getItemAt(0).text.toString()
                    
                    if (isInstagramLink(text)) {
                        // Instagram linki bulundu, kullanıcıya bilgi ver
                        binding.etLink.setText(text)
                        Toast.makeText(this, "Instagram linki bulundu! Yakında desteklenecek.", Toast.LENGTH_LONG).show()
                    }
                }
            }
        } catch (e: Exception) {
            android.util.Log.e("InstagramCheck", "Error checking Instagram links: ${e.message}")
        }
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