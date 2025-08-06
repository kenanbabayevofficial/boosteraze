package com.trlike.smmapp.data.model

data class CreditPackage(
    val id: String,
    val credits: Int,
    val price: Double, // in USD
    val productId: String // Google Play product ID
)