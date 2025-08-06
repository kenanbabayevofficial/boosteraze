package com.snaptikpro.app

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import com.snaptikpro.app.databinding.ActivitySettingsBinding
import com.snaptikpro.app.utils.ThemeManager

class SettingsActivity : AppCompatActivity() {
    
    private lateinit var binding: ActivitySettingsBinding
    private lateinit var adapter: ThemeAdapter
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivitySettingsBinding.inflate(layoutInflater)
        setContentView(binding.root)
        
        setupToolbar()
        setupRecyclerView()
        loadCurrentTheme()
    }
    
    private fun setupToolbar() {
        binding.ivBack.setOnClickListener {
            finish()
        }
        
        binding.tvTitle.text = getString(R.string.settings)
    }
    
    private fun setupRecyclerView() {
        val themeNames = ThemeManager.getThemeNames()
        val themeKeys = ThemeManager.getThemeKeys()
        val currentTheme = ThemeManager.getCurrentTheme(this)
        
        adapter = ThemeAdapter(themeNames, themeKeys, currentTheme) { themeKey ->
            showThemeChangeDialog(themeKey)
        }
        
        binding.rvThemes.layoutManager = LinearLayoutManager(this)
        binding.rvThemes.adapter = adapter
    }
    
    private fun loadCurrentTheme() {
        val currentTheme = ThemeManager.getCurrentTheme(this)
        val themeNames = ThemeManager.getThemeNames()
        val themeKeys = ThemeManager.getThemeKeys()
        val currentIndex = themeKeys.indexOf(currentTheme)
        
        if (currentIndex >= 0) {
            binding.tvCurrentTheme.text = "Cari tema: ${themeNames[currentIndex]}"
        }
    }
    
    private fun showThemeChangeDialog(themeKey: String) {
        val themeNames = ThemeManager.getThemeNames()
        val themeKeys = ThemeManager.getThemeKeys()
        val themeIndex = themeKeys.indexOf(themeKey)
        val themeName = if (themeIndex >= 0) themeNames[themeIndex] else themeKey
        
        AlertDialog.Builder(this)
            .setTitle("Tema Dəyişdir")
            .setMessage("Temanı '$themeName' olaraq dəyişdirmək istədiyinizə əminsiniz?")
            .setPositiveButton("Bəli") { _, _ ->
                changeTheme(themeKey)
            }
            .setNegativeButton("Xeyr", null)
            .show()
    }
    
    private fun changeTheme(themeKey: String) {
        ThemeManager.setTheme(this, themeKey)
        
        Toast.makeText(this, "Tema dəyişdirildi! Tətbiqi yenidən başladın.", Toast.LENGTH_LONG).show()
        
        // Restart the app to apply theme changes
        val intent = Intent(this, SplashActivity::class.java)
        intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK)
        startActivity(intent)
        finish()
    }
}