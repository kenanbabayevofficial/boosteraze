package com.snaptikpro.app

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.snaptikpro.app.databinding.ItemHelpBinding

class HelpAdapter(
    private val helpItems: List<HelpItem>,
    private val onItemClick: (HelpItem) -> Unit
) : RecyclerView.Adapter<HelpAdapter.ViewHolder>() {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemHelpBinding.inflate(
            LayoutInflater.from(parent.context),
            parent,
            false
        )
        return ViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        holder.bind(helpItems[position])
    }

    override fun getItemCount(): Int = helpItems.size

    inner class ViewHolder(private val binding: ItemHelpBinding) : RecyclerView.ViewHolder(binding.root) {

        init {
            binding.root.setOnClickListener {
                val position = adapterPosition
                if (position != RecyclerView.NO_POSITION) {
                    onItemClick(helpItems[position])
                }
            }
        }

        fun bind(helpItem: HelpItem) {
            binding.tvTitle.text = helpItem.title
            binding.tvDescription.text = helpItem.description
            binding.ivIcon.setImageResource(helpItem.icon)
        }
    }
}