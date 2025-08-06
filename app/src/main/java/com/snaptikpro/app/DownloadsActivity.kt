package com.snaptikpro.app

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.os.Environment
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import com.snaptikpro.app.databinding.ActivityDownloadsBinding
import java.io.File
import java.text.SimpleDateFormat
import java.util.*

class DownloadsActivity : AppCompatActivity() {
    
    private lateinit var binding: ActivityDownloadsBinding
    private lateinit var adapter: DownloadsAdapter
    private val downloads = mutableListOf<DownloadItem>()
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityDownloadsBinding.inflate(layoutInflater)
        setContentView(binding.root)
        
        setupRecyclerView()
        setupUI()
        loadDownloads()
    }
    
    private fun setupRecyclerView() {
        adapter = DownloadsAdapter(downloads) { downloadItem ->
            playVideo(downloadItem)
        }
        
        binding.rvDownloads.layoutManager = LinearLayoutManager(this)
        binding.rvDownloads.adapter = adapter
    }
    
    private fun setupUI() {
        binding.ivBack.setOnClickListener {
            finish()
        }
        
        binding.tvClearAll.setOnClickListener {
            showClearAllDialog()
        }
    }
    
    private fun loadDownloads() {
        val downloadsDir = File(getExternalFilesDir(Environment.DIRECTORY_DOWNLOADS), "SnapTikPro")
        if (downloadsDir.exists()) {
            val files = downloadsDir.listFiles { file ->
                file.extension.lowercase() in listOf("mp4", "avi", "mov", "mkv")
            }
            
            downloads.clear()
            files?.forEach { file ->
                downloads.add(
                    DownloadItem(
                        title = file.nameWithoutExtension,
                        path = file.absolutePath,
                        size = file.length(),
                        date = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault())
                            .format(Date(file.lastModified()))
                    )
                )
            }
            
            downloads.sortByDescending { it.date }
            adapter.notifyDataSetChanged()
        }
        
        updateEmptyState()
    }
    
    private fun updateEmptyState() {
        if (downloads.isEmpty()) {
            binding.emptyLayout.visibility = android.view.View.VISIBLE
            binding.rvDownloads.visibility = android.view.View.GONE
        } else {
            binding.emptyLayout.visibility = android.view.View.GONE
            binding.rvDownloads.visibility = android.view.View.VISIBLE
        }
    }
    
    private fun playVideo(downloadItem: DownloadItem) {
        val file = File(downloadItem.path)
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
                Toast.makeText(this, "No video player found: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        } else {
            Toast.makeText(this, "File not found", Toast.LENGTH_SHORT).show()
        }
    }
    
    private fun showClearAllDialog() {
        AlertDialog.Builder(this)
            .setTitle("Clear All Downloads")
            .setMessage("Are you sure you want to delete all downloaded videos?")
            .setPositiveButton("Clear") { _, _ ->
                clearAllDownloads()
            }
            .setNegativeButton("Cancel", null)
            .show()
    }
    
    private fun clearAllDownloads() {
        downloads.forEach { downloadItem ->
            val file = File(downloadItem.path)
            if (file.exists()) {
                file.delete()
            }
        }
        
        downloads.clear()
        adapter.notifyDataSetChanged()
        updateEmptyState()
        
        Toast.makeText(this, "All downloads cleared", Toast.LENGTH_SHORT).show()
    }
    
    data class DownloadItem(
        val title: String,
        val path: String,
        val size: Long,
        val date: String
    )
}