package com.snaptikpro.app.utils

import android.content.Context
import android.content.res.ColorStateList
import android.graphics.Color
import android.view.View
import android.widget.TextView
import androidx.core.content.ContextCompat
import com.snaptikpro.app.R
import java.util.*

object DayNightManager {
    
    private const val PREFS_NAME = "day_night_prefs"
    private const val KEY_IS_NIGHT_MODE = "is_night_mode"
    
    private fun getPrefs(context: Context) = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
    
    fun isNightMode(context: Context): Boolean {
        val currentHour = Calendar.getInstance().get(Calendar.HOUR_OF_DAY)
        // Gece modu: 18:00 - 06:00 arası
        return currentHour >= 18 || currentHour < 6
    }
    
    fun getCurrentTheme(context: Context): String {
        return if (isNightMode(context)) "dark" else "light"
    }
    
    fun applyTheme(context: Context) {
        val isNight = isNightMode(context)
        getPrefs(context).edit().putBoolean(KEY_IS_NIGHT_MODE, isNight).apply()
        
        // Renkleri güncelle
        updateColors(context, isNight)
    }
    
    private fun updateColors(context: Context, isNight: Boolean) {
        val suffix = if (isNight) "dark" else "light"
        
        // Primary colors
        val primaryColor = ContextCompat.getColor(context, getColorResourceId("primary_$suffix"))
        val primaryDarkColor = ContextCompat.getColor(context, getColorResourceId("primary_dark_$suffix"))
        val accentColor = ContextCompat.getColor(context, getColorResourceId("accent_$suffix"))
        
        // Background colors
        val backgroundColor = ContextCompat.getColor(context, getColorResourceId("background_$suffix"))
        val surfaceColor = ContextCompat.getColor(context, getColorResourceId("surface_$suffix"))
        val cardBackgroundColor = ContextCompat.getColor(context, getColorResourceId("card_background_$suffix"))
        
        // Text colors
        val textPrimaryColor = ContextCompat.getColor(context, getColorResourceId("text_primary_$suffix"))
        val textSecondaryColor = ContextCompat.getColor(context, getColorResourceId("text_secondary_$suffix"))
        
        // Renkleri global olarak güncelle
        updateGlobalColors(context, primaryColor, primaryDarkColor, accentColor, 
                          backgroundColor, surfaceColor, cardBackgroundColor,
                          textPrimaryColor, textSecondaryColor)
    }
    
    private fun getColorResourceId(colorName: String): Int {
        return when (colorName) {
            "primary_dark" -> R.color.primary_dark
            "primary_light" -> R.color.primary_light
            "primary_dark_dark" -> R.color.primary_dark_dark
            "primary_dark_light" -> R.color.primary_dark_light
            "primary_light_dark" -> R.color.primary_light_dark
            "primary_light_light" -> R.color.primary_light_light
            "accent_dark" -> R.color.accent_dark
            "accent_light" -> R.color.accent_light
            "accent_dark_dark" -> R.color.accent_dark_dark
            "accent_dark_light" -> R.color.accent_dark_light
            "accent_light_dark" -> R.color.accent_light_dark
            "accent_light_light" -> R.color.accent_light_light
            "background_dark" -> R.color.background_dark
            "background_light" -> R.color.background_light
            "surface_dark" -> R.color.surface_dark
            "surface_light" -> R.color.surface_light
            "surface_light_dark" -> R.color.surface_light_dark
            "surface_light_light" -> R.color.surface_light_light
            "card_background_dark" -> R.color.card_background_dark
            "card_background_light" -> R.color.card_background_light
            "text_primary_dark" -> R.color.text_primary_dark
            "text_primary_light" -> R.color.text_primary_light
            "text_secondary_dark" -> R.color.text_secondary_dark
            "text_secondary_light" -> R.color.text_secondary_light
            else -> R.color.primary_dark
        }
    }
    
    private fun updateGlobalColors(context: Context, 
                                 primaryColor: Int, 
                                 primaryDarkColor: Int, 
                                 accentColor: Int,
                                 backgroundColor: Int, 
                                 surfaceColor: Int, 
                                 cardBackgroundColor: Int,
                                 textPrimaryColor: Int, 
                                 textSecondaryColor: Int) {
        // Bu fonksiyon runtime'da renkleri güncellemek için kullanılır
        // Şimdilik sadece log yazdırıyoruz
        android.util.Log.d("DayNightManager", "Theme updated: ${if (isNightMode(context)) "Night" else "Day"}")
        android.util.Log.d("DayNightManager", "Primary: ${String.format("#%06X", (0xFFFFFF and primaryColor))}")
        android.util.Log.d("DayNightManager", "Background: ${String.format("#%06X", (0xFFFFFF and backgroundColor))}")
    }
    
    fun shouldUpdateTheme(context: Context): Boolean {
        val savedNightMode = getPrefs(context).getBoolean(KEY_IS_NIGHT_MODE, isNightMode(context))
        val currentNightMode = isNightMode(context)
        return savedNightMode != currentNightMode
    }
}