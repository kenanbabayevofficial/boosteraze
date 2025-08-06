package com.trlike.smmapp.data.api

import com.trlike.smmapp.data.model.*
import retrofit2.Response
import retrofit2.http.*

interface ApiService {
    
    // Authentication
    @POST("auth/google")
    suspend fun googleSignIn(@Body request: GoogleSignInRequest): Response<AuthResponse>
    
    // User
    @GET("user/profile")
    suspend fun getUserProfile(): Response<User>
    
    @PUT("user/credits")
    suspend fun updateCredits(@Body request: UpdateCreditsRequest): Response<User>
    
    // Services
    @GET("services")
    suspend fun getServices(): Response<List<Service>>
    
    @GET("services/{category}")
    suspend fun getServicesByCategory(@Path("category") category: String): Response<List<Service>>
    
    // Orders
    @POST("orders")
    suspend fun createOrder(@Body request: CreateOrderRequest): Response<Order>
    
    @GET("orders")
    suspend fun getUserOrders(): Response<List<Order>>
    
    @GET("orders/{orderId}")
    suspend fun getOrder(@Path("orderId") orderId: String): Response<Order>
    
    // Credit Packages
    @GET("credit-packages")
    suspend fun getCreditPackages(): Response<List<CreditPackage>>
    
    // Coupons
    @POST("coupons/validate")
    suspend fun validateCoupon(@Body request: ValidateCouponRequest): Response<CouponValidationResponse>
    
    @POST("coupons/apply")
    suspend fun applyCoupon(@Body request: ApplyCouponRequest): Response<ApplyCouponResponse>
}

// Request/Response models
data class GoogleSignInRequest(
    val idToken: String,
    val email: String,
    val name: String?,
    val photoUrl: String?
)

data class AuthResponse(
    val user: User,
    val token: String
)

data class UpdateCreditsRequest(
    val credits: Int
)

data class CreateOrderRequest(
    val serviceId: String,
    val username: String,
    val quantity: Int
)

data class ValidateCouponRequest(
    val code: String
)

data class CouponValidationResponse(
    val isValid: Boolean,
    val coupon: Coupon?,
    val message: String?
)

data class ApplyCouponRequest(
    val code: String,
    val orderId: String
)

data class ApplyCouponResponse(
    val success: Boolean,
    val discount: Int,
    val message: String
)