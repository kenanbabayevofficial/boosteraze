package com.snaptikpro.app.utils

import android.content.Context
import kotlinx.coroutines.*
import okhttp3.OkHttpClient
import okhttp3.Request
import java.io.File
import java.io.FileOutputStream
import java.io.IOException

class DownloadManager(private val context: Context) {
    
    private val client = OkHttpClient()
    private val scope = CoroutineScope(Dispatchers.IO + SupervisorJob())
    
    interface DownloadCallback {
        fun onProgress(progress: Int)
        fun onSuccess(file: File)
        fun onError(error: String)
    }
    
    fun downloadFile(url: String, file: File, callback: DownloadCallback) {
        android.util.Log.d("DownloadManager", "Starting download: $url")
        android.util.Log.d("DownloadManager", "File path: ${file.absolutePath}")
        
        scope.launch {
            try {
                val request = Request.Builder()
                    .url(url)
                    .addHeader("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36")
                    .build()
                
                val response = client.newCall(request).execute()
                
                if (!response.isSuccessful) {
                    withContext(Dispatchers.Main) {
                        callback.onError("HTTP ${response.code}")
                    }
                    return@launch
                }
                
                val body = response.body
                if (body == null) {
                    withContext(Dispatchers.Main) {
                        callback.onError("Empty response body")
                    }
                    return@launch
                }
                
                val contentLength = body.contentLength()
                val inputStream = body.byteStream()
                val outputStream = FileOutputStream(file)
                
                android.util.Log.d("DownloadManager", "Content length: $contentLength")
                android.util.Log.d("DownloadManager", "File created: ${file.exists()}")
                
                val buffer = ByteArray(8192)
                var bytesRead: Int
                var totalBytesRead = 0L
                
                while (inputStream.read(buffer).also { bytesRead = it } != -1) {
                    outputStream.write(buffer, 0, bytesRead)
                    totalBytesRead += bytesRead
                    
                    if (contentLength > 0) {
                        val progress = ((totalBytesRead * 100) / contentLength).toInt()
                        withContext(Dispatchers.Main) {
                            callback.onProgress(progress)
                        }
                    }
                }
                
                outputStream.close()
                inputStream.close()
                
                android.util.Log.d("DownloadManager", "Download completed. File size: ${file.length()}")
                android.util.Log.d("DownloadManager", "File exists: ${file.exists()}")
                
                withContext(Dispatchers.Main) {
                    callback.onSuccess(file)
                }
                
            } catch (e: IOException) {
                withContext(Dispatchers.Main) {
                    callback.onError("Download failed: ${e.message}")
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    callback.onError("Unexpected error: ${e.message}")
                }
            }
        }
    }
    
    fun cancelAllDownloads() {
        scope.cancel()
    }
}