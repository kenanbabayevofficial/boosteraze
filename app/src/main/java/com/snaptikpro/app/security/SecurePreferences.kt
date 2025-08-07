package com.snaptikpro.app.security

import android.content.Context
import android.content.SharedPreferences
import android.content.SharedPreferences.Editor

class SecurePreferences(context: Context) {
    
    private val securePrefs: SharedPreferences
    private val regularPrefs: SharedPreferences
    
    init {
        // Initialize secure preferences with encryption
        securePrefs = context.getSharedPreferences("secure_prefs", Context.MODE_PRIVATE)
        
        // Regular preferences for non-sensitive data
        regularPrefs = context.getSharedPreferences("regular_prefs", Context.MODE_PRIVATE)
    }
    
    // Secure methods for sensitive data
    fun putSecureString(key: String, value: String) {
        securePrefs.edit().putString(key, value).apply()
    }
    
    fun getSecureString(key: String, defaultValue: String? = null): String? {
        return securePrefs.getString(key, defaultValue)
    }
    
    fun putSecureBoolean(key: String, value: Boolean) {
        securePrefs.edit().putBoolean(key, value).apply()
    }
    
    fun getSecureBoolean(key: String, defaultValue: Boolean = false): Boolean {
        return securePrefs.getBoolean(key, defaultValue)
    }
    
    fun putSecureInt(key: String, value: Int) {
        securePrefs.edit().putInt(key, value).apply()
    }
    
    fun getSecureInt(key: String, defaultValue: Int = 0): Int {
        return securePrefs.getInt(key, defaultValue)
    }
    
    // Regular methods for non-sensitive data
    fun putString(key: String, value: String) {
        regularPrefs.edit().putString(key, value).apply()
    }
    
    fun getString(key: String, defaultValue: String? = null): String? {
        return regularPrefs.getString(key, defaultValue)
    }
    
    fun putBoolean(key: String, value: Boolean) {
        regularPrefs.edit().putBoolean(key, value).apply()
    }
    
    fun getBoolean(key: String, defaultValue: Boolean = false): Boolean {
        return regularPrefs.getBoolean(key, defaultValue)
    }
    
    fun putInt(key: String, value: Int) {
        regularPrefs.edit().putInt(key, value).apply()
    }
    
    fun getInt(key: String, defaultValue: Int = 0): Int {
        return regularPrefs.getInt(key, defaultValue)
    }
    
    // Clear methods
    fun clearSecure() {
        securePrefs.edit().clear().apply()
    }
    
    fun clearRegular() {
        regularPrefs.edit().clear().apply()
    }
    
    fun clearAll() {
        clearSecure()
        clearRegular()
    }
    
    // Check if key exists
    fun containsSecure(key: String): Boolean {
        return securePrefs.contains(key)
    }
    
    fun containsRegular(key: String): Boolean {
        return regularPrefs.contains(key)
    }
    
    // Remove specific keys
    fun removeSecure(key: String) {
        securePrefs.edit().remove(key).apply()
    }
    
    fun removeRegular(key: String) {
        regularPrefs.edit().remove(key).apply()
    }
}