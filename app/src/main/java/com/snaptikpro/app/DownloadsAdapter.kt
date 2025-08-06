package com.snaptikpro.app

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.snaptikpro.app.databinding.ItemDownloadBinding
import java.text.DecimalFormat

class DownloadsAdapter(
    private val downloads: List<DownloadsActivity.DownloadItem>,
    private val onItemClick: (DownloadsActivity.DownloadItem) -> Unit,
    private val onShareClick: (DownloadsActivity.DownloadItem) -> Unit,
    private val onDeleteClick: (DownloadsActivity.DownloadItem) -> Unit
) : RecyclerView.Adapter<DownloadsAdapter.ViewHolder>() {
    
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemDownloadBinding.inflate(
            LayoutInflater.from(parent.context),
            parent,
            false
        )
        return ViewHolder(binding)
    }
    
    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        holder.bind(downloads[position])
    }
    
    override fun getItemCount(): Int = downloads.size
    
    inner class ViewHolder(private val binding: ItemDownloadBinding) : RecyclerView.ViewHolder(binding.root) {
        
        init {
            binding.root.setOnClickListener {
                val position = adapterPosition
                if (position != RecyclerView.NO_POSITION) {
                    onItemClick(downloads[position])
                }
            }
            
            binding.ivShare.setOnClickListener {
                val position = adapterPosition
                if (position != RecyclerView.NO_POSITION) {
                    onShareClick(downloads[position])
                }
            }
            
            binding.btnDelete.setOnClickListener {
                val position = adapterPosition
                if (position != RecyclerView.NO_POSITION) {
                    onDeleteClick(downloads[position])
                }
            }
        }
        
        fun bind(download: DownloadsActivity.DownloadItem) {
            binding.tvTitle.text = download.title
            binding.tvDate.text = download.date
            binding.tvSize.text = formatFileSize(download.size)
            
            // TODO: Load thumbnail if available
            // For now, using default icon
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
    }
}