package com.trlike.smmapp.data.model

data class Service(
    val id: String,
    val name: String,
    val description: String,
    val category: String,
    val platform: String, // Instagram, TikTok, etc.
    val price: Int, // in credits
    val deliveryTime: String,
    val isActive: Boolean = true
)