package com.snaptikpro.app.network

import retrofit2.http.GET
import retrofit2.http.Query

interface ApiService {
    
    @GET("api/")
    suspend fun downloadTikTokVideo(
        @Query("url") url: String
    ): TikWMResponse
}

data class TikWMResponse(
    val code: Int,
    val msg: String,
    val processed_time: Double,
    val data: TikWMData?
)

data class TikWMData(
    val id: String?,
    val region: String?,
    val title: String?,
    val cover: String?,
    val ai_dynamic_cover: String?,
    val origin_cover: String?,
    val duration: Int?,
    val play: String?,
    val wmplay: String?,
    val size: Long?,
    val wm_size: Long?,
    val music: String?,
    val music_info: MusicInfo?,
    val play_count: Long?,
    val digg_count: Long?,
    val comment_count: Long?,
    val share_count: Long?,
    val download_count: Long?,
    val collect_count: Long?,
    val create_time: Long?,
    val author: Author?
)

data class MusicInfo(
    val id: String?,
    val title: String?,
    val play: String?,
    val cover: String?,
    val author: String?,
    val original: Boolean?,
    val duration: Int?,
    val album: String?
)

data class Author(
    val id: String?,
    val unique_id: String?,
    val nickname: String?,
    val avatar: String?
)



// Legacy response for backward compatibility
data class DownloadResponse(
    val success: Boolean,
    val message: String?,
    val downloadUrl: String,
    val title: String,
    val thumbnail: String?
)