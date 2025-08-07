package com.snaptikpro.app

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.FileProvider
import androidx.media3.common.MediaItem
import androidx.media3.common.Player
import androidx.media3.exoplayer.ExoPlayer
import androidx.media3.ui.PlayerView
import com.snaptikpro.app.databinding.ActivityVideoPlayerBinding
import java.io.File

class VideoPlayerActivity : AppCompatActivity() {
    
    private lateinit var binding: ActivityVideoPlayerBinding
    private var player: ExoPlayer? = null
    private var videoPath: String? = null
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityVideoPlayerBinding.inflate(layoutInflater)
        setContentView(binding.root)
        
        videoPath = intent.getStringExtra("video_path")
        if (videoPath == null) {
            Toast.makeText(this, "Video path not found", Toast.LENGTH_SHORT).show()
            finish()
            return
        }
        
        setupUI()
        setupPlayer()
    }
    
    private fun setupUI() {
        binding.ivBack.setOnClickListener {
            finish()
        }
        
        binding.ivShare.setOnClickListener {
            shareVideo()
        }
        
        binding.ivDelete.setOnClickListener {
            deleteVideo()
        }
    }
    
    private fun setupPlayer() {
        try {
            val file = File(videoPath!!)
            if (!file.exists()) {
                Toast.makeText(this, "Video file not found", Toast.LENGTH_SHORT).show()
                finish()
                return
            }
            
            // Create ExoPlayer
            player = ExoPlayer.Builder(this).build()
            
            // Create media item - try different approaches
            val mediaItem = try {
                // First try FileProvider
                val uri = FileProvider.getUriForFile(
                    this,
                    "${packageName}.fileprovider",
                    file
                )
                MediaItem.fromUri(uri)
            } catch (e: Exception) {
                // If FileProvider fails, try direct file URI
                val uri = Uri.fromFile(file)
                MediaItem.fromUri(uri)
            }
            
            // Set media item to player
            player?.setMediaItem(mediaItem)
            player?.prepare()
            player?.playWhenReady = true
            
            // Set player to PlayerView
            binding.playerView.player = player
            
            // Add player listener
            player?.addListener(object : Player.Listener {
                override fun onPlaybackStateChanged(playbackState: Int) {
                    when (playbackState) {
                        Player.STATE_READY -> {
                            binding.progressBar.visibility = View.GONE
                        }
                        Player.STATE_BUFFERING -> {
                            binding.progressBar.visibility = View.VISIBLE
                        }
                        Player.STATE_ENDED -> {
                            // Video ended, you can add logic here
                        }
                    }
                }
                
                override fun onPlayerError(error: androidx.media3.common.PlaybackException) {
                    Toast.makeText(this@VideoPlayerActivity, "Error playing video: ${error.message}", Toast.LENGTH_LONG).show()
                    binding.progressBar.visibility = View.GONE
                }
            })
            
        } catch (e: Exception) {
            Toast.makeText(this, "Error setting up player: ${e.message}", Toast.LENGTH_LONG).show()
            finish()
        }
    }
    
    private fun shareVideo() {
        try {
            val file = File(videoPath!!)
            if (file.exists()) {
                val uri = try {
                    // First try FileProvider
                    FileProvider.getUriForFile(
                        this,
                        "${packageName}.fileprovider",
                        file
                    )
                } catch (e: Exception) {
                    // If FileProvider fails, use direct file URI
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
                Toast.makeText(this, "Video file not found", Toast.LENGTH_SHORT).show()
            }
        } catch (e: Exception) {
            Toast.makeText(this, "Error sharing video: ${e.message}", Toast.LENGTH_LONG).show()
        }
    }
    
    private fun deleteVideo() {
        try {
            val file = File(videoPath!!)
            if (file.exists()) {
                val deleted = file.delete()
                if (deleted) {
                    Toast.makeText(this, "Video deleted successfully", Toast.LENGTH_SHORT).show()
                    // Send result back to DownloadsActivity
                    setResult(RESULT_OK, Intent().apply {
                        putExtra("deleted_video_path", videoPath)
                    })
                    finish()
                } else {
                    Toast.makeText(this, "Failed to delete video", Toast.LENGTH_SHORT).show()
                }
            } else {
                Toast.makeText(this, "Video file not found", Toast.LENGTH_SHORT).show()
            }
        } catch (e: Exception) {
            Toast.makeText(this, "Error deleting video: ${e.message}", Toast.LENGTH_LONG).show()
        }
    }
    
    override fun onPause() {
        super.onPause()
        player?.pause()
    }
    
    override fun onDestroy() {
        super.onDestroy()
        player?.release()
        player = null
    }
    
    companion object {
        const val REQUEST_CODE_VIDEO_PLAYER = 1001
    }
}