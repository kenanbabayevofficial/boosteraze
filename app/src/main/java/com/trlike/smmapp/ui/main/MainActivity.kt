package com.trlike.smmapp.ui.main

import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import com.trlike.smmapp.data.SessionManager
import com.trlike.smmapp.data.model.CreditPackage
import com.trlike.smmapp.data.model.Service
import com.trlike.smmapp.data.repository.AuthRepository
import com.trlike.smmapp.data.repository.ServiceRepository
import com.trlike.smmapp.databinding.ActivityMainBinding
import com.trlike.smmapp.ui.adapter.CreditPackageAdapter
import com.trlike.smmapp.ui.adapter.ServiceAdapter
import kotlinx.coroutines.launch

class MainActivity : AppCompatActivity() {
    
    private lateinit var binding: ActivityMainBinding
    private val authRepository = AuthRepository()
    private val serviceRepository = ServiceRepository()
    
    private lateinit var creditPackageAdapter: CreditPackageAdapter
    private lateinit var followerPackageAdapter: ServiceAdapter
    private lateinit var turkishFollowerPackageAdapter: ServiceAdapter
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)
        
        setupRecyclerViews()
        setupClickListeners()
        loadUserData()
        loadServices()
    }
    
    private fun setupRecyclerViews() {
        // Credit Packages
        creditPackageAdapter = CreditPackageAdapter { package ->
            // Handle credit package purchase
            purchaseCredits(package)
        }
        binding.rvCreditPackages.apply {
            layoutManager = LinearLayoutManager(this@MainActivity, LinearLayoutManager.HORIZONTAL, false)
            adapter = creditPackageAdapter
        }
        
        // Follower Packages
        followerPackageAdapter = ServiceAdapter { service ->
            // Handle follower package selection
            showOrderDialog(service)
        }
        binding.rvFollowerPackages.apply {
            layoutManager = LinearLayoutManager(this@MainActivity, LinearLayoutManager.HORIZONTAL, false)
            adapter = followerPackageAdapter
        }
        
        // Turkish Follower Packages
        turkishFollowerPackageAdapter = ServiceAdapter { service ->
            // Handle Turkish follower package selection
            showOrderDialog(service)
        }
        binding.rvTurkishFollowerPackages.apply {
            layoutManager = LinearLayoutManager(this@MainActivity, LinearLayoutManager.HORIZONTAL, false)
            adapter = turkishFollowerPackageAdapter
        }
    }
    
    private fun setupClickListeners() {
        binding.ivMenu.setOnClickListener {
            // Toggle navigation drawer visibility
            toggleNavigationDrawer()
        }
        
        binding.btnMyOrders.setOnClickListener {
            // Navigate to orders screen
            Toast.makeText(this, "My Orders", Toast.LENGTH_SHORT).show()
        }
        
        binding.btnSupport.setOnClickListener {
            // Navigate to support screen
            Toast.makeText(this, "Support", Toast.LENGTH_SHORT).show()
        }
        
        binding.btnUseCoupon.setOnClickListener {
            // Show coupon dialog
            showCouponDialog()
        }
        
        binding.btnPrivacy.setOnClickListener {
            // Navigate to privacy agreement
            Toast.makeText(this, "Privacy Agreement", Toast.LENGTH_SHORT).show()
        }
    }
    
    private fun loadUserData() {
        val userEmail = SessionManager.getUserEmail()
        val userCredits = SessionManager.getUserCredits()
        
        binding.tvUserEmail.text = userEmail
        binding.tvUserCredits.text = userCredits.toString()
        
        // Load user profile from API
        lifecycleScope.launch {
            try {
                val result = authRepository.getUserProfile()
                result.fold(
                    onSuccess = { user ->
                        binding.tvUserEmail.text = user.email
                        binding.tvUserCredits.text = user.credits.toString()
                    },
                    onFailure = { exception ->
                        Toast.makeText(this@MainActivity, "Failed to load user data: ${exception.message}", Toast.LENGTH_SHORT).show()
                    }
                )
            } catch (e: Exception) {
                Toast.makeText(this@MainActivity, "Failed to load user data: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
    }
    
    private fun loadServices() {
        lifecycleScope.launch {
            try {
                val result = serviceRepository.getServices()
                result.fold(
                    onSuccess = { services ->
                        // Filter services by category
                        val followerServices = services.filter { it.category == "followers" }
                        val turkishFollowerServices = services.filter { it.category == "turkish_followers" }
                        
                        followerPackageAdapter.submitList(followerServices)
                        turkishFollowerPackageAdapter.submitList(turkishFollowerServices)
                        
                        // Load credit packages (mock data for now)
                        loadCreditPackages()
                    },
                    onFailure = { exception ->
                        Toast.makeText(this@MainActivity, "Failed to load services: ${exception.message}", Toast.LENGTH_SHORT).show()
                    }
                )
            } catch (e: Exception) {
                Toast.makeText(this@MainActivity, "Failed to load services: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
    }
    
    private fun loadCreditPackages() {
        // Mock credit packages - replace with API call
        val creditPackages = listOf(
            CreditPackage("1", 5000, 12.84, "credit_5000"),
            CreditPackage("2", 2500, 6.76, "credit_2500"),
            CreditPackage("3", 1000, 3.12, "credit_1000"),
            CreditPackage("4", 50, 1.0, "credit_50")
        )
        creditPackageAdapter.submitList(creditPackages)
    }
    
    private fun toggleNavigationDrawer() {
        // Implement navigation drawer toggle
        Toast.makeText(this, "Menu", Toast.LENGTH_SHORT).show()
    }
    
    private fun purchaseCredits(creditPackage: CreditPackage) {
        // Implement Google Play Billing
        Toast.makeText(this, "Purchase ${creditPackage.credits} credits for $${creditPackage.price}", Toast.LENGTH_SHORT).show()
    }
    
    private fun showOrderDialog(service: Service) {
        // Show order dialog
        Toast.makeText(this, "Order ${service.name}", Toast.LENGTH_SHORT).show()
    }
    
    private fun showCouponDialog() {
        // Show coupon dialog
        Toast.makeText(this, "Enter Coupon Code", Toast.LENGTH_SHORT).show()
    }
}