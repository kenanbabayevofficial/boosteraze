package com.trlike.smmapp.data.repository

import com.trlike.smmapp.data.api.RetrofitClient
import com.trlike.smmapp.data.api.CreateOrderRequest
import com.trlike.smmapp.data.model.Order
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

class OrderRepository {
    
    suspend fun createOrder(serviceId: String, username: String, quantity: Int): Result<Order> {
        return withContext(Dispatchers.IO) {
            try {
                val request = CreateOrderRequest(serviceId, username, quantity)
                val response = RetrofitClient.apiService.createOrder(request)
                
                if (response.isSuccessful) {
                    response.body()?.let { order ->
                        Result.success(order)
                    } ?: Result.failure(Exception("Empty response"))
                } else {
                    Result.failure(Exception("Failed to create order: ${response.code()}"))
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
    }
    
    suspend fun getUserOrders(): Result<List<Order>> {
        return withContext(Dispatchers.IO) {
            try {
                val response = RetrofitClient.apiService.getUserOrders()
                
                if (response.isSuccessful) {
                    response.body()?.let { orders ->
                        Result.success(orders)
                    } ?: Result.failure(Exception("Empty response"))
                } else {
                    Result.failure(Exception("Failed to get orders: ${response.code()}"))
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
    }
    
    suspend fun getOrder(orderId: String): Result<Order> {
        return withContext(Dispatchers.IO) {
            try {
                val response = RetrofitClient.apiService.getOrder(orderId)
                
                if (response.isSuccessful) {
                    response.body()?.let { order ->
                        Result.success(order)
                    } ?: Result.failure(Exception("Empty response"))
                } else {
                    Result.failure(Exception("Failed to get order: ${response.code()}"))
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
    }
}