package com.trlike.smmapp.data.repository

import com.trlike.smmapp.data.api.RetrofitClient
import com.trlike.smmapp.data.model.Service
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

class ServiceRepository {
    
    suspend fun getServices(): Result<List<Service>> {
        return withContext(Dispatchers.IO) {
            try {
                val response = RetrofitClient.apiService.getServices()
                
                if (response.isSuccessful) {
                    response.body()?.let { services ->
                        Result.success(services)
                    } ?: Result.failure(Exception("Empty response"))
                } else {
                    Result.failure(Exception("Failed to get services: ${response.code()}"))
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
    }
    
    suspend fun getServicesByCategory(category: String): Result<List<Service>> {
        return withContext(Dispatchers.IO) {
            try {
                val response = RetrofitClient.apiService.getServicesByCategory(category)
                
                if (response.isSuccessful) {
                    response.body()?.let { services ->
                        Result.success(services)
                    } ?: Result.failure(Exception("Empty response"))
                } else {
                    Result.failure(Exception("Failed to get services: ${response.code()}"))
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
    }
}