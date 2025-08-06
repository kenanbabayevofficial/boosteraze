package com.trlike.smmapp.data.model

data class Coupon(
    val id: String,
    val code: String,
    val discount: Int, // percentage or fixed amount
    val type: CouponType,
    val maxUses: Int,
    val usedCount: Int,
    val isActive: Boolean = true,
    val expiresAt: String?
)

enum class CouponType {
    PERCENTAGE,
    FIXED_AMOUNT
}