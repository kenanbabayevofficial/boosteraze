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
    
    override fun onResume() {
        super.onResume()
        // Refresh the list when returning to this activity
        loadDownloads()
    }
    
    private fun setupRecyclerView() {
        adapter = DownloadsAdapter(
            downloads = downloads,
            onItemClick = { downloadItem ->
                playVideo(downloadItem)
            },
            onDeleteClick = { downloadItem ->
                showDeleteDialog(downloadItem)
            }
        )
        
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
        try {
            val downloadsDir = File(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_MOVIES), "SnapTikPro")
            android.util.Log.d("DownloadsActivity", "Downloads directory: ${downloadsDir.absolutePath}")
            android.util.Log.d("DownloadsActivity", "Directory exists: ${downloadsDir.exists()}")
            
            if (downloadsDir.exists()) {
                val files = downloadsDir.listFiles { file ->
                    file.extension.lowercase() in listOf("mp4", "avi", "mov", "mkv") && file.exists() && file.length() > 0
                }
                
                android.util.Log.d("DownloadsActivity", "Found ${files?.size ?: 0} valid video files")
                
                downloads.clear()
                files?.forEach { file ->
                    // Double check if file still exists and is valid
                    if (file.exists() && file.length() > 0) {
                        android.util.Log.d("DownloadsActivity", "Adding file: ${file.name}, Size: ${file.length()}")
                        downloads.add(
                            DownloadItem(
                                title = "Video ${file.nameWithoutExtension}",
                                path = file.absolutePath,
                                size = file.length(),
                                date = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault())
                                    .format(Date(file.lastModified()))
                            )
                        )
                    } else {
                        android.util.Log.w("DownloadsActivity", "Skipping invalid file: ${file.name}")
                    }
                }
                
                downloads.sortByDescending { it.date }
                adapter.notifyDataSetChanged()
                
                android.util.Log.d("DownloadsActivity", "Total downloads in list: ${downloads.size}")
            } else {
                android.util.Log.w("DownloadsActivity", "Downloads directory does not exist")
                downloads.clear()
                adapter.notifyDataSetChanged()
            }
        } catch (e: Exception) {
            android.util.Log.e("DownloadsActivity", "Error loading downloads: ${e.message}")
            Toast.makeText(this, "Error loading downloads: ${e.message}", Toast.LENGTH_LONG).show()
            downloads.clear()
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
                Toast.makeText(this, getString(R.string.no_video_player), Toast.LENGTH_SHORT).show()
            }
        } else {
            Toast.makeText(this, getString(R.string.file_not_found), Toast.LENGTH_SHORT).show()
            // Remove from list if file doesn't exist
            removeFromList(downloadItem)
        }
    }
    
    private fun deleteVideo(downloadItem: DownloadItem) {
        val file = File(downloadItem.path)
        if (file.exists()) {
            val deleted = file.delete()
            if (deleted) {
                android.util.Log.d("DownloadsActivity", "File ${file.name} deleted successfully")
                removeFromList(downloadItem)
                Toast.makeText(this, "Video deleted", Toast.LENGTH_SHORT).show()
            } else {
                android.util.Log.w("DownloadsActivity", "Failed to delete file ${file.name}")
                Toast.makeText(this, "Failed to delete video", Toast.LENGTH_SHORT).show()
            }
        } else {
            android.util.Log.w("DownloadsActivity", "File ${downloadItem.path} does not exist")
            // Remove from list if file doesn't exist
            removeFromList(downloadItem)
            Toast.makeText(this, "File not found, removed from list", Toast.LENGTH_SHORT).show()
        }
    }
    
    private fun showDeleteDialog(downloadItem: DownloadItem) {
        AlertDialog.Builder(this)
            .setTitle("Delete Video")
            .setMessage("Are you sure you want to delete this video?")
            .setPositiveButton("Delete") { _, _ ->
                deleteVideo(downloadItem)
            }
            .setNegativeButton("Cancel", null)
            .show()
    }
    
    private fun removeFromList(downloadItem: DownloadItem) {
        downloads.remove(downloadItem)
        adapter.notifyDataSetChanged()
        updateEmptyState()
    }
    
    private fun showClearAllDialog() {
        AlertDialog.Builder(this)
            .setTitle(getString(R.string.clear_all_dialog_title))
            .setMessage(getString(R.string.clear_all_dialog_message))
            .setPositiveButton(getString(R.string.clear)) { _, _ ->
                clearAllDownloads()
            }
            .setNegativeButton(getString(R.string.cancel), null)
            .show()
    }
    
    private fun clearAllDownloads() {
        try {
            val downloadsDir = File(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_MOVIES), "SnapTikPro")
            
            if (downloadsDir.exists()) {
                val files = downloadsDir.listFiles { file ->
                    file.extension.lowercase() in listOf("mp4", "avi", "mov", "mkv")
                }
                
                var deletedCount = 0
                files?.forEach { file ->
                    if (file.exists()) {
                        val deleted = file.delete()
                        if (deleted) {
                            deletedCount++
                            android.util.Log.d("DownloadsActivity", "File ${file.name} deleted successfully")
                        } else {
                            android.util.Log.w("DownloadsActivity", "Failed to delete file ${file.name}")
                        }
                    }
                }
                
                android.util.Log.d("DownloadsActivity", "Total files deleted: $deletedCount")
            }
            
            // Clear the list and refresh
            downloads.clear()
            adapter.notifyDataSetChanged()
            updateEmptyState()
            
            Toast.makeText(this, getString(R.string.all_downloads_cleared), Toast.LENGTH_SHORT).show()
            
        } catch (e: Exception) {
            android.util.Log.e("DownloadsActivity", "Error clearing downloads: ${e.message}")
            Toast.makeText(this, "Error clearing downloads: ${e.message}", Toast.LENGTH_LONG).show()
        }
    }
    
    data class DownloadItem(
        val title: String,
        val path: String,
        val size: Long,
        val date: String
    )
}