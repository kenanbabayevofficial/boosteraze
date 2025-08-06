package com.snaptikpro.app

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.snaptikpro.app.databinding.ItemThemeBinding
import com.snaptikpro.app.utils.ThemeManager

class ThemeAdapter(
    private val themeNames: List<String>,
    private val themeKeys: List<String>,
    private val currentTheme: String,
    private val onThemeClick: (String) -> Unit
) : RecyclerView.Adapter<ThemeAdapter.ViewHolder>() {
    
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemThemeBinding.inflate(
            LayoutInflater.from(parent.context),
            parent,
            false
        )
        return ViewHolder(binding)
    }
    
    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        holder.bind(themeNames[position], themeKeys[position])
    }
    
    override fun getItemCount(): Int = themeNames.size
    
    inner class ViewHolder(private val binding: ItemThemeBinding) : RecyclerView.ViewHolder(binding.root) {
        
        init {
            binding.root.setOnClickListener {
                val position = adapterPosition
                if (position != RecyclerView.NO_POSITION) {
                    onThemeClick(themeKeys[position])
                }
            }
        }
        
        fun bind(themeName: String, themeKey: String) {
            binding.tvThemeName.text = themeName
            
            // Set theme color preview
            val context = binding.root.context
            val primaryColor = ThemeManager.getThemeColor(context, "primary_$themeKey")
            binding.ivThemeColor.setBackgroundColor(primaryColor)
            
            // Show checkmark for current theme
            if (themeKey == currentTheme) {
                binding.ivCheck.visibility = android.view.View.VISIBLE
                binding.root.setBackgroundResource(R.drawable.theme_selected_background)
            } else {
                binding.ivCheck.visibility = android.view.View.GONE
                binding.root.setBackgroundResource(R.drawable.theme_background)
            }
        }
    }
}