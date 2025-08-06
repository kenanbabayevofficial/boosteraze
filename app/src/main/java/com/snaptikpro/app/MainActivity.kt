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
    }
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)
        
        setupApiService()
        setupDownloadManager()
        setupUI()
        checkPermissions()
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
        binding.btnPaste.setOnClickListener { pasteFromClipboard() }
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
    
    private fun pasteFromClipboard() {
        val clipboard = getSystemService(Context.CLIPBOARD_SERVICE) as ClipboardManager
        if (clipboard.hasPrimaryClip()) {
            val clipData = clipboard.primaryClip
                    if (clipData != null && clipData.itemCount > 0) {
            val text = clipData.getItemAt(0).text.toString()
            binding.etLink.setText(text)
            Toast.makeText(this, getString(R.string.link_pasted), Toast.LENGTH_SHORT).show()
        }
    } else {
        Toast.makeText(this, getString(R.string.no_clipboard_text), Toast.LENGTH_SHORT).show()
    }
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
        
        showDownloadProgress()
        
        lifecycleScope.launch {
            try {
                val response = apiService.downloadTikTokVideo(link)
                
                if (response.code == 0 && response.data != null) {
                    val videoUrl = response.data.play ?: response.data.wmplay
                    val title = response.data.title ?: "TikTok Video"
                    val thumbnail = response.data.cover
                    
                    if (videoUrl != null) {
                        downloadFile(videoUrl, title)
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
    
    private fun downloadFile(url: String, title: String) {
        try {
            // Use public Movies directory for better organization
            val downloadsDir = File(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_MOVIES), "SnapTik")
            if (!downloadsDir.exists()) {
                val created = downloadsDir.mkdirs()
                android.util.Log.d("DownloadManager", "Created directory: $created, Path: ${downloadsDir.absolutePath}")
            }
            
            // Clean filename
            val cleanTitle = title.replace(Regex("[^a-zA-Z0-9._-]"), "_")
            val fileName = "${cleanTitle}_${System.currentTimeMillis()}.mp4"
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
                    Toast.makeText(this@MainActivity, getString(R.string.download_complete), Toast.LENGTH_LONG).show()
                    saveDownloadRecord(title, file.absolutePath, file.length())
                    
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
        // TODO: Implement settings activity
        Toast.makeText(this, getString(R.string.settings_coming_soon), Toast.LENGTH_SHORT).show()
    }
    
    private fun openHelp() {
        // TODO: Implement help activity
        Toast.makeText(this, getString(R.string.help_coming_soon), Toast.LENGTH_SHORT).show()
    }
}