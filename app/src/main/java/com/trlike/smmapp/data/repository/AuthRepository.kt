package com.trlike.smmapp.data.repository

import com.trlike.smmapp.data.SessionManager
import com.trlike.smmapp.data.api.RetrofitClient
import com.trlike.smmapp.data.api.GoogleSignInRequest
import com.trlike.smmapp.data.model.User
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

class AuthRepository {
    
    suspend fun googleSignIn(idToken: String, email: String, name: String?, photoUrl: String?): Result<User> {
        return withContext(Dispatchers.IO) {
            try {
                val request = GoogleSignInRequest(idToken, email, name, photoUrl)
                val response = RetrofitClient.apiService.googleSignIn(request)
                
                if (response.isSuccessful) {
                    response.body()?.let { authResponse ->
                        SessionManager.saveAuthToken(authResponse.token)
                        SessionManager.saveUserData(
                            authResponse.user.id,
                            authResponse.user.email,
                            authResponse.user.name,
                            authResponse.user.photoUrl,
                            authResponse.user.credits
                        )
                        Result.success(authResponse.user)
                    } ?: Result.failure(Exception("Empty response"))
                } else {
                    Result.failure(Exception("Authentication failed: ${response.code()}"))
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
    }
    
    suspend fun getUserProfile(): Result<User> {
        return withContext(Dispatchers.IO) {
            try {
                val response = RetrofitClient.apiService.getUserProfile()
                
                if (response.isSuccessful) {
                    response.body()?.let { user ->
                        SessionManager.saveUserData(
                            user.id,
                            user.email,
                            user.name,
                            user.photoUrl,
                            user.credits
                        )
                        Result.success(user)
                    } ?: Result.failure(Exception("Empty response"))
                } else {
                    Result.failure(Exception("Failed to get user profile: ${response.code()}"))
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
    }
    
    fun logout() {
        SessionManager.clearSession()
    }
}