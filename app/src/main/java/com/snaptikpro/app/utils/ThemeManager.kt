package com.snaptikpro.app.utils

import android.content.Context
import android.content.SharedPreferences

object ThemeManager {
    private const val PREFS_NAME = "theme_prefs"
    private const val KEY_CURRENT_THEME = "current_theme"
    
    const val THEME_BLUE = "blue"
    const val THEME_PURPLE = "purple"
    const val THEME_GREEN = "green"
    const val THEME_DARK = "dark"
    const val THEME_PINK = "pink"
    
    private fun getPrefs(context: Context): SharedPreferences {
        return context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
    }
    
    fun getCurrentTheme(context: Context): String {
        return getPrefs(context).getString(KEY_CURRENT_THEME, THEME_BLUE) ?: THEME_BLUE
    }
    
    fun setTheme(context: Context, theme: String) {
        getPrefs(context).edit().putString(KEY_CURRENT_THEME, theme).apply()
    }
    
    fun getThemeNames(): List<String> {
        return listOf(
            "Mavi",
            "Mor", 
            "Yaşıl",
            "Qaranlıq",
            "Çəhrayı"
        )
    }
    
    fun getThemeKeys(): List<String> {
        return listOf(
            THEME_BLUE,
            THEME_PURPLE,
            THEME_GREEN,
            THEME_DARK,
            THEME_PINK
        )
    }
    
    fun getThemeColor(context: Context, colorType: String): Int {
        val currentTheme = getCurrentTheme(context)
        val colorName = "${colorType}_${currentTheme}"
        
        return try {
            val colorResId = context.resources.getIdentifier(colorName, "color", context.packageName)
            context.getColor(colorResId)
        } catch (e: Exception) {
            // Fallback to blue theme
            val fallbackColorName = "${colorType}_blue"
            val fallbackColorResId = context.resources.getIdentifier(fallbackColorName, "color", context.packageName)
            context.getColor(fallbackColorResId)
        }
    }
}