package com.snaptikpro.app.network

import retrofit2.http.GET
import retrofit2.http.Query

interface ApiService {
    
    @GET("download")
    suspend fun downloadVideo(
        @Query("url") url: String,
        @Query("platform") platform: String
    ): DownloadResponse
}

data class DownloadResponse(
    val success: Boolean,
    val message: String?,
    val downloadUrl: String,
    val title: String,
    val thumbnail: String?
)