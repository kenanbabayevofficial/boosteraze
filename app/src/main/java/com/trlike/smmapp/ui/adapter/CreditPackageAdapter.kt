package com.trlike.smmapp.ui.adapter

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.trlike.smmapp.data.model.CreditPackage
import com.trlike.smmapp.databinding.ItemCreditPackageBinding

class CreditPackageAdapter(
    private val onItemClick: (CreditPackage) -> Unit
) : ListAdapter<CreditPackage, CreditPackageAdapter.CreditPackageViewHolder>(CreditPackageDiffCallback()) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): CreditPackageViewHolder {
        val binding = ItemCreditPackageBinding.inflate(
            LayoutInflater.from(parent.context),
            parent,
            false
        )
        return CreditPackageViewHolder(binding, onItemClick)
    }

    override fun onBindViewHolder(holder: CreditPackageViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    class CreditPackageViewHolder(
        private val binding: ItemCreditPackageBinding,
        private val onItemClick: (CreditPackage) -> Unit
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(creditPackage: CreditPackage) {
            binding.tvCredits.text = "${creditPackage.credits} Kredi"
            binding.tvPrice.text = "$${creditPackage.price}"
            binding.btnBuy.text = "Satın Al"
            
            binding.btnBuy.setOnClickListener {
                onItemClick(creditPackage)
            }
        }
    }

    private class CreditPackageDiffCallback : DiffUtil.ItemCallback<CreditPackage>() {
        override fun areItemsTheSame(oldItem: CreditPackage, newItem: CreditPackage): Boolean {
            return oldItem.id == newItem.id
        }

        override fun areContentsTheSame(oldItem: CreditPackage, newItem: CreditPackage): Boolean {
            return oldItem == newItem
        }
    }
}