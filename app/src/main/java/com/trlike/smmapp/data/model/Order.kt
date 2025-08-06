package com.trlike.smmapp.data.model

data class Order(
    val id: String,
    val userId: String,
    val serviceId: String,
    val serviceName: String,
    val username: String,
    val quantity: Int,
    val price: Int,
    val status: OrderStatus,
    val createdAt: String,
    val completedAt: String?
)

enum class OrderStatus {
    PENDING,
    PROCESSING,
    COMPLETED,
    CANCELLED,
    FAILED
}