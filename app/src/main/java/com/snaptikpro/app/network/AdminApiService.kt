package com.snaptikpro.app.network

import retrofit2.http.*

interface AdminApiService {
    
    @GET("admob.php")
    suspend fun getAdMobConfig(): AdMobConfig
    
    @POST("register.php")
    suspend fun registerDevice(@Body device: DeviceRegistration): String
    
    @POST("token.php")
    suspend fun registerPushToken(@Body device: DeviceRegistration): String
}

data class AdMobConfig(
    val banner: String,
    val interstitial: String,
    val rewarded: String
)

data class DeviceRegistration(
    val device_id: String,
    val token: String
)